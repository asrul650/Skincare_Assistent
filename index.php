<?php
require_once 'config/auth_check.php';
require_once 'config/db.php';

// Jika user sudah login, redirect ke home.php
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skincare Assistant | Your Personal Beauty Guide</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Style yang sudah ada */
        
        /* Style baru untuk auth buttons */
        .auth-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-left: 2rem;
        }

        .login-btn {
            padding: 0.7rem 1.5rem;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
            background: linear-gradient(135deg, #2980b9, #3498db);
        }

        .register-btn {
            padding: 0.7rem 1.5rem;
            background: transparent;
            color: #3498db;
            border: 2px solid #3498db;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .register-btn:hover {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2);
        }

        .auth-buttons i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .login-btn:hover i,
        .register-btn:hover i {
            transform: translateX(3px);
        }

        /* Perbaikan responsive design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                padding: 1rem;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                text-align: center;
                margin: 1rem 0;
            }

            .auth-buttons {
                flex-direction: row;
                width: 100%;
                margin: 0;
                justify-content: center;
            }

            .login-btn,
            .register-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        /* Style yang sudah ada lainnya */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 2rem;
        }

        .article-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .article-card:hover {
            transform: translateY(-5px);
        }

        .article-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .article-content {
            padding: 1.5rem;
        }

        .article-content h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .article-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .article-meta {
            display: flex;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .read-more {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .read-more:hover {
            background: #2980b9;
        }

        .no-articles {
            text-align: center;
            grid-column: 1 / -1;
            padding: 2rem;
            color: #666;
        }

        /* Responsive Styles */
        @media screen and (max-width: 1024px) {
            .nav-container {
                padding: 1rem;
            }

            main {
                padding: 1rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 768px) {
            /* Navbar */
            .nav-container {
                flex-direction: column;
                padding: 0.5rem;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                gap: 0.5rem;
                display: none; /* Akan ditampilkan saat menu mobile aktif */
            }

            .nav-links.active {
                display: flex;
            }

            .auth-buttons {
                flex-direction: column;
                width: 100%;
                gap: 0.5rem;
                margin-top: 1rem;
            }

            /* Tambah tombol hamburger menu */
            .menu-toggle {
                display: block;
                position: absolute;
                top: 1rem;
                right: 1rem;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
            }

            /* Hero Section */
            .hero-section {
                padding: 2rem 1rem;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
            }

            /* Sections */
            section {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            /* Features Grid */
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            /* Articles Grid */
            .articles-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .article-card {
                margin-bottom: 1rem;
            }

            /* Skin Analysis */
            .skin-type-options {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .option-card {
                padding: 1rem;
            }

            /* Footer */
            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }

            .social-links {
                justify-content: center;
            }
        }

        @media screen and (max-width: 480px) {
            /* Additional mobile adjustments */
            .hero-section h1 {
                font-size: 1.75rem;
            }

            button {
                width: 100%;
            }

            .input-group {
                flex-direction: column;
            }

            .input-group input,
            .input-group button {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 2rem;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            z-index: 100;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .menu-toggle {
            display: none;
        }

        /* Responsive styles untuk header */
        @media screen and (max-width: 768px) {
            .nav-container {
                padding: 0.5rem 1rem;
            }

            .menu-toggle {
                display: block;
                position: absolute;
                right: 1rem;
                background: transparent;
                border: none;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
                z-index: 100;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                flex-direction: column;
                background: var(--primary);
                padding: 1rem;
                gap: 1rem;
                display: none;
                text-align: center;
            }

            .nav-links.active {
                display: flex;
            }

            .auth-buttons {
                position: absolute;
                top: calc(100% + 200px); /* Sesuaikan dengan jumlah menu */
                left: 0;
                right: 0;
                flex-direction: column;
                background: var(--primary);
                padding: 1rem;
                gap: 0.5rem;
                display: none;
                text-align: center;
            }

            .auth-buttons.active {
                display: flex;
            }

            .login-btn, 
            .register-btn {
                width: 90%;
                margin: 0 auto;
                justify-content: center;
            }

            .hero-section {
                padding: 2rem 1rem;
                margin-top: 1rem;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 1rem;
                padding: 0 1rem;
            }
        }

        /* Animasi untuk menu mobile */
        .nav-links, 
        .auth-buttons {
            transition: all 0.3s ease-in-out;
        }

        .nav-links.active, 
        .auth-buttons.active {
            animation: slideDown 0.3s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="nav-container">
            <div class="logo">
                <i class="fas fa-spa"></i> Skincare Assistant
            </div>
            
            <!-- Pindahkan menu-toggle ke sini -->
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>

            <ul class="nav-links">
                <li><a href="#home">Beranda</a></li>
                <li><a href="#product-info">Produk</a></li>
                <li><a href="#scheduler">Jadwal</a></li>
                <li><a href="#about">Tentang</a></li>
            </ul>

            <div class="auth-buttons">
                <a href="pages/login_user.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk
                </a>
                <a href="pages/register.php" class="register-btn">
                    <i class="fas fa-user-plus"></i>
                    Daftar
                </a>
            </div>
        </nav>
        <div class="hero-section">
            <h1>Skincare Assistant</h1>
            <p>Panduan kecantikan personal untuk kulit sehat dan bercahaya</p>
        </div>
    </header>

    <main>        
        <section id="product-info">
            <h2><i class="fas fa-search"></i> Informasi Produk</h2>
            <div class="input-group">
                <input type="text" id="product-name" placeholder="Masukkan nama produk skincare...">
                <button onclick="getProductInfo()">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
            <div id="product-details"></div>
        </section>
        
        <section id="scheduler">
            <h2><i class="fas fa-calendar-alt"></i> Penjadwalan Perawatan</h2>
            <div class="schedule-form">
                <input type="text" id="schedule-name" placeholder="Nama rutinitas skincare">
                <input type="datetime-local" id="schedule-time">
                <button onclick="addSchedule()">
                    <i class="fas fa-plus"></i> Tambah Jadwal
                </button>
            </div>
            <div id="schedule-list"></div>
        </section>

        <section id="skin-analysis" class="skin-analysis">
            <h2><i class="fas fa-microscope"></i> Analisis Kulit</h2>
            <div class="skin-quiz">
                <div class="quiz-progress">
                    <div class="progress-bar">
                        <div class="progress-steps">
                            <div class="step active" data-step="1">1</div>
                            <div class="step" data-step="2">2</div>
                            <div class="step" data-step="3">3</div>
                        </div>
                        <div class="progress-line">
                            <div class="progress" style="width: 0%"></div>
                        </div>
                    </div>
                    <span class="step-info">Langkah 1 dari 3</span>
                </div>

                <form id="skinQuizForm">
                    <!-- Step 1: Jenis Kulit -->
                    <div class="quiz-step active" data-step="1">
                        <h3>Jenis Kulit Anda</h3>
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
                    </div>

                    <!-- Step 2: Masalah Kulit -->
                    <div class="quiz-step" data-step="2">
                        <h3>Masalah Kulit</h3>
                        <div class="skin-concerns-grid">
                            <label class="concern-card">
                                <input type="checkbox" name="concerns[]" value="acne">
                                <div class="card-content">
                                    <i class="fas fa-check"></i>
                                    <img src="images/concerns/acne.jpg" alt="Acne">
                                    <h4>Jerawat</h4>
                                </div>
                            </label>
                            <label class="concern-card">
                                <input type="checkbox" name="concerns[]" value="darkspots">
                                <div class="card-content">
                                    <i class="fas fa-check"></i>
                                    <img src="images/concerns/darkspots.jpg" alt="Dark Spots">
                                    <h4>Flek Hitam</h4>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Step 3: Rutinitas -->
                    <div class="quiz-step" data-step="3">
                        <h3>Rutinitas Saat Ini</h3>
                        <div class="routine-questions">
                            <div class="form-group">
                                <label>Seberapa sering Anda melakukan perawatan kulit?</label>
                                <select name="routine_frequency" required>
                                    <option value="">Pilih frekuensi</option>
                                    <option value="never">Tidak pernah</option>
                                    <option value="sometimes">Kadang-kadang</option>
                                    <option value="daily">Setiap hari</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Produk apa yang sudah Anda gunakan?</label>
                                <div class="checkbox-group">
                                    <label><input type="checkbox" name="current_products[]" value="cleanser"> Cleanser</label>
                                    <label><input type="checkbox" name="current_products[]" value="toner"> Toner</label>
                                    <label><input type="checkbox" name="current_products[]" value="moisturizer"> Moisturizer</label>
                                    <label><input type="checkbox" name="current_products[]" value="sunscreen"> Sunscreen</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="quiz-navigation">
                        <button type="button" class="btn btn-prev" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Sebelumnya
                        </button>
                        <button type="button" class="btn btn-next">
                            Selanjutnya <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="submit" class="btn btn-submit" style="display: none;">
                            <i class="fas fa-check-circle"></i> Analisis Kulit Saya
                        </button>
                    </div>
                </form>

                <div id="analysisResult" class="analysis-result"></div>
            </div>
        </section>

        <section id="trending" class="trending-products">
            <h2><i class="fas fa-fire"></i> Produk Trending</h2>
            <div class="product-carousel">
                <!-- Produk akan di-generate dari JavaScript -->
            </div>
        </section>

        <section id="articles" class="skincare-articles">
            <h2><i class="fas fa-book-open"></i> Tips & Artikel</h2>
            <div class="articles-grid">
                <?php
                $query = "SELECT a.*, admin.username as author_name 
                         FROM articles a 
                         LEFT JOIN admin ON a.author_id = admin.id 
                         ORDER BY a.created_at DESC 
                         LIMIT 4";
                $result = $conn->query($query);
                
                if ($result && $result->num_rows > 0) {
                    while ($article = $result->fetch_assoc()) {
                        ?>
                        <article class="article-card">
                            <div class="article-image">
                                <?php if (!empty($article['image_url'])): ?>
                                    <img src="uploads/articles/<?php echo htmlspecialchars($article['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>">
                                <?php else: ?>
                                    <img src="assets/default-article.jpg" alt="Default Article Image">
                                <?php endif; ?>
                            </div>
                            <div class="article-content">
                                <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                                <p><?php echo substr(strip_tags($article['content']), 0, 150) . '...'; ?></p>
                                <div class="article-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author_name']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
                                </div>
                                <a href="pages/article.php?id=<?php echo $article['id']; ?>" class="read-more">Baca Selengkapnya</a>
                            </div>
                        </article>
                        <?php
                    }
                } else {
                    echo "<p class='no-articles'>Belum ada artikel yang ditambahkan.</p>";
                }
                ?>
            </div>
        </section>

        <section id="about" class="features">
            <h2><i class="fas fa-star"></i> Fitur Unggulan</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-search-plus"></i>
                    <h3>Pencarian Produk</h3>
                    <p>Temukan informasi lengkap tentang produk skincare favorit Anda</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pengingat Rutin</h3>
                    <p>Atur jadwal perawatan kulit Anda dengan pengingat otomatis</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-heart"></i>
                    <h3>Rekomendasi Personal</h3>
                    <p>Dapatkan saran produk yang sesuai dengan jenis kulit Anda</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>Tentang Kami</h3>
                <p>Skincare Assistant adalah platform yang membantu Anda menemukan dan mengatur rutinitas perawatan kulit yang tepat.</p>
            </div>
            <div class="footer-section">
                <h3>Tautan Cepat</h3>
                <ul>
                    <li><a href="pages/login_user.php">Beranda</a></li>
                    <li><a href="pages/login_user.php">Cari Produk</a></li>
                    <li><a href="pages/login_user.php">Atur Jadwal</a></li>
                    <li><a href="#about">Tentang Kami</a></li>
                    <li><a href="admin/login.php" class="admin-link">
                        <i class="fas fa-lock"></i> Admin Panel
                    </a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Ikuti Kami</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Hubungi Kami</h3>
                <p><i class="fas fa-envelope"></i> skincareassistant@gmail.com</p>
                <p><i class="fas fa-phone"></i> +62 123 4567 890</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Skincare Assistant. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <script src="scripts.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi untuk menampilkan pesan login
        function showLoginMessage() {
            Swal.fire({
                title: 'Login Diperlukan',
                text: 'Silakan login terlebih dahulu untuk melakukan analisis kulit',
                icon: 'info',
                confirmButtonText: 'Login Sekarang',
                showCancelButton: true,
                cancelButtonText: 'Nanti Saja'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'pages/login_user.php';
                }
            });
        }

        // Event listener untuk tombol analisis dan kartu opsi
        document.querySelector('#skinQuizForm').addEventListener('submit', function(e) {
            e.preventDefault();
            showLoginMessage();
        });

        document.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('click', function() {
                showLoginMessage();
            });
        });

        document.querySelectorAll('.concern-card').forEach(card => {
            card.addEventListener('click', function() {
                showLoginMessage();
            });
        });

        // Menu toggle functionality
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.querySelector('.nav-links');
        const authButtons = document.querySelector('.auth-buttons');
        
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');
            authButtons.classList.toggle('active');
            
            // Toggle icon
            const icon = menuToggle.querySelector('i');
            if (navLinks.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.nav-container')) {
                navLinks.classList.remove('active');
                authButtons.classList.remove('active');
                menuToggle.querySelector('i').classList.remove('fa-times');
                menuToggle.querySelector('i').classList.add('fa-bars');
            }
        });

        // Close menu when clicking nav links
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                authButtons.classList.remove('active');
                menuToggle.querySelector('i').classList.remove('fa-times');
                menuToggle.querySelector('i').classList.add('fa-bars');
            });
        });
    });
    </script>
</body>
</html> 