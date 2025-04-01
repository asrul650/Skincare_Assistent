<?php
session_start();

// Cek jika user sudah login
if(isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

// Cek koneksi database
try {
    require_once '../config/db_login.php';
} catch (Exception $e) {
    $db_error = "Koneksi database gagal. Silakan coba lagi nanti.";
}

if (isset($_POST['register'])) {
    try {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validasi input
        $errors = [];
        
        // Cek username minimal 3 karakter
        if(strlen($username) < 3) {
            $errors[] = "Username minimal 3 karakter!";
        }

        // Cek email valid
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid!";
        }

        // Cek password minimal 6 karakter
        if(strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter!";
        }

        // Cek konfirmasi password
        if($password !== $confirm_password) {
            $errors[] = "Konfirmasi password tidak cocok!";
        }

        // Cek username sudah ada atau belum
        $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn_login->prepare($check_query);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Username atau email sudah terdaftar!";
        }

        // Jika tidak ada error, simpan ke database
        if(empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_query = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $conn_login->prepare($insert_query);
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if($insert_stmt->execute()) {
                $_SESSION['register_success'] = true;
                header("Location: login_user.php");
                exit;
            } else {
                $errors[] = "Gagal mendaftar. Silakan coba lagi.";
            }
        }
    } catch (Exception $e) {
        $errors[] = "Terjadi kesalahan. Silakan coba lagi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi | Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f6f9fc 0%, #e9f2f9 100%);
            padding: 2rem;
        }

        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #3498db;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            color: #2980b9;
            transform: translateX(-3px);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header i {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .register-header h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .register-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .register-btn {
            width: 100%;
            padding: 1rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .register-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee2e2;
            color: #ef4444;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Kembali ke Beranda
    </a>

    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h1>Daftar Akun</h1>
            <p>Buat akun baru untuk mengakses Skincare Assistant</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach($errors as $error): ?>
                    <p><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required 
                       placeholder="Masukkan username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Masukkan email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Masukkan password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Konfirmasi password">
            </div>
            <button type="submit" name="register" class="register-btn">
                <i class="fas fa-user-plus"></i>
                Daftar
            </button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login_user.php">Login disini</a>
        </div>
    </div>
</body>
</html> 