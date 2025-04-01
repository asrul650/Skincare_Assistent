<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['register'])) {
        // Proses Registrasi
        $username = trim($_POST['reg_username']);
        $email = trim($_POST['reg_email']);
        $password = $_POST['reg_password'];
        $confirm_password = $_POST['reg_confirm_password'];

        // Validasi input
        $errors = [];
        
        // Cek username
        if (empty($username)) {
            $errors[] = "Username harus diisi";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = "Username hanya boleh berisi huruf, angka, dan underscore";
        }

        // Cek email
        if (empty($email)) {
            $errors[] = "Email harus diisi";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid";
        }

        // Cek password
        if (empty($password)) {
            $errors[] = "Password harus diisi";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter";
        }

        // Cek konfirmasi password
        if ($password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak cocok";
        }

        if (empty($errors)) {
            // Cek apakah username atau email sudah ada
            $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Username atau email sudah terdaftar";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user baru
                $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Registrasi berhasil! Silakan login.";
                    header("Location: login_user.php");
                    exit;
                } else {
                    $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
                }
            }
        }
    } elseif (isset($_POST['login'])) {
        // Proses Login
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Validasi input
        $errors = [];
        
        if (empty($username)) {
            $errors[] = "Username harus diisi";
        }
        if (empty($password)) {
            $errors[] = "Password harus diisi";
        }

        if (empty($errors)) {
            // Cek user di database
            $query = "SELECT * FROM users WHERE username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Update status menjadi active
                    $update_status = "UPDATE users SET status = 'active', last_activity = CURRENT_TIMESTAMP WHERE id = ?";
                    $stmt = $conn->prepare($update_status);
                    $stmt->bind_param("i", $user['id']);
                    $stmt->execute();
                    
                    header("Location: home.php");
                    exit;
                }
            }
            $errors[] = "Username atau password salah";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register | Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #87CEEB;
            --secondary-color: #5F9EA0;
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
            background: url('../images/login-bg.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(135, 206, 235, 0.3);
            backdrop-filter: blur(5px);
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .logo {
            text-align: center;
            padding: 2rem;
            background: rgba(135, 206, 235, 0.1);
        }

        .logo i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .logo h1 {
            color: var(--text-color);
            font-size: 1.8rem;
            margin-top: 0.5rem;
        }

        .tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 2px solid rgba(135, 206, 235, 0.2);
        }

        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: var(--primary-color);
            color: white;
        }

        .form-container {
            padding: 2rem;
            background: rgba(255, 255, 255, 0.8);
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
            border: 2px solid rgba(135, 206, 235, 0.3);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(135, 206, 235, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .submit-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .success-message {
            background: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        #registerForm {
            display: none;
        }

        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .container {
                border-radius: 10px;
            }

            .form-container {
                padding: 1.5rem;
            }
        }

        .back-to-dashboard {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 0.8rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .back-to-dashboard:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .back-to-dashboard {
                top: 10px;
                left: 10px;
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="home.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Dashboard
        </a>
    <?php else: ?>
        <a href="../index.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i>
            Kembali ke Beranda
        </a>
    <?php endif; ?>

    <div class="container">
        <div class="logo">
            <i class="fas fa-spa"></i>
            <h1>Skincare Assistant</h1>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success-message">
                <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showForm('login')">Login</div>
            <div class="tab" onclick="showForm('register')">Register</div>
        </div>

        <div class="form-container">
            <!-- Login Form -->
            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login" class="submit-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <!-- Register Form -->
            <form id="registerForm" method="POST" action="">
                <div class="form-group">
                    <label for="reg_username">Username</label>
                    <input type="text" id="reg_username" name="reg_username" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input type="email" id="reg_email" name="reg_email" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">Password</label>
                    <input type="password" id="reg_password" name="reg_password" required>
                </div>
                <div class="form-group">
                    <label for="reg_confirm_password">Konfirmasi Password</label>
                    <input type="password" id="reg_confirm_password" name="reg_confirm_password" required>
                </div>
                <button type="submit" name="register" class="submit-btn">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
        </div>
    </div>

    <script>
        function showForm(formType) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabs = document.querySelectorAll('.tab');

            if (formType === 'login') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                tabs[0].classList.add('active');
                tabs[1].classList.remove('active');
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                tabs[0].classList.remove('active');
                tabs[1].classList.add('active');
            }
        }
    </script>
</body>
</html>