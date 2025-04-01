<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Get statistics
$stats = [
    'products' => $conn->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'articles' => $conn->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    'reviews' => $conn->query("SELECT COUNT(*) FROM product_reviews")->fetchColumn(),
    'schedules' => $conn->query("SELECT COUNT(*) FROM schedules")->fetchColumn()
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Skincare Assistant</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="admin-container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-box"></i>
                <h3>Total Produk</h3>
                <p><?php echo $stats['products']; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-newspaper"></i>
                <h3>Total Artikel</h3>
                <p><?php echo $stats['articles']; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-star"></i>
                <h3>Total Review</h3>
                <p><?php echo $stats['reviews']; ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar"></i>
                <h3>Total Jadwal</h3>
                <p><?php echo $stats['schedules']; ?></p>
            </div>
        </div>

        <div class="quick-actions">
            <h2>Aksi Cepat</h2>
            <div class="action-buttons">
                <a href="products/add.php" class="btn">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
                <a href="articles/add.php" class="btn">
                    <i class="fas fa-plus"></i> Tambah Artikel
                </a>
                <a href="products/manage.php" class="btn">
                    <i class="fas fa-list"></i> Kelola Produk
                </a>
                <a href="articles/manage.php" class="btn">
                    <i class="fas fa-list"></i> Kelola Artikel
                </a>
            </div>
        </div>
    </div>
</body>
</html> 