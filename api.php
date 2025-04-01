<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

// Cek request method dan parameter untuk menentukan aksi
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pencarian produk
    if (isset($_GET['product_name'])) {
        try {
            $productName = $_GET['product_name'];
            $query = "SELECT p.*, GROUP_CONCAT(pr.rating) as ratings, 
                     GROUP_CONCAT(pr.review_text) as reviews
                     FROM products p 
                     LEFT JOIN product_reviews pr ON p.id = pr.product_id
                     WHERE p.name LIKE :name
                     GROUP BY p.id";
            
            $stmt = $conn->prepare($query);
            $stmt->bindValue(':name', "%$productName%", PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Format response
                $response = "
                    <div class='product-info'>
                        <h3>{$product['name']}</h3>
                        <p class='brand'>Brand: {$product['brand']}</p>
                        <p class='price'>Harga: Rp " . number_format($product['price'], 0, ',', '.') . "</p>
                        <div class='description'>
                            <h4>Deskripsi:</h4>
                            <p>{$product['description']}</p>
                        </div>
                        <div class='ingredients'>
                            <h4>Kandungan:</h4>
                            <p>{$product['ingredients']}</p>
                        </div>
                        <div class='usage'>
                            <h4>Cara Penggunaan:</h4>
                            <p>" . (isset($product['how_to_use']) ? $product['how_to_use'] : '') . "</p>
                        </div>
                    </div>";
                
                echo $response;
            } else {
                echo "<div class='error-message'>Produk tidak ditemukan.</div>";
            }
        } catch(PDOException $e) {
            echo "<div class='error-message'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
        }
    }
    // Produk trending
    else if (isset($_GET['trending'])) {
        try {
            $query = "SELECT p.*, COUNT(pr.id) as review_count, AVG(pr.rating) as avg_rating
                     FROM products p
                     LEFT JOIN product_reviews pr ON p.id = pr.product_id
                     GROUP BY p.id
                     ORDER BY avg_rating DESC, review_count DESC
                     LIMIT 5";
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            $trendingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($trendingProducts);
        } catch(PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    // Artikel
    else if (isset($_GET['articles'])) {
        try {
            $query = "SELECT * FROM articles ORDER BY created_at DESC LIMIT 6";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($articles);
        } catch(PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
// Handle POST requests
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Analisis kulit
    if (isset($_POST['action']) && $_POST['action'] === 'analyze_skin') {
        session_start();
        
        if (!isset($_POST['skinType']) || !isset($_POST['concerns'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Data tidak lengkap'
            ]);
            exit;
        }

        $skinType = $_POST['skinType'];
        $concerns = $_POST['concerns'];
        $userId = $_SESSION['user_id'];
        
        try {
            // Simpan hasil analisis ke database
            $query = "INSERT INTO skin_analysis (user_id, skin_type, concerns, analysis_date) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $concernsStr = is_array($concerns) ? implode(',', $concerns) : $concerns;
            $stmt->execute([$userId, $skinType, $concernsStr]);

            // Ambil rekomendasi produk
            $products = [
                [
                    'name' => 'Facial Wash untuk ' . ucfirst($skinType),
                    'brand' => 'SkinCare Pro',
                    'price' => '150000',
                    'description' => 'Pembersih wajah khusus untuk kulit ' . $skinType,
                    'ingredients' => 'Aqua, Glycerin, Niacinamide',
                    'how_to_use' => 'Gunakan 2 kali sehari, pagi dan malam'
                ],
                [
                    'name' => 'Toner untuk ' . ucfirst($skinType),
                    'brand' => 'SkinCare Pro',
                    'price' => '200000',
                    'description' => 'Toner khusus untuk kulit ' . $skinType,
                    'ingredients' => 'Aqua, Centella Asiatica, Hyaluronic Acid',
                    'how_to_use' => 'Aplikasikan setelah mencuci wajah'
                ],
                [
                    'name' => 'Moisturizer untuk ' . ucfirst($skinType),
                    'brand' => 'SkinCare Pro',
                    'price' => '250000',
                    'description' => 'Pelembab khusus untuk kulit ' . $skinType,
                    'ingredients' => 'Aqua, Ceramide, Vitamin E',
                    'how_to_use' => 'Aplikasikan setelah toner'
                ]
            ];

            // Buat rekomendasi rutinitas
            $routineRecommendations = [
                'cleansing' => getCleansingRecommendation($skinType),
                'toner' => getTonerRecommendation($skinType),
                'moisturizer' => getMoisturizerRecommendation($skinType),
                'sunscreen' => "Gunakan sunscreen SPF minimal 30 setiap hari"
            ];

            $response = [
                'status' => 'success',
                'skinType' => $skinType,
                'concerns' => $concerns,
                'routineRecommendations' => $routineRecommendations,
                'productRecommendations' => $products
            ];

            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    // Tambah jadwal
    else if (isset($_POST['schedule_name']) && isset($_POST['schedule_time'])) {
        try {
            $query = "INSERT INTO schedules (routine_name, schedule_time, user_id) 
                     VALUES (:routine_name, :schedule_time, :user_id)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':routine_name', $_POST['schedule_name']);
            $stmt->bindParam(':schedule_time', $_POST['schedule_time']);
            $stmt->bindValue(':user_id', 1); // Sementara hardcode user_id = 1
            
            if ($stmt->execute()) {
                echo "Jadwal berhasil ditambahkan.";
            } else {
                echo "Gagal menambahkan jadwal.";
            }
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Helper functions
function getCleansingRecommendation($skinType) {
    switch($skinType) {
        case 'oily':
            return "Gunakan pembersih wajah dengan kandungan Salicylic Acid atau Tea Tree Oil, cuci wajah 2-3 kali sehari";
        case 'dry':
            return "Gunakan pembersih wajah lembut dengan kandungan Ceramide atau Hyaluronic Acid, cuci wajah 2 kali sehari";
        case 'combination':
            return "Gunakan pembersih wajah pH balanced yang tidak terlalu keras, fokus pada area T-zone";
        case 'sensitive':
            return "Gunakan pembersih wajah bebas alkohol dan fragrance, cuci wajah dengan air hangat";
        default:
            return "Gunakan pembersih wajah pH balanced 2 kali sehari";
    }
}

function getTonerRecommendation($skinType) {
    switch($skinType) {
        case 'oily':
            return "Gunakan toner dengan kandungan BHA atau Niacinamide untuk mengontrol minyak";
        case 'dry':
            return "Gunakan toner hydrating dengan kandungan Hyaluronic Acid dan Glycerin";
        case 'combination':
            return "Gunakan toner yang mengandung AHA/BHA ringan, aplikasikan lebih banyak di area T-zone";
        case 'sensitive':
            return "Gunakan toner alcohol-free dengan kandungan Centella Asiatica dan Aloe Vera";
        default:
            return "Gunakan toner alcohol-free yang menyegarkan";
    }
}

function getMoisturizerRecommendation($skinType) {
    switch($skinType) {
        case 'oily':
            return "Gunakan moisturizer gel atau water-based yang ringan dan non-comedogenic";
        case 'dry':
            return "Gunakan moisturizer krim yang lebih tebal dengan kandungan Ceramide dan Peptide";
        case 'combination':
            return "Gunakan moisturizer gel-cream yang ringan, aplikasikan lebih sedikit di area T-zone";
        case 'sensitive':
            return "Gunakan moisturizer hypoallergenic tanpa pewangi dengan kandungan Ceramide";
        default:
            return "Gunakan moisturizer sesuai dengan kebutuhan kulit";
    }
}
?>
