<?php
session_start();
require_once '../config/db.php';
require_once '../database/connection.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit;
}

// Ambil data user
$user_id = $_SESSION['user_id'];

// Tambahkan ini untuk debugging
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_routine'])) {
    $check_user = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $routine_name = $_POST['routine_name'];
        $product_name = $_POST['product_name'];
        $frequency = $_POST['frequency'];
        $time_of_day = $_POST['time_of_day'];
        $schedule_time = $_POST['schedule_time'];
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';

        // Gabungkan tanggal hari ini dengan waktu yang dipilih
        $schedule_datetime = date('Y-m-d') . ' ' . $schedule_time;

        try {
            // Perbaiki query insert sesuai struktur tabel
            $insert_query = "INSERT INTO schedules (user_id, routine_name, schedule_time, notes) 
                            VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isss", $user_id, $routine_name, $schedule_datetime, $notes);
            
            if ($stmt->execute()) {
                header("Location: schedule.php");
                exit;
            } else {
                echo "<script>alert('Gagal menambahkan rutinitas: " . $conn->error . "');</script>";
            }
        } catch (Exception $e) {
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        }
    }
}

// Ambil data skincare routines
$routines = [];
$query = "SELECT * FROM schedules WHERE user_id = ? ORDER BY schedule_time ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$routines = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Skincare | Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
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
            background: var(--background-color);
            padding-top: 80px;
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
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .schedule-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            text-align: center;
        }

        .schedule-header h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .routine-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
            margin-top: 20px;
        }

        .routine-card:hover {
            transform: translateY(-5px);
        }

        .routine-time {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .routine-name {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .routine-details {
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .routine-frequency {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .add-routine-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .add-routine-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            max-width: 500px;
            margin: 2rem auto;
            position: relative;
        }

        .close {
            position: absolute;
            right: 1rem;
            top: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: var(--secondary-color);
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

            .schedule-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                margin: 1rem;
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
                    <li><a href="home.php"><i class="fas fa-home"></i> Beranda</a></li>
                    <li><a href="skin_analysis.php"><i class="fas fa-microscope"></i> Analisis Kulit</a></li>
                    <li><a href="products.php"><i class="fas fa-pump-soap"></i> Produk</a></li>
                    <li><a href="schedule.php" class="active"><i class="fas fa-calendar"></i> Jadwal</a></li>
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

    <div class="container">
        <div class="schedule-header">
            <h1><i class="fas fa-calendar-alt"></i> Jadwal Skincare Rutin</h1>
            <p>Atur rutinitas skincare Anda untuk hasil yang optimal</p>
        </div>

        <button class="add-routine-btn" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah Rutinitas Baru
        </button>

        <div class="schedule-grid">
            <?php if (empty($routines)): ?>
                <div class="routine-card">
                    <p>Belum ada rutinitas skincare. Mulai tambahkan sekarang!</p>
                </div>
            <?php else: ?>
                <?php foreach ($routines as $routine): ?>
                    <div class="routine-card">
                        <div class="routine-time">
                            <i class="fas fa-clock"></i>
                            <?php 
                                $schedule_time = new DateTime($routine['schedule_time']);
                                echo $schedule_time->format('H:i'); 
                            ?>
                        </div>
                        <div class="routine-name">
                            <?php echo htmlspecialchars($routine['routine_name']); ?>
                        </div>
                        <div class="routine-details">
                            <?php if(isset($routine['notes']) && !empty($routine['notes'])): ?>
                                <p><i class="fas fa-sticky-note"></i> Catatan: <?php echo htmlspecialchars($routine['notes']); ?></p>
                            <?php endif; ?>
                            <p><i class="fas fa-calendar"></i> Tanggal: <?php echo $schedule_time->format('d-m-Y'); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Tambah Rutinitas -->
    <div id="addRoutineModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Tambah Rutinitas Baru</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="routine_name">Nama Rutinitas</label>
                    <input type="text" id="routine_name" name="routine_name" required 
                           placeholder="Contoh: Pembersihan Pagi">
                </div>

                <div class="form-group">
                    <label for="product_name">Nama Produk</label>
                    <input type="text" id="product_name" name="product_name" required
                           placeholder="Contoh: Facial Wash Brand A">
                </div>

                <div class="form-group">
                    <label for="frequency">Frekuensi</label>
                    <select id="frequency" name="frequency" required>
                        <option value="Setiap Hari">Setiap Hari</option>
                        <option value="2x Seminggu">2x Seminggu</option>
                        <option value="3x Seminggu">3x Seminggu</option>
                        <option value="Mingguan">Mingguan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="schedule_time">Waktu</label>
                    <input type="time" id="schedule_time" name="schedule_time" required>
                </div>

                <div class="form-group">
                    <label for="time_of_day">Periode</label>
                    <select id="time_of_day" name="time_of_day" required>
                        <option value="Pagi">Pagi</option>
                        <option value="Siang">Siang</option>
                        <option value="Malam">Malam</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Catatan</label>
                    <textarea id="notes" name="notes" 
                              placeholder="Tambahkan catatan atau instruksi khusus"></textarea>
                </div>

                <button type="submit" name="add_routine" class="submit-btn">
                    <i class="fas fa-save"></i> Simpan Rutinitas
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addRoutineModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addRoutineModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addRoutineModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

    <main>
        <!-- Rest of the existing content -->
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

    <style>
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
    </style>
</body>
</html> 