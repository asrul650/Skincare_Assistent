<?php
session_start();
require_once '../config/db.php';

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

// Anti-CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Skincare Assistant</title>
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

        /* Main Content Styles */
        main {
            max-width: 1200px;
            margin: 6rem auto 2rem;
            padding: 0 1rem;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-banner h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: #3498db;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Footer Styles */
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

            .welcome-banner {
                padding: 1.5rem;
            }

            .welcome-banner h1 {
                font-size: 1.5rem;
            }

            .feature-card {
                padding: 1.5rem;
            }
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
                    <li><a href="home.php" class="active"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="skin_analysis.php"><i class="fas fa-microscope"></i> Analisis Kulit</a></li>
                    <li><a href="products.php"><i class="fas fa-pump-soap"></i> Produk</a></li>
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
        <div class="welcome-banner">
            <h1>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Mulai perjalanan perawatan kulit Anda dengan Skincare Assistant. Kami siap membantu Anda mencapai kulit sehat dan cantik yang Anda impikan.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-microscope"></i>
                <h3>Analisis Kulit</h3>
                <p>Analisis kondisi kulit Anda secara detail dan dapatkan rekomendasi perawatan yang tepat.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-shopping-bag"></i>
                <h3>Rekomendasi Produk</h3>
                <p>Temukan produk skincare yang cocok dengan jenis kulit dan kebutuhan Anda.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-comments"></i>
                <h3>Konsultasi Online</h3>
                <p>Konsultasikan masalah kulit Anda dengan ahli kami secara online.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-calendar-alt"></i>
                <h3>Rutinitas Skincare</h3>
                <p>Buat dan kelola rutinitas skincare harian Anda dengan mudah.</p>
            </div>
        </div>
    </main>

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
</body>
</html> 