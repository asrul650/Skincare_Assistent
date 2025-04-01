<?php
session_start();
require_once '../config/db.php';

// Cek jika sudah login
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Default admin credentials
    $default_username = 'admin';
    $default_password = 'admin123';

    if ($username === $default_username && $password === $default_password) {
        // Cek apakah admin sudah ada di tabel users
        $check_admin = "SELECT id FROM users WHERE username = 'admin'";
        $result = $conn->query($check_admin);
        
        if ($result->num_rows == 0) {
            // Tambahkan admin ke tabel users jika belum ada
            $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
            $insert_admin = "INSERT INTO users (username, email, password, status) 
                            VALUES ('admin', 'admin@example.com', ?, 'active')";
            $stmt = $conn->prepare($insert_admin);
            $stmt->bind_param("s", $hashed_password);
            $stmt->execute();
            
            $_SESSION['admin_id'] = $conn->insert_id;
        } else {
            $admin_user = $result->fetch_assoc();
            $_SESSION['admin_id'] = $admin_user['id'];
        }
        
        $_SESSION['admin_username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f6f9fc 0%, #e9f2f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .back-to-home {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 0.8rem 1.5rem;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .back-to-home:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header i {
            font-size: 3rem;
            color: #3498db;
            margin-bottom: 1rem;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .login-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group label {
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input {
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

        .login-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 1rem;
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

        .login-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .credentials-info {
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
        }

        .credentials-info p {
            margin: 0.2rem 0;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        Kembali ke Beranda
    </a>

    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-spa"></i>
            <h1>Admin Login</h1>
            <p>Skincare Assistant</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
    </div>
</body>
</html> 