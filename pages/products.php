<?php
session_start();
require_once '../config/db.php';
require_once '../database/connection.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit;
}

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Mencegah akses langsung ke file tanpa login
if (!isset($_SESSION['username'])) {
    header("Location: login_user.php");
    exit;
}

// Array produk default jika database kosong
$default_products = [
    [
        'name' => "Gentle Foam Cleanser",
        'brand' => "Pure Beauty",
        'category' => "cleanser",
        'price' => 125000,
        'description' => "Pembersih wajah lembut dengan pH seimbang, cocok untuk semua jenis kulit",
        'image' => "https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types"
    ],
    [
        'name' => "Hydrating Toner",
        'brand' => "Skin Essentials",
        'category' => "toner",
        'price' => 180000,
        'description' => "Toner dengan kandungan hyaluronic acid untuk melembabkan kulit",
        'image' => "https://images.unsplash.com/photo-1573575155376-b5010099301b?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "Dry Skin"
    ],
    [
        'name' => "Vitamin C Serum",
        'brand' => "Glow Lab",
        'category' => "serum",
        'price' => 350000,
        'description' => "Serum vitamin C 10% untuk mencerahkan dan meratakan warna kulit",
        'image' => "https://images.unsplash.com/photo-1620916566398-39f1143ab7be?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types"
    ],
    [
        'name' => "Moisture Cream",
        'brand' => "Hydra Plus",
        'category' => "moisturizer",
        'price' => 200000,
        'description' => "Krim pelembab ringan dengan ekstrak aloe vera",
        'image' => "https://images.unsplash.com/photo-1556228578-8c89e6adf883?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types"
    ],
    [
        'name' => "UV Shield SPF50+ PA++++",
        'brand' => "Sun Protection",
        'category' => "sunscreen",
        'price' => 275000,
        'description' => "Sunscreen dengan perlindungan tinggi, tidak lengket",
        'image' => "https://images.unsplash.com/photo-1556228720-195a672e8a03?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types"
    ]
];

// Tambahkan array gambar default
$default_images = [
    'cleanser' => [
        'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8',
        'https://images.unsplash.com/photo-1556228720-195a672e8a03'
    ],
    'toner' => [
        'https://images.unsplash.com/photo-1573575155376-b5010099301b',
        'https://images.unsplash.com/photo-1573575154488-f87c36e0d5df'
    ],
    'serum' => [
        'https://images.unsplash.com/photo-1620916566398-39f1143ab7be',
        'https://images.unsplash.com/photo-1620916565839-1f7809db7c54'
    ],
    'moisturizer' => [
        'https://images.unsplash.com/photo-1556228578-8c89e6adf883',
        'https://images.unsplash.com/photo-1556228841-c5b87c10f7fc'
    ],
    'sunscreen' => [
        'https://images.unsplash.com/photo-1556228720-195a672e8a03',
        'https://images.unsplash.com/photo-1556228841-5e100c6df2e6'
    ]
];

// Mengambil data produk dari database
$query = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($query);
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    // Jika tidak ada produk di database, gunakan produk default
    $products = $default_products;
}

// Membuat direktori uploads jika belum ada
$uploads_dir = "../uploads/products";
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0777, true);
}

// Membuat direktori assets jika belum ada
$assets_dir = "../assets";
if (!file_exists($assets_dir)) {
    mkdir($assets_dir, 0777, true);
}

// Modifikasi fungsi getDefaultProductImage
function getDefaultProductImage($category) {
    $default_images = [
        'cleanser' => 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8',
        'toner' => 'https://images.unsplash.com/photo-1573575155376-b5010099301b',
        'serum' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be',
        'moisturizer' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883',
        'sunscreen' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03'
    ];
    
    $category = strtolower($category);
    
    if (isset($default_images[$category])) {
        return $default_images[$category] . '?auto=format&fit=crop&w=400&q=80';
    }
    
    return 'https://images.unsplash.com/photo-1556228841-5e100c6df2e6?auto=format&fit=crop&w=400&q=80';
}

// Fungsi untuk mendapatkan URL gambar produk
function getProductImage($image, $name) {
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        // Jika image adalah URL, gunakan langsung
        return $image;
    } elseif ($image && file_exists("../uploads/products/" . $image)) {
        // Jika image ada di folder uploads
        return "../uploads/products/" . $image;
    } else {
        // Jika tidak ada gambar, gunakan placeholder
        return "https://via.placeholder.com/400x400.png?text=" . urlencode($name);
    }
}

