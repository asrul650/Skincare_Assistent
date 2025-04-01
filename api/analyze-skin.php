<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi session dan user_id
        if (!isset($_SESSION['user_id'])) {
            throw new Exception('Silakan login terlebih dahulu');
        }

        // Periksa apakah user exists di database
        $user_id = $_SESSION['user_id'];
        $checkUser = "SELECT id FROM users WHERE id = ?";
        $stmt = $conn->prepare($checkUser);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('User tidak ditemukan');
        }

        // Validasi input
        $skinType = $_POST['skinType'] ?? '';
        $concerns = $_POST['concerns'] ?? [];
        
        if (empty($skinType)) {
            throw new Exception('Silakan pilih jenis kulit Anda');
        }

        // Hapus analisis sebelumnya jika ada
        $deleteOld = "DELETE FROM skin_analysis WHERE user_id = ?";
        $stmt = $conn->prepare($deleteOld);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Simpan data analisis kulit baru
        $query = "INSERT INTO skin_analysis (user_id, skin_type, concerns) 
                 VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        // Ubah array concerns menjadi string JSON
        $concernsJson = json_encode($concerns, JSON_UNESCAPED_UNICODE);
        
        $stmt->bind_param("iss", $user_id, $skinType, $concernsJson);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan data analisis');
        }
        
        // Ambil rekomendasi produk
        $productQuery = "SELECT * FROM products 
                        WHERE skin_type = ? OR skin_type = 'all' 
                        LIMIT 5";
        $stmt = $conn->prepare($productQuery);
        $stmt->bind_param("s", $skinType);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = $result->fetch_all(MYSQLI_ASSOC);

        // Siapkan rekomendasi rutinitas
        $routineRecommendations = [
            'cleansing' => getCleansingRecommendation($skinType),
            'toner' => getTonerRecommendation($skinType),
            'moisturizer' => getMoisturizerRecommendation($skinType),
            'sunscreen' => "Gunakan sunscreen SPF minimal 30 setiap hari"
        ];

        echo json_encode([
            'status' => 'success',
            'skinType' => $skinType,
            'concerns' => $concerns,
            'routineRecommendations' => $routineRecommendations,
            'productRecommendations' => $products
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

// Fungsi helper untuk rekomendasi
function getCleansingRecommendation($skinType) {
    $recommendations = [
        'oily' => 'Gunakan pembersih wajah dengan formula gel atau busa yang mengandung salicylic acid',
        'dry' => 'Gunakan pembersih wajah cream atau lotion yang lembut dan tidak mengandung alkohol',
        'combination' => 'Gunakan pembersih wajah yang seimbang, tidak terlalu kering atau berminyak',
        'sensitive' => 'Gunakan pembersih wajah hypoallergenic tanpa pewangi',
        'normal' => 'Gunakan pembersih wajah dengan pH seimbang'
    ];
    return $recommendations[$skinType] ?? $recommendations['normal'];
}

function getTonerRecommendation($skinType) {
    $recommendations = [
        'oily' => 'Gunakan toner dengan kandungan BHA atau niacinamide',
        'dry' => 'Gunakan toner hydrating dengan kandungan hyaluronic acid',
        'combination' => 'Gunakan toner yang mengandung AHA/BHA ringan',
        'sensitive' => 'Gunakan toner alcohol-free dengan kandungan chamomile atau aloe vera',
        'normal' => 'Gunakan toner dengan kandungan vitamin dan antioksidan'
    ];
    return $recommendations[$skinType] ?? $recommendations['normal'];
}

function getMoisturizerRecommendation($skinType) {
    $recommendations = [
        'oily' => 'Gunakan moisturizer gel atau water-based yang ringan',
        'dry' => 'Gunakan moisturizer cream yang kaya emolien',
        'combination' => 'Gunakan moisturizer gel-cream yang seimbang',
        'sensitive' => 'Gunakan moisturizer hypoallergenic tanpa pewangi',
        'normal' => 'Gunakan moisturizer dengan tekstur medium'
    ];
    return $recommendations[$skinType] ?? $recommendations['normal'];
}
?> 