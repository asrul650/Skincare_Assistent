<?php
session_start();
require_once '../config/db.php';

// Cek apakah admin sudah login
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil data feedback
$query = "SELECT f.*, u.username, u.full_name, u.email 
          FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          ORDER BY f.created_at DESC";
$result = $conn->query($query);
$feedbacks = $result->fetch_all(MYSQLI_ASSOC);

// Update status feedback menjadi telah dibaca
$update_query = "UPDATE feedback SET is_read = TRUE WHERE is_read = FALSE";
$conn->query($update_query);

// Hitung statistik feedback
$stats_query = "SELECT 
                COUNT(*) as total_feedback,
                AVG(rating) as avg_rating,
                COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_feedback,
                COUNT(CASE WHEN rating <= 2 THEN 1 END) as negative_feedback
                FROM feedback";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Users | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: #f6f9fc;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles - Sama seperti index.php */
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* Feedback Content Styles */
        .feedback-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .feedback-filters {
            display: flex;
            gap: 1rem;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background: #f1f1f1;
            color: #666;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: #3498db;
            color: white;
        }

        .feedback-grid {
            display: grid;
            gap: 1.5rem;
        }

        .feedback-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease;
        }

        .feedback-card:hover {
            transform: translateY(-5px);
        }

        .feedback-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .user-name {
            font-weight: bold;
            color: #2c3e50;
        }

        .user-email {
            color: #666;
            font-size: 0.9rem;
        }

        .rating {
            display: flex;
            gap: 0.2rem;
        }

        .star {
            color: #f1c40f;
        }

        .feedback-message {
            color: #2c3e50;
            line-height: 1.6;
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 5px;
        }

        .feedback-date {
            color: #666;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .export-btn {
            background: #2ecc71;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
            margin-top: 20px;
        }

        .action-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Tambahkan di bagian style setiap file admin */
        .badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: auto;
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

        .menu-item a.active .badge {
            background: white;
            color: #3498db;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar - Sama seperti index.php -->
        <div class="sidebar">
            <div class="admin-logo">
                <i class="fas fa-spa"></i>
                <span>Admin Panel</span>
            </div>
            <ul class="admin-menu">
                <li class="menu-item">
                    <a href="index.php">
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
                    <a href="feedback.php" <?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'class="active"' : ''; ?>>
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
                <h1 class="admin-title">Feedback Users</h1>
                <button class="action-btn export-btn" onclick="exportFeedback()">
                    <i class="fas fa-download"></i> Export Excel
                </button>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-comments stat-icon" style="color: #3498db"></i>
                    <div class="stat-value"><?php echo $stats['total_feedback']; ?></div>
                    <div class="stat-label">Total Feedback</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-star stat-icon" style="color: #f1c40f"></i>
                    <div class="stat-value"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                    <div class="stat-label">Rating Rata-rata</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-thumbs-up stat-icon" style="color: #2ecc71"></i>
                    <div class="stat-value"><?php echo $stats['positive_feedback']; ?></div>
                    <div class="stat-label">Feedback Positif</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-thumbs-down stat-icon" style="color: #e74c3c"></i>
                    <div class="stat-value"><?php echo $stats['negative_feedback']; ?></div>
                    <div class="stat-label">Feedback Negatif</div>
                </div>
            </div>

            <!-- Feedback Content -->
            <div class="feedback-container">
                <div class="feedback-header">
                    <div class="feedback-filters">
                        <button class="filter-btn active" data-filter="all">Semua</button>
                        <button class="filter-btn" data-filter="positive">Positif (4-5 ⭐)</button>
                        <button class="filter-btn" data-filter="negative">Negatif (1-2 ⭐)</button>
                    </div>
                </div>

                <div class="feedback-grid">
                    <?php if (empty($feedbacks)): ?>
                        <div class="feedback-card">
                            <p>Belum ada feedback dari users.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <div class="feedback-card" data-rating="<?php echo $feedback['rating']; ?>">
                                <div class="feedback-info">
                                    <div class="user-info">
                                        <div class="user-name">
                                            <i class="fas fa-user-circle"></i>
                                            <?php echo htmlspecialchars($feedback['full_name'] ?: $feedback['username']); ?>
                                        </div>
                                        <div class="user-email">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($feedback['email']); ?>
                                        </div>
                                    </div>
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star star" style="color: <?php echo $i <= $feedback['rating'] ? '#f1c40f' : '#ddd'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="feedback-message">
                                    <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                                </div>
                                <div class="feedback-date">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d F Y H:i', strtotime($feedback['created_at'])); ?>
                                </div>
                                <button class="action-btn delete-btn" onclick="deleteFeedback(<?php echo $feedback['id']; ?>)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filter feedback
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filter = btn.dataset.filter;
                document.querySelectorAll('.feedback-card').forEach(card => {
                    const rating = parseInt(card.dataset.rating);
                    if (filter === 'all' ||
                        (filter === 'positive' && rating >= 4) ||
                        (filter === 'negative' && rating <= 2)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Delete feedback
        function deleteFeedback(id) {
            if (confirm('Apakah Anda yakin ingin menghapus feedback ini?')) {
                fetch(`delete_feedback.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert('Gagal menghapus feedback');
                    }
                });
            }
        }

        // Export feedback
        function exportFeedback() {
            window.location.href = 'export_feedback.php';
        }
    </script>
</body>
</html> 