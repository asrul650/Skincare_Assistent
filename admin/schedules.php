<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Cek dan tambahkan admin ke tabel users jika belum ada
$get_admin_user = "SELECT id FROM users WHERE username = 'admin'";
$result = $conn->query($get_admin_user);

if ($result->num_rows == 0) {
    // Jika admin belum ada di tabel users, tambahkan
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (username, email, password, status) 
                    VALUES ('admin', 'admin@example.com', ?, 'active')";
    $stmt = $conn->prepare($insert_admin);
    $stmt->bind_param("s", $hashed_password);
    $stmt->execute();
    
    $admin_user_id = $conn->insert_id;
} else {
    // Jika admin sudah ada, ambil ID-nya
    $admin_user = $result->fetch_assoc();
    $admin_user_id = $admin_user['id'];
}

// Handle Save Schedule
if (isset($_POST['save_schedule'])) {
    if (empty($_POST['routine_name']) || empty($_POST['notes']) || empty($_POST['schedule_time'])) {
        echo "<script>alert('Semua field harus diisi!');</script>";
    } else {
        $routine_name = $_POST['routine_name'];
        $notes = $_POST['notes'];
        $schedule_time = $_POST['schedule_time'];

        if (isset($_POST['schedule_id']) && !empty($_POST['schedule_id'])) {
            // Update existing schedule
            $schedule_id = $_POST['schedule_id'];
            $query = "UPDATE schedules SET routine_name=?, notes=?, schedule_time=? WHERE id=? AND user_id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssii", $routine_name, $notes, $schedule_time, $schedule_id, $admin_user_id);
        } else {
            // Add new schedule
            $query = "INSERT INTO schedules (routine_name, notes, schedule_time, user_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $routine_name, $notes, $schedule_time, $admin_user_id);
        }
        
        if ($stmt->execute()) {
            echo "<script>alert('Jadwal berhasil " . (isset($_POST['schedule_id']) ? "diupdate" : "ditambahkan") . "!');</script>";
            echo "<script>window.location.href = 'schedules.php';</script>";
        } else {
            echo "<script>alert('Gagal " . (isset($_POST['schedule_id']) ? "mengupdate" : "menambahkan") . " jadwal!');</script>";
        }
    }
}

// Handle Delete Schedule
if (isset($_POST['delete_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    $admin_id = $_SESSION['admin_id'];
    
    $query = "DELETE FROM schedules WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $schedule_id, $admin_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Jadwal berhasil dihapus!');</script>";
        echo "<script>window.location.href = 'schedules.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus jadwal!');</script>";
    }
}

// Update query untuk menampilkan jadwal
$query = "SELECT * FROM schedules WHERE user_id = ? ORDER BY schedule_time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        /* Schedules Section */
        .schedules-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .add-btn {
            padding: 0.8rem 1.5rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
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

        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
        }

        .btn-edit:hover {
            background: #d68910;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
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
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 10px;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #333;
        }

        .schedule-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #2c3e50;
        }

        .form-group input,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
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
                    <a href="schedules.php" class="active">
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
                <h1 class="admin-title">Kelola Jadwal</h1>
                <button class="add-btn" onclick="openModal()">
                    <i class="fas fa-plus"></i>
                    Tambah Jadwal
                </button>
            </div>

            <div class="schedules-section">
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Tanggal & Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$_SESSION['admin_username']}</td>";
                                    echo "<td>" . htmlspecialchars($row['routine_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                                    echo "<td>" . date('d M Y H:i', strtotime($row['schedule_time'])) . "</td>";
                                    echo "<td class='action-buttons'>
                                            <button class='btn-edit' onclick='editSchedule({$row['id']})'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                            <button class='btn-delete' onclick='deleteSchedule({$row['id']})'>
                                                <i class='fas fa-trash'></i> Hapus
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center;'>Belum ada jadwal tersedia. Silakan tambah jadwal baru.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Schedule Modal -->
    <div id="scheduleModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah Jadwal Baru</h2>
            <form class="schedule-form" method="POST">
                <input type="hidden" id="schedule_id" name="schedule_id">
                <div class="form-group">
                    <label for="routine_name">Nama Rutinitas</label>
                    <input type="text" id="routine_name" name="routine_name" required>
                </div>
                <div class="form-group">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" required></textarea>
                </div>
                <div class="form-group">
                    <label for="schedule_time">Tanggal & Waktu</label>
                    <input type="datetime-local" id="schedule_time" name="schedule_time" required>
                </div>
                <button type="submit" name="save_schedule" class="add-btn">
                    <i class="fas fa-save"></i>
                    Simpan Jadwal
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(scheduleId = null) {
            document.getElementById('scheduleModal').style.display = 'block';
            if (scheduleId) {
                document.getElementById('modalTitle').textContent = 'Edit Jadwal';
                fetch(`get_schedule.php?id=${scheduleId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('schedule_id').value = data.id;
                        document.getElementById('routine_name').value = data.routine_name;
                        document.getElementById('notes').value = data.notes;
                        document.getElementById('schedule_time').value = data.schedule_time.slice(0, 16);
                    });
            } else {
                document.getElementById('modalTitle').textContent = 'Tambah Jadwal Baru';
                document.getElementById('schedule_id').value = '';
                document.querySelector('.schedule-form').reset();
            }
        }

        function closeModal() {
            document.getElementById('scheduleModal').style.display = 'none';
        }

        function editSchedule(id) {
            openModal(id);
        }

        function deleteSchedule(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Jadwal yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="delete_schedule" value="1">
                        <input type="hidden" name="schedule_id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('scheduleModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html> 