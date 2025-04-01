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

// Data dokter spesialis (nantinya bisa diambil dari database)
$doctors = [
    [
        'name' => 'Dr. Sarah Wijaya, Sp.KK',
        'specialty' => 'Dokter Spesialis Kulit dan Kelamin',
        'experience' => '10 tahun',
        'education' => 'Universitas Indonesia',
        'description' => 'Spesialis dalam perawatan kulit sensitif dan masalah jerawat',
        'image' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-1.2.1&auto=format&fit=crop&w=400',
        'schedule' => 'Senin - Jumat: 09:00 - 17:00',
        'rating' => 4.8,
        'patients' => 1000,
        'price' => 300000
    ],
    [
        'name' => 'Dr. Ahmad Rahman, Sp.KK',
        'specialty' => 'Dokter Spesialis Kulit dan Kosmetik',
        'experience' => '8 tahun',
        'education' => 'Universitas Gadjah Mada',
        'description' => 'Ahli dalam perawatan anti-aging dan terapi laser',
        'image' => 'https://images.unsplash.com/photo-1537368910025-700350fe46c7?ixlib=rb-1.2.1&auto=format&fit=crop&w=400',
        'schedule' => 'Selasa - Sabtu: 10:00 - 18:00',
        'rating' => 4.7,
        'patients' => 850,
        'price' => 275000
    ],
    [
        'name' => 'Dr. Linda Kusuma, Sp.KK',
        'specialty' => 'Dokter Spesialis Dermatologi Estetik',
        'experience' => '12 tahun',
        'education' => 'Universitas Airlangga',
        'description' => 'Spesialis dalam perawatan pigmentasi dan rejuvenasi kulit',
        'image' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?ixlib=rb-1.2.1&auto=format&fit=crop&w=400',
        'schedule' => 'Senin - Kamis: 08:00 - 16:00',
        'rating' => 4.9,
        'patients' => 1200,
        'price' => 350000
    ],
    [
        'name' => 'Dr. Budi Santoso, Sp.KK',
        'specialty' => 'Dokter Spesialis Alergi Kulit',
        'experience' => '15 tahun',
        'education' => 'Universitas Padjadjaran',
        'description' => 'Ahli dalam menangani masalah alergi dan eksim',
        'image' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?ixlib=rb-1.2.1&auto=format&fit=crop&w=400',
        'schedule' => 'Rabu - Minggu: 09:00 - 17:00',
        'rating' => 4.6,
        'patients' => 950,
        'price' => 325000
    ]
];

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
    <title>Konsultasi | Skincare Assistant</title>
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
            padding-top: 80px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
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

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--border-radius);
            margin: 2rem auto;
            text-align: center;
            box-shadow: var(--box-shadow);
        }

        .welcome-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Doctors Grid */
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem auto;
        }

        .doctor-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .doctor-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .doctor-info {
            padding: 1.5rem;
        }

        .doctor-name {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .doctor-specialty {
            color: var(--primary-color);
            font-size: 1rem;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .doctor-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1rem 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }

        .stat {
            text-align: center;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        .doctor-schedule {
            background: var(--background-color);
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .schedule-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .book-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            width: 100%;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .book-btn:hover {
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
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: #e74c3c;
            transform: rotate(90deg);
        }

        .booking-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group label {
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        /* Footer Styles */
        footer {
            background: var(--text-color);
            color: white;
            padding: 4rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-section h3 {
            color: var(--primary-color);
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
            color: var(--primary-color);
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

        /* Responsive Design */
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
                padding: 2rem 1rem;
                margin: 1rem;
            }

            .welcome-banner h1 {
                font-size: 2rem;
            }

            .features-grid,
            .doctors-grid {
                grid-template-columns: 1fr;
                margin: 2rem 1rem;
            }

            .modal-content {
                margin: 1rem;
                padding: 1.5rem;
            }
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: var(--border-radius);
            text-align: center;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.3);
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
                    <li><a href="schedule.php"><i class="fas fa-calendar"></i> Jadwal</a></li>
                    <li><a href="consultation.php" class="active"><i class="fas fa-stethoscope"></i> Konsultasi</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
            
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="welcome-banner">
            <h1>Konsultasi dengan Dokter Spesialis</h1>
            <p>Konsultasikan masalah kulit Anda dengan dokter spesialis terpercaya</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-user-md"></i>
                <h3>Dokter Berpengalaman</h3>
                <p>Konsultasi dengan dokter spesialis kulit berpengalaman dan tersertifikasi</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h3>Fleksibel</h3>
                <p>Pilih jadwal konsultasi yang sesuai dengan waktu Anda</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-comments"></i>
                <h3>Konsultasi Online</h3>
                <p>Konsultasi dapat dilakukan secara online melalui video call</p>
            </div>
        </div>

        <div class="doctors-grid">
            <?php foreach ($doctors as $doctor): ?>
            <div class="doctor-card">
                <img src="<?php echo htmlspecialchars($doctor['image']); ?>" 
                     alt="<?php echo htmlspecialchars($doctor['name']); ?>" 
                     class="doctor-image">
                
                <div class="doctor-info">
                    <h3 class="doctor-name"><?php echo htmlspecialchars($doctor['name']); ?></h3>
                    <p class="doctor-specialty"><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                    <p><?php echo htmlspecialchars($doctor['description']); ?></p>

                    <div class="doctor-stats">
                        <div class="stat">
                            <span class="stat-value"><?php echo htmlspecialchars($doctor['experience']); ?></span>
                            <span class="stat-label">Pengalaman</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo htmlspecialchars($doctor['rating']); ?></span>
                            <span class="stat-label">Rating</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value"><?php echo number_format($doctor['patients']); ?>+</span>
                            <span class="stat-label">Pasien</span>
                        </div>
                    </div>

                    <div class="doctor-schedule">
                        <div class="schedule-title">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Jadwal Praktik</span>
                        </div>
                        <p><?php echo htmlspecialchars($doctor['schedule']); ?></p>
                    </div>

                    <p><strong>Biaya Konsultasi:</strong> Rp <?php echo number_format($doctor['price'], 0, ',', '.'); ?></p>

                    <button class="book-btn" onclick="openBooking('<?php echo htmlspecialchars($doctor['name']); ?>')">
                        <i class="fas fa-calendar-check"></i>
                        Buat Janji Konsultasi
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal Booking -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2>Buat Janji Konsultasi</h2>
            <form class="booking-form" method="POST" action="process_booking.php">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="doctor_name" id="doctorName">
                
                <div class="form-group">
                    <label for="consultation_date">Tanggal Konsultasi</label>
                    <input type="date" id="consultation_date" name="consultation_date" required>
                </div>

                <div class="form-group">
                    <label for="consultation_time">Waktu Konsultasi</label>
                    <select id="consultation_time" name="consultation_time" required>
                        <option value="">Pilih Waktu</option>
                        <option value="09:00">09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="consultation_type">Jenis Konsultasi</label>
                    <select id="consultation_type" name="consultation_type" required>
                        <option value="online">Online (Video Call)</option>
                        <option value="offline">Offline (Tatap Muka)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="symptoms">Keluhan</label>
                    <textarea id="symptoms" name="symptoms" rows="4" required 
                              placeholder="Ceritakan keluhan kulit Anda..."></textarea>
                </div>

                <button type="submit" class="book-btn">
                    <i class="fas fa-check"></i>
                    Konfirmasi Booking
                </button>
            </form>
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
        function openBooking(doctorName) {
            document.getElementById('doctorName').value = doctorName;
            document.getElementById('bookingModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('consultation_date').min = today;

            // Form validation
            document.querySelector('.booking-form').addEventListener('submit', function(e) {
                const date = document.getElementById('consultation_date').value;
                const time = document.getElementById('consultation_time').value;
                const type = document.getElementById('consultation_type').value;
                const symptoms = document.getElementById('symptoms').value;

                if (!date || !time || !type || !symptoms.trim()) {
                    e.preventDefault();
                    alert('Mohon lengkapi semua field yang diperlukan');
                    return false;
                }

                // Validasi waktu konsultasi
                const selectedDateTime = new Date(date + ' ' + time);
                const now = new Date();
                
                if (selectedDateTime < now) {
                    e.preventDefault();
                    alert('Tidak dapat memilih waktu yang sudah lewat');
                    return false;
                }
            });
        });
    </script>
</body>
</html> 