<?php
session_start();
require_once '../config/db.php';

// Cek apakah sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil informasi admin
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Mengambil statistik
$stats = [
    'products' => $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'],
    'articles' => $conn->query("SELECT COUNT(*) as total FROM articles")->fetch_assoc()['total'],
    'reviews' => $conn->query("SELECT COUNT(*) as total FROM reviews")->fetch_assoc()['total'] ?? 0,
    'schedules' => $conn->query("SELECT COUNT(*) as total FROM schedules")->fetch_assoc()['total']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f6f9fc;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 1rem;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .admin-logo i {
            font-size: 1.5rem;
        }

        .admin-menu {
            list-style: none;
        }

        .menu-item {
            margin-bottom: 0.5rem;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: #3498db;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: linear-gradient(135deg, #f6f9fc 0%, #e9f2f9 100%);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .admin-title {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        /* Stats Grid Styles */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .stat-title {
            color: #666;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }

        /* Quick Actions Styles */
        .quick-actions {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            padding: 1rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* Tambahkan CSS untuk badge notifikasi */
        .badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: auto;
        }

        .menu-item {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="admin-logo">
                <i class="fas fa-spa"></i>
                <span>Admin Panel</span>
            </div>
            <ul class="admin-menu">
                <li class="menu-item">
                    <a href="index.php" class="active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="products.php">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="articles.php">
                        <i class="fas fa-newspaper"></i>
                        <span>Artikel</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="schedules.php">
                        <i class="fas fa-calendar"></i>
                        <span>Jadwal</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="feedback.php">
                        <i class="fas fa-comments"></i>
                        <span>Feedback</span>
                        <?php
                        // Hitung jumlah feedback yang belum dibaca
                        $unread_query = "SELECT COUNT(*) as count FROM feedback WHERE is_read = FALSE";
                        $unread_result = $conn->query($unread_query);
                        $unread_count = $unread_result->fetch_assoc()['count'];
                        
                        if($unread_count > 0) {
                            echo "<span class='badge'>$unread_count</span>";
                        }
                        ?>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1 class="admin-title">Dashboard</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo $_SESSION['admin_username']; ?></span>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-box stat-icon"></i>
                    <h3 class="stat-title">Total Produk</h3>
                    <div class="stat-value"><?php echo $stats['products']; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-newspaper stat-icon"></i>
                    <h3 class="stat-title">Total Artikel</h3>
                    <div class="stat-value"><?php echo $stats['articles']; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-star stat-icon"></i>
                    <h3 class="stat-title">Total Review</h3>
                    <div class="stat-value"><?php echo $stats['reviews']; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar stat-icon"></i>
                    <h3 class="stat-title">Total Jadwal</h3>
                    <div class="stat-value"><?php echo $stats['schedules']; ?></div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2 class="section-title">Aksi Cepat</h2>
                <div class="actions-grid">
                    <a href="add_product.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        Tambah Produk
                    </a>
                    <a href="add_article.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        Tambah Artikel
                    </a>
                    <a href="products.php" class="action-btn">
                        <i class="fas fa-list"></i>
                        Kelola Produk
                    </a>
                    <a href="articles.php" class="action-btn">
                        <i class="fas fa-list"></i>
                        Kelola Artikel
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 