<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Delete User
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    
    try {
        // Mulai transaction
        $conn->begin_transaction();

        // Hapus data dari tabel terkait
        $related_tables = ['skin_analysis', 'consultations', 'schedules', 'skincare_routines', 'reviews', 'product_reviews'];
        foreach ($related_tables as $table) {
            $query = "DELETE FROM $table WHERE user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        }

        // Terakhir, hapus user
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "User berhasil dihapus";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Gagal menghapus user: " . $e->getMessage();
    }

    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna | Admin Panel</title>
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

        /* Users Section */
        .users-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-delete {
            padding: 0.5rem 1rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-active {
            background: #2ecc71;
            color: white;
        }

        .status-inactive {
            background: #e74c3c;
            color: white;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                    <a href="users.php" class="active">
                        <i class="fas fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="feedback.php">
                        <i class="fas fa-comments"></i>
                        <span>Feedback</span>
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
                <h1 class="admin-title">Kelola Pengguna</h1>
                <div class="user-info">
                    <span>Selamat datang, <?php echo $_SESSION['admin_username']; ?></span>
                </div>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="users-section">
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Tanggal Registrasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php
                            $query = "SELECT *, CASE 
                                        WHEN status = 'active' THEN 'Aktif'
                                        WHEN status = 'inactive' THEN 'Tidak Aktif'
                                        END as status_text 
                                      FROM users 
                                      ORDER BY created_at DESC";
                            $result = $conn->query($query);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $statusClass = $row['status'] == 'active' ? 'status-active' : 'status-inactive';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . date('d M Y', strtotime($row['created_at'])) . "</td>";
                                    echo "<td><span class='status-badge {$statusClass}'>{$row['status_text']}</span></td>";
                                    echo "<td class='action-buttons'>
                                            <button class='btn-delete' onclick='deleteUser({$row['id']})'>
                                                <i class='fas fa-trash'></i> Hapus
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center;'>Tidak ada pengguna terdaftar</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteUser(id) {
            if (confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua data terkait pengguna ini juga akan dihapus.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_user" value="1">
                    <input type="hidden" name="user_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function refreshUserTable() {
            fetch('get_users_status.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('usersTableBody').innerHTML = html;
                });
        }

        // Refresh setiap 5 detik
        setInterval(refreshUserTable, 5000);
    </script>
</body>
</html> 