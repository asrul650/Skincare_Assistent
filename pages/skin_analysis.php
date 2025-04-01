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
    <title>Analisis Kulit | Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        /* Skin Analysis Specific Styles */
        .analysis-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .analysis-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .analysis-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .analysis-header p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .analysis-steps {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .progress-container {
            margin-bottom: 3rem;
        }

        .progress {
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(135deg, #3498db, #2980b9);
            transition: width 0.3s ease;
        }

        .step-indicators {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .step {
            text-align: center;
            position: relative;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            color: #666;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: #3498db;
            color: white;
        }

        .step.completed .step-number {
            background: #2ecc71;
            color: white;
        }

        .step-label {
            color: #666;
            font-size: 0.9rem;
        }

        .skin-type-options, .skin-concerns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .option-card, .concern-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .option-card:hover, .concern-card:hover {
            transform: translateY(-5px);
            border-color: #3498db;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
        }

        .option-card.selected, .concern-card.selected {
            border-color: #2ecc71;
            background: #f1f9f6;
        }

        .option-icon, .concern-icon {
            width: 50px;
            height: 50px;
            background: rgba(52, 152, 219, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3498db;
            font-size: 1.5rem;
        }

        .option-content h4, .concern-content h4 {
            margin: 0 0 0.5rem;
            color: #2c3e50;
        }

        .option-content p, .concern-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .hidden-radio, .hidden-checkbox {
            display: none;
        }

        .step-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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

            .analysis-steps {
                padding: 1rem;
            }

            .step-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
                    <li><a href="skin_analysis.php" class="active"><i class="fas fa-microscope"></i> Analisis Kulit</a></li>
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
        <div class="container mt-5">
            <div class="analysis-container">
                <div class="analysis-header text-center">
                    <h1>Analisis Kulit</h1>
                    <p>Mari temukan jenis kulit dan rekomendasi perawatan yang tepat untuk Anda</p>
                </div>

                <div class="analysis-steps">
                    <!-- Progress Bar -->
                    <div class="progress-container">
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                        <div class="step-indicators">
                            <div class="step active" data-step="1">
                                <div class="step-number">1</div>
                                <div class="step-label">Jenis Kulit</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-number">2</div>
                                <div class="step-label">Keluhan</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-number">3</div>
                                <div class="step-label">Konfirmasi</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Jenis Kulit -->
                    <div class="analysis-step" id="step1">
                        <h3>Langkah 1: Tentukan Jenis Kulit Anda</h3>
                        <div class="skin-type-options">
                            <div class="option-card" data-value="oily">
                                <div class="option-icon">
                                    <i class="fas fa-tint"></i>
                                </div>
                                <div class="option-content">
                                    <h4>Berminyak</h4>
                                    <p>Kulit terasa berminyak sepanjang hari, pori-pori besar dan mudah berjerawat</p>
                                </div>
                                <input type="radio" name="skinType" value="oily" class="hidden-radio">
                            </div>

                            <div class="option-card" data-value="dry">
                                <div class="option-icon">
                                    <i class="fas fa-wind"></i>
                                </div>
                                <div class="option-content">
                                    <h4>Kering</h4>
                                    <p>Kulit terasa kering, kaku, dan mudah mengelupas</p>
                                </div>
                                <input type="radio" name="skinType" value="dry" class="hidden-radio">
                            </div>

                            <div class="option-card" data-value="combination">
                                <div class="option-icon">
                                    <i class="fas fa-adjust"></i>
                                </div>
                                <div class="option-content">
                                    <h4>Kombinasi</h4>
                                    <p>Beberapa area berminyak (T-zone) dan beberapa area kering</p>
                                </div>
                                <input type="radio" name="skinType" value="combination" class="hidden-radio">
                            </div>

                            <div class="option-card" data-value="sensitive">
                                <div class="option-icon">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="option-content">
                                    <h4>Sensitif</h4>
                                    <p>Mudah iritasi, kemerahan, dan gatal</p>
                                </div>
                                <input type="radio" name="skinType" value="sensitive" class="hidden-radio">
                            </div>

                            <div class="option-card" data-value="normal">
                                <div class="option-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="option-content">
                                    <h4>Normal</h4>
                                    <p>Tidak terlalu berminyak atau kering, jarang bermasalah</p>
                                </div>
                                <input type="radio" name="skinType" value="normal" class="hidden-radio">
                            </div>
                        </div>
                        <div class="step-buttons">
                            <button class="btn btn-primary next-step" data-next="2">Selanjutnya <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 2: Keluhan Kulit -->
                    <div class="analysis-step" id="step2" style="display: none;">
                        <h3>Langkah 2: Pilih Keluhan Kulit Anda</h3>
                        <div class="skin-concerns">
                            <div class="concern-card" data-value="acne">
                                <div class="concern-icon">
                                    <i class="fas fa-dot-circle"></i>
                                </div>
                                <div class="concern-content">
                                    <h4>Jerawat</h4>
                                    <p>Masalah jerawat dan breakouts</p>
                                </div>
                                <input type="checkbox" name="concerns[]" value="acne" class="hidden-checkbox">
                            </div>

                            <div class="concern-card" data-value="blackheads">
                                <div class="concern-icon">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div class="concern-content">
                                    <h4>Komedo</h4>
                                    <p>Komedo hitam dan putih</p>
                                </div>
                                <input type="checkbox" name="concerns[]" value="blackheads" class="hidden-checkbox">
                            </div>

                            <div class="concern-card" data-value="dark_spots">
                                <div class="concern-icon">
                                    <i class="fas fa-adjust"></i>
                                </div>
                                <div class="concern-content">
                                    <h4>Flek Hitam</h4>
                                    <p>Noda hitam dan hiperpigmentasi</p>
                                </div>
                                <input type="checkbox" name="concerns[]" value="dark_spots" class="hidden-checkbox">
                            </div>

                            <div class="concern-card" data-value="wrinkles">
                                <div class="concern-icon">
                                    <i class="fas fa-wave-square"></i>
                                </div>
                                <div class="concern-content">
                                    <h4>Kerutan</h4>
                                    <p>Garis halus dan kerutan</p>
                                </div>
                                <input type="checkbox" name="concerns[]" value="wrinkles" class="hidden-checkbox">
                            </div>

                            <div class="concern-card" data-value="dullness">
                                <div class="concern-icon">
                                    <i class="fas fa-cloud"></i>
                                </div>
                                <div class="concern-content">
                                    <h4>Kulit Kusam</h4>
                                    <p>Kulit terlihat lelah dan kusam</p>
                                </div>
                                <input type="checkbox" name="concerns[]" value="dullness" class="hidden-checkbox">
                            </div>
                        </div>
                        <div class="step-buttons">
                            <button class="btn btn-secondary prev-step" data-prev="1"><i class="fas fa-arrow-left"></i> Sebelumnya</button>
                            <button class="btn btn-primary next-step" data-next="3">Selanjutnya <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 3: Konfirmasi -->
                    <div class="analysis-step" id="step3" style="display: none;">
                        <h3>Langkah 3: Konfirmasi Analisis</h3>
                        <div class="confirmation-details">
                            <div class="confirmation-card">
                                <div class="confirmation-item">
                                    <h4>Jenis Kulit:</h4>
                                    <p id="selected-skin-type"></p>
                                </div>
                                <div class="confirmation-item">
                                    <h4>Keluhan:</h4>
                                    <p id="selected-concerns"></p>
                                </div>
                            </div>
                        </div>
                        <div class="step-buttons">
                            <button class="btn btn-secondary prev-step" data-prev="2"><i class="fas fa-arrow-left"></i> Sebelumnya</button>
                            <button class="btn btn-success" id="submit-analysis">Analisis Sekarang <i class="fas fa-check"></i></button>
                        </div>
                    </div>

                    <!-- Hasil Analisis -->
                    <div class="analysis-result" id="analysis-result" style="display: none;">
                        <div class="result-header">
                            <h3>Hasil Analisis Kulit Anda</h3>
                            <p>Berikut adalah rekomendasi perawatan berdasarkan analisis kulit Anda</p>
                        </div>
                        <div class="result-content">
                            <!-- Hasil akan ditampilkan di sini -->
                        </div>
                    </div>
                </div>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentStep = 1;
        updateProgress(currentStep);

        // Pilih jenis kulit
        $('.option-card').click(function() {
            $('.option-card').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // Pilih keluhan kulit
        $('.concern-card').click(function() {
            $(this).toggleClass('selected');
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked'));
        });

        // Next step
        $('.next-step').click(function() {
            const nextStep = parseInt($(this).data('next'));
            if (validateStep(currentStep)) {
                showStep(nextStep);
                currentStep = nextStep;
                updateProgress(currentStep);
            }
        });

        // Previous step
        $('.prev-step').click(function() {
            const prevStep = parseInt($(this).data('prev'));
            showStep(prevStep);
            currentStep = prevStep;
            updateProgress(currentStep);
        });

        // Submit analysis
        $('#submit-analysis').click(function() {
            if (validateStep(3)) {
                const skinType = $('input[name="skinType"]:checked').val();
                const concerns = [];
                $('input[name="concerns[]"]:checked').each(function() {
                    concerns.push($(this).val());
                });

                // Kirim data ke analyze-skin.php
                $.ajax({
                    url: '../api/analyze-skin.php',
                    method: 'POST',
                    data: {
                        skinType: skinType,
                        concerns: concerns
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            displayResults(response);
                        } else {
                            alert('Terjadi kesalahan: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', error);
                        alert('Terjadi kesalahan dalam menghubungi server');
                    }
                });
            }
        });

        function showStep(step) {
            $('.analysis-step').hide();
            $('#step' + step).show();
            if (step === 3) {
                updateConfirmation();
            }
        }

        function updateProgress(step) {
            const percent = ((step - 1) / 2) * 100;
            $('.progress-bar').css('width', percent + '%');
            $('.step').removeClass('active completed');
            for (let i = 1; i <= 3; i++) {
                if (i < step) {
                    $('.step[data-step="' + i + '"]').addClass('completed');
                } else if (i === step) {
                    $('.step[data-step="' + i + '"]').addClass('active');
                }
            }
        }

        function validateStep(step) {
            if (step === 1) {
                if (!$('input[name="skinType"]:checked').val()) {
                    alert('Silakan pilih jenis kulit Anda');
                    return false;
                }
            } else if (step === 2) {
                if ($('input[name="concerns[]"]:checked').length === 0) {
                    alert('Silakan pilih minimal satu keluhan kulit Anda');
                    return false;
                }
            }
            return true;
        }

        function updateConfirmation() {
            const skinType = $('input[name="skinType"]:checked').val();
            const concerns = [];
            $('input[name="concerns[]"]:checked').each(function() {
                concerns.push($(this).closest('.concern-card').find('h4').text());
            });

            $('#selected-skin-type').text(getSkinTypeName(skinType));
            $('#selected-concerns').text(concerns.length > 0 ? concerns.join(', ') : 'Tidak ada keluhan khusus');
        }

        // Tambahkan event listener untuk update konfirmasi setiap kali ada perubahan
        $('input[name="skinType"], input[name="concerns[]"]').on('change', function() {
            updateConfirmation();
        });

        function getSkinTypeName(type) {
            const types = {
                'oily': 'Berminyak',
                'dry': 'Kering',
                'combination': 'Kombinasi',
                'sensitive': 'Sensitif',
                'normal': 'Normal'
            };
            return types[type] || type;
        }

        function displayResults(result) {
            let html = `
                <div class="result-section">
                    <div class="skin-type-result">
                        <h4>Jenis Kulit: ${getSkinTypeName(result.skinType)}</h4>
                    </div>
                    
                    <div class="routine-recommendations mt-4">
                        <h4>Rekomendasi Rutinitas:</h4>
                        <div class="recommendation-card">
                            <div class="recommendation-item">
                                <i class="fas fa-pump-soap"></i>
                                <div>
                                    <h5>Pembersihan</h5>
                                    <p>${result.routineRecommendations.cleansing}</p>
                                </div>
                            </div>
                            <div class="recommendation-item">
                                <i class="fas fa-spray-can"></i>
                                <div>
                                    <h5>Toner</h5>
                                    <p>${result.routineRecommendations.toner}</p>
                                </div>
                            </div>
                            <div class="recommendation-item">
                                <i class="fas fa-tint"></i>
                                <div>
                                    <h5>Pelembab</h5>
                                    <p>${result.routineRecommendations.moisturizer}</p>
                                </div>
                            </div>
                            <div class="recommendation-item">
                                <i class="fas fa-sun"></i>
                                <div>
                                    <h5>Sunscreen</h5>
                                    <p>${result.routineRecommendations.sunscreen}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="product-recommendations mt-4">
                        <h4>Rekomendasi Produk:</h4>
                        <div class="products-grid">
            `;

            result.productRecommendations.forEach(product => {
                html += `
                    <div class="product-card">
                        <div class="product-header">
                            <h5>${product.name}</h5>
                            <span class="brand">${product.brand}</span>
                        </div>
                        <div class="product-details">
                            <p class="price">Rp ${product.price}</p>
                            <p class="description">${product.description}</p>
                            <div class="ingredients">
                                <h6>Kandungan:</h6>
                                <p>${product.ingredients}</p>
                            </div>
                            <div class="usage">
                                <h6>Cara Penggunaan:</h6>
                                <p>${product.how_to_use}</p>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                        </div>
                    </div>
                </div>
            `;

            $('.analysis-step').hide();
            $('#analysis-result').fadeIn().find('.result-content').html(html);

            // Tambahkan style untuk hasil
            $('<style>')
                .text(`
                    .result-section {
                        padding: 1rem;
                    }
                    .skin-type-result {
                        background: linear-gradient(135deg, #3498db, #2980b9);
                        color: white;
                        padding: 1.5rem;
                        border-radius: 15px;
                        margin-bottom: 2rem;
                    }
                    .skin-type-result h4 {
                        margin: 0;
                        font-size: 1.3rem;
                    }
                    .recommendation-card {
                        background: white;
                        border-radius: 15px;
                        padding: 1.5rem;
                        margin-top: 1rem;
                    }
                    .recommendation-item {
                        display: flex;
                        align-items: flex-start;
                        gap: 1rem;
                        padding: 1rem;
                        border-bottom: 1px solid #eee;
                    }
                    .recommendation-item:last-child {
                        border-bottom: none;
                    }
                    .recommendation-item i {
                        font-size: 1.5rem;
                        color: #3498db;
                        background: rgba(52, 152, 219, 0.1);
                        padding: 1rem;
                        border-radius: 10px;
                    }
                    .products-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                        gap: 1.5rem;
                        margin-top: 1rem;
                    }
                    .product-card {
                        background: white;
                        border-radius: 15px;
                        padding: 1.5rem;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    }
                    .product-header {
                        margin-bottom: 1rem;
                    }
                    .product-header h5 {
                        margin: 0;
                        color: #2c3e50;
                    }
                    .brand {
                        color: #666;
                        font-size: 0.9rem;
                    }
                    .price {
                        font-size: 1.2rem;
                        color: #2ecc71;
                        font-weight: bold;
                        margin-bottom: 1rem;
                    }
                    .description {
                        color: #666;
                        margin-bottom: 1rem;
                    }
                    .ingredients, .usage {
                        margin-top: 1rem;
                    }
                    .ingredients h6, .usage h6 {
                        color: #2c3e50;
                        margin-bottom: 0.5rem;
                    }
                `)
                .appendTo('head');
        }
    });
    </script>
</body>
</html> 