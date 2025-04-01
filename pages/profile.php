<?php
session_start();
require_once '../config/db.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit;
}

// Mencegah akses langsung ke file tanpa login
if (!isset($_SESSION['username'])) {
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

// Anti-CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Cek apakah user ada di database
if (!$user) {
    // Jika user tidak ditemukan, redirect ke login
    session_destroy();
    header("Location: login_user.php");
    exit;
}

// Ambil data consultations
$consultations = [];
$consultation_query = "SELECT * FROM consultations WHERE user_id = ? ORDER BY consultation_date DESC LIMIT 5";
$stmt = $conn->prepare($consultation_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$consultations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil data skin analysis
$analyses = [];
$analysis_query = "SELECT * FROM skin_analysis WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($analysis_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$analyses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Update skin_type di tabel users jika belum ada
if (empty($user['skin_type']) && !empty($analyses)) {
    $latest_analysis = reset($analyses); // Ambil analisis terbaru
    $update_skin_type = "UPDATE users SET skin_type = ? WHERE id = ?";
    $stmt = $conn->prepare($update_skin_type);
    $stmt->bind_param("si", $latest_analysis['skin_type'], $user_id);
    $stmt->execute();
    $user['skin_type'] = $latest_analysis['skin_type'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil | Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/feedback.css">
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
            min-height: 100vh;
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

        /* Profile Specific Styles */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--border-radius);
            margin: 2rem auto;
            box-shadow: var(--box-shadow);
            text-align: center;
            max-width: 1200px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 20px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .profile-sidebar {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            height: fit-content;
            transition: transform 0.3s ease;
        }

        .profile-sidebar:hover {
            transform: translateY(-5px);
        }

        .profile-section {
            background: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease;
            margin-top: 20px;
        }

        .profile-section:hover {
            transform: translateY(-5px);
        }

        .profile-section h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            border-bottom: 2px solid var(--background-color);
            padding-bottom: 1rem;
        }

        .profile-info {
            display: grid;
            gap: 1.5rem;
        }

        .info-group {
            display: grid;
            gap: 0.5rem;
            padding: 1rem 0;
            border-bottom: 1px solid var(--background-color);
            transition: all 0.3s ease;
        }

        .info-group:hover {
            background: var(--background-color);
            padding: 1rem;
            border-radius: 10px;
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-value {
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .edit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            margin-top: 2rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .history-list {
            display: grid;
            gap: 1rem;
        }

        .history-item {
            background: var(--background-color);
            padding: 1.5rem;
            border-radius: 10px;
            display: grid;
            gap: 0.8rem;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .history-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .history-date {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .history-title {
            color: var(--text-color);
            font-weight: 500;
            font-size: 1.1rem;
        }

        .history-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            width: fit-content;
        }

        .status-completed {
            background: #2ecc71;
            color: white;
        }

        .status-pending {
            background: #f1c40f;
            color: white;
        }

        .skin-type-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            background: var(--primary-color);
            color: white;
            margin-top: 0.5rem;
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
            overflow-y: auto;
            padding: 20px;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 500px;
            margin: 20px auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            position: relative;
            animation: modalSlideIn 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
        }

        .edit-form {
            display: grid;
            gap: 1.5rem;
            padding-bottom: 20px;
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
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--background-color);
        }

        .close-modal:hover {
            background: var(--primary-color);
            color: white;
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
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

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                text-align: center;
                gap: 0.5rem;
            }

            .user-info {
                flex-direction: column;
                width: 100%;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-header {
                margin: 1rem;
                padding: 2rem 1rem;
            }

            .profile-avatar {
                width: 120px;
                height: 120px;
            }

            .modal {
                padding: 10px;
            }
            
            .modal-content {
                margin: 10px auto;
                padding: 1.5rem;
                max-height: calc(100vh - 20px);
            }
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 10px auto;
            border: 3px solid var(--primary-color);
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        #avatar {
            display: none;
        }

        .upload-container {
            text-align: center;
            margin-top: 15px;
        }

        .upload-btn {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .upload-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        #selected-file {
            display: block;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }

        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
        }

        .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-preview::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .avatar-preview:hover::after {
            opacity: 1;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            background: var(--background-color);
            border-radius: 10px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .empty-state p {
            margin: 0;
            line-height: 1.5;
        }

        .empty-state a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Tambahkan CSS untuk feedback modal */
        .rating-container {
            text-align: center;
            margin: 1rem 0;
        }

        .stars {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            font-size: 2rem;
            margin: 1rem 0;
        }

        .stars i {
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .stars i:hover {
            transform: scale(1.2);
        }

        #feedback-message {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 1rem;
        }

        .submit-feedback-btn {
            background: #2ecc71;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            border: none;
            width: 100%;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-feedback-btn:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }

        .badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-container">
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
                    <li><a href="consultation.php"><i class="fas fa-stethoscope"></i> Konsultasi</a></li>
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
            
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php
                $avatar_path = $user['avatar'] ? '../' . $user['avatar'] : '../assets/images/default-avatar.png';
                ?>
                <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Profile Picture" id="current-avatar">
            </div>
            <h2>Selamat datang, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
        </div>

        <div class="profile-grid">
            <div class="profile-sidebar">
                <div class="profile-section">
                    <h2><i class="fas fa-user"></i> Informasi Pribadi</h2>
                    <div class="profile-info">
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-user-circle"></i> Nama Lengkap</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-phone"></i> No. Telepon</span>
                            <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-calendar-alt"></i> Tanggal Lahir</span>
                            <span class="info-value"><?php echo $user['birthdate'] ? date('d F Y', strtotime($user['birthdate'])) : '-'; ?></span>
                        </div>
                        <div class="info-group">
                            <span class="info-label"><i class="fas fa-smile"></i> Jenis Kulit</span>
                            <span class="skin-type-tag">
                                <i class="fas fa-check-circle"></i>
                                <?php echo htmlspecialchars($user['skin_type'] ?? 'Belum dianalisis'); ?>
                            </span>
                        </div>
                    </div>
                    <button class="edit-btn" onclick="openEditModal()">
                        <i class="fas fa-edit"></i>
                        Edit Profil
                    </button>
                </div>
            </div>

            <div class="profile-main">
                <div class="profile-section">
                    <h2><i class="fas fa-history"></i> Riwayat Konsultasi</h2>
                    <div class="history-list">
                        <?php if (empty($consultations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <p>Belum ada riwayat konsultasi. Anda dapat membuat janji konsultasi baru di menu <a href="consultation.php">Konsultasi</a>.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($consultations as $consultation): ?>
                            <div class="history-item">
                                <span class="history-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d F Y', strtotime($consultation['consultation_date'])); ?>
                                </span>
                                <span class="history-title">
                                    <i class="fas fa-user-md"></i>
                                    Konsultasi dengan <?php echo htmlspecialchars($consultation['doctor_name']); ?>
                                </span>
                                <span class="history-status <?php echo $consultation['status'] == 'completed' ? 'status-completed' : 'status-pending'; ?>">
                                    <i class="fas <?php echo $consultation['status'] == 'completed' ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                                    <?php echo $consultation['status'] == 'completed' ? 'Selesai' : 'Menunggu'; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-section">
                    <h2><i class="fas fa-chart-line"></i> Riwayat Analisis Kulit</h2>
                    <div class="history-list">
                        <?php if (empty($analyses)): ?>
                            <p>Belum ada riwayat analisis kulit</p>
                        <?php else: ?>
                            <?php foreach ($analyses as $analysis): ?>
                            <div class="history-item">
                                <span class="history-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d F Y', strtotime($analysis['created_at'])); ?>
                                </span>
                                <span class="history-title">
                                    <i class="fas fa-flask"></i>
                                    Jenis Kulit: <?php echo htmlspecialchars($analysis['skin_type']); ?>
                                </span>
                                <?php if (!empty($analysis['concerns'])): ?>
                                    <div class="concerns">
                                        <strong>Keluhan:</strong>
                                        <?php 
                                        $concerns = json_decode($analysis['concerns'], true);
                                        if (is_array($concerns)) {
                                            echo htmlspecialchars(implode(', ', $concerns));
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Edit Profil -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Profil</h2>
            <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="avatar">Foto Profil:</label>
                    <div class="avatar-preview">
                        <img src="<?php echo htmlspecialchars($avatar_path); ?>" alt="Preview" id="avatar-preview">
                    </div>
                    <div class="upload-container">
                        <label for="avatar" class="upload-btn">
                            <i class="fas fa-camera"></i>
                            Pilih Foto
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)" style="display: none;">
                        <span id="selected-file">Belum ada file dipilih</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i>
                        Nama Lengkap
                    </label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                           placeholder="Masukkan nama lengkap">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i>
                        No. Telepon
                    </label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           placeholder="Masukkan nomor telepon">
                </div>

                <div class="form-group">
                    <label for="birthdate">
                        <i class="fas fa-calendar"></i>
                        Tanggal Lahir
                    </label>
                    <input type="date" id="birthdate" name="birthdate" 
                           value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
                </div>

                <button type="submit" class="edit-btn">
                    <i class="fas fa-save"></i>
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Feedback -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeFeedbackModal()">&times;</span>
            <h2>Berikan Feedback</h2>
            <div class="feedback-form">
                <div class="rating-container">
                    <p>Berikan Rating:</p>
                    <div class="stars">
                        <i class="fas fa-star" data-rating="1"></i>
                        <i class="fas fa-star" data-rating="2"></i>
                        <i class="fas fa-star" data-rating="3"></i>
                        <i class="fas fa-star" data-rating="4"></i>
                        <i class="fas fa-star" data-rating="5"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="feedback-message">Pesan Feedback:</label>
                    <textarea id="feedback-message" rows="4" placeholder="Tulis feedback Anda di sini..."></textarea>
                </div>
                <button type="button" class="submit-feedback-btn" onclick="submitFeedback()">
                    <i class="fas fa-paper-plane"></i> Kirim Feedback
                </button>
            </div>
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
        function openEditModal() {
            document.getElementById('editProfileModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        function previewImage(input) {
            const preview = document.getElementById('avatar-preview');
            const selectedFile = document.getElementById('selected-file');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    selectedFile.textContent = input.files[0].name;
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '<?php echo htmlspecialchars($avatar_path); ?>';
                selectedFile.textContent = 'Belum ada file dipilih';
            }
        }

        // Ubah script untuk feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Hapus pengecekan localStorage dan langsung tampilkan modal feedback
            setTimeout(function() {
                openFeedbackModal();
            }, 1000);
        });

        function openFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'block';
            // Reset rating dan pesan setiap kali modal dibuka
            currentRating = 0;
            document.getElementById('feedback-message').value = '';
            document.querySelectorAll('.stars i').forEach(s => {
                s.style.color = '#ddd';
            });
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
        }

        // Handle star rating
        let currentRating = 0;
        document.querySelectorAll('.stars i').forEach(star => {
            star.addEventListener('click', () => {
                currentRating = parseInt(star.dataset.rating);
                document.querySelectorAll('.stars i').forEach(s => {
                    s.style.color = s.dataset.rating <= currentRating ? '#f1c40f' : '#ddd';
                });
            });

            star.addEventListener('mouseover', () => {
                const rating = parseInt(star.dataset.rating);
                document.querySelectorAll('.stars i').forEach(s => {
                    s.style.color = s.dataset.rating <= rating ? '#f1c40f' : '#ddd';
                });
            });

            star.addEventListener('mouseout', () => {
                document.querySelectorAll('.stars i').forEach(s => {
                    s.style.color = s.dataset.rating <= currentRating ? '#f1c40f' : '#ddd';
                });
            });
        });

        function submitFeedback() {
            const message = document.getElementById('feedback-message').value;

            if (!currentRating) {
                alert('Silakan berikan rating');
                return;
            }

            fetch('../api/submit_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `rating=${currentRating}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    closeFeedbackModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim feedback');
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const feedbackModal = document.getElementById('feedbackModal');
            const editModal = document.getElementById('editProfileModal');
            if (event.target == feedbackModal) {
                closeFeedbackModal();
            }
            if (event.target == editModal) {
                closeModal();
            }
        }
    </script>
</body>
</html> 