// Anti-CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --text-color: #2c3e50;
            --background-color: #f8f9fa;
            --border-radius: 15px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            min-height: 100vh;
            background: var(--background-color);
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 1rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-right: 2rem;
        }

        .logo {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2.5rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a i {
            font-size: 1rem;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .menu-section {
            display: flex;
            align-items: center;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .username-display {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-weight: 500;
            margin-left: 20px;
        }

        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .menu-section .nav-links {
                flex-direction: column;
                width: 100%;
                text-align: center;
                gap: 0.5rem;
            }

            .user-section {
                flex-direction: column;
                width: 100%;
            }

            .username-display,
            .logout-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Main Content Styles */
        main {
            max-width: 1200px;
            margin: 6rem auto 2rem;
            padding: 0 1rem;
        }

        /* Products Specific Styles */
        .products-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .products-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .products-header p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .product-filters {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }

        .filter-btn {
            padding: 0.8rem 1.5rem;
            border: 2px solid #3498db;
            border-radius: 25px;
            background: white;
            color: #3498db;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.2);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image-container {
            position: relative;
            padding-top: 100%; /* Aspect ratio 1:1 */
            background: #f5f5f5;
            overflow: hidden;
            border-radius: 10px 10px 0 0;
        }

        .product-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-image.loading {
            filter: blur(10px);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-info {
            padding: 1rem;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .product-brand {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .product-price {
            color: #3498db;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #7f8c8d;
        }

        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .modal-info h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .modal-info p {
            color: #7f8c8d;
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .ingredients-list {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .how-to-use {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }

        /* Footer Styles - Sama dengan home.php */
        footer {
            background: #2c3e50;
            color: white;
            padding: 4rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .footer-section h3 {
            color: #3498db;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-section p {
            color: #ecf0f1;
            line-height: 1.8;
            margin-bottom: 0.8rem;
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .social-links a {
            color: white;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: #3498db;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid #34495e;
        }

        .footer-bottom p {
            color: #bdc3c7;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-container">
            <div class="logo-section">
                <a href="home.php" class="logo">
                    <i class="fas fa-spa"></i>
                    Skincare Assistant
                </a>
            </div>
            <div class="menu-section">
                <ul class="nav-links">
                    <li><a href="home.php"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="skin_analysis.php"><i class="fas fa-microscope"></i> Analisis Kulit</a></li>
                    <li><a href="products.php" class="active"><i class="fas fa-pump-soap"></i> Produk</a></li>
                    <li><a href="schedule.php"><i class="fas fa-calendar"></i> Jadwal</a></li>
                    <li><a href="consultation.php"><i class="fas fa-stethoscope"></i> Konsultasi</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
            
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </header>

    <main>
        <div class="products-header">
            <h1>Katalog Produk Skincare</h1>
            <p>Temukan produk skincare terbaik yang sesuai dengan jenis kulit Anda</p>
        </div>

        <div class="product-filters">
            <button class="filter-btn active" data-category="all">Semua Produk</button>
            <button class="filter-btn" data-category="cleanser">Pembersih</button>
            <button class="filter-btn" data-category="toner">Toner</button>
            <button class="filter-btn" data-category="serum">Serum</button>
            <button class="filter-btn" data-category="moisturizer">Pelembab</button>
            <button class="filter-btn" data-category="sunscreen">Sunscreen</button>
        </div>

        <div class="products-grid">
            <?php
            require_once('../database/connection.php');
            
            $query = "SELECT * FROM products ORDER BY created_at DESC";
            $result = $conn->query($query);

            while ($product = $result->fetch_assoc()) {
                $productImage = getDefaultProductImage($product['category']);
                
                echo "<div class='product-card' data-category='" . strtolower(htmlspecialchars($product['category'])) . "' onclick='showProductDetails({$product['id']})'>";
                echo "<div class='product-image-container'>";
                echo "<img src='" . htmlspecialchars($productImage) . "' 
                          alt='" . htmlspecialchars($product['name']) . "' 
                          class='product-image'
                          loading='lazy'>";
                echo "</div>";
                echo "<div class='product-info'>";
                echo "<div class='product-name'>" . htmlspecialchars($product['name']) . "</div>";
                echo "<div class='product-brand'>" . htmlspecialchars($product['brand']) . "</div>";
                echo "<div class='product-price'>Rp " . number_format($product['price'], 0, ',', '.') . "</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </main>

    <!-- Modal for product details -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Tentang Kami</h3>
                <p>Skincare Assistant adalah platform perawatan kulit terpercaya yang membantu Anda menemukan produk dan rutinitas yang tepat untuk kulit Anda.</p>
            </div>
            <div class="footer-section">
                <h3>Kontak</h3>
                <p><i class="fas fa-envelope"></i> skincareassistant@gmail.com</p>
                <p><i class="fas fa-phone"></i> +62 123 4567 890</p>
                <p><i class="fas fa-map-marker-alt"></i> Jl. Skincare No. 123, Jakarta</p>
            </div>
            <div class="footer-section">
                <h3>Ikuti Kami</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Skincare Assistant. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function showProductDetails(productId) {
            fetch(`get_product_details.php?id=${productId}`)
                .then(response => response.json())
                .then(product => {
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <img src="${product.image}" alt="${product.name}" class="modal-image">
                        <div class="modal-info">
                            <h2>${product.name}</h2>
                            <p class="product-brand">${product.brand}</p>
                            <p class="product-price">Rp ${new Intl.NumberFormat('id-ID').format(product.price)}</p>
                            <div class="how-to-use">
                                <h3>Cara Penggunaan:</h3>
                                <p>${product.how_to_use}</p>
                            </div>
                            <h3>Deskripsi:</h3>
                            <p>${product.description}</p>
                            <h3>Kandungan:</h3>
                            <p>${product.ingredients}</p>
                        </div>
                    `;
                    document.getElementById('productModal').style.display = 'flex';
                })
                .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const productCards = document.querySelectorAll('.product-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Hapus kelas active dari semua tombol
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Tambah kelas active ke tombol yang diklik
                    button.classList.add('active');

                    const selectedCategory = button.getAttribute('data-category');
                    console.log('Selected category:', selectedCategory); // Debug

                    productCards.forEach(card => {
                        const cardCategory = card.getAttribute('data-category');
                        console.log('Card category:', cardCategory); // Debug

                        if (selectedCategory === 'all') {
                            card.style.display = 'block';
                        } else {
                            if (cardCategory === selectedCategory) {
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }
                    });
                });
            });

            const images = document.querySelectorAll('.product-image');
            
            images.forEach(img => {
                img.classList.add('loading');
                
                img.onload = function() {
                    img.classList.remove('loading');
                }
                
                img.onerror = function() {
                    img.src = 'https://images.unsplash.com/photo-1556228841-5e100c6df2e6?auto=format&fit=crop&w=400&q=80';
                    img.classList.remove('loading');
                }
            });
        });
    </script>
</body>
</html> 