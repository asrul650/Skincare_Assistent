<?php
session_start();
require_once '../config/db.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit;
}

// Cek CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

// Ambil data dari form
$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$birthdate = $_POST['birthdate'] ?? null;

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Format email tidak valid";
    header("Location: profile.php");
    exit;
}

try {
    // Update data user
    $query = "UPDATE users SET full_name = ?, email = ?, phone = ?, birthdate = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $full_name, $email, $phone, $birthdate, $user_id);
    $stmt->execute();

    // Handle upload foto profil jika ada
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Validasi tipe file
        if (in_array($filetype, $allowed)) {
            // Buat direktori uploads jika belum ada
            $upload_dir = "../uploads/avatars/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate nama file unik
            $new_filename = "avatar_" . $user_id . "_" . time() . "." . $filetype;
            $upload_path = $upload_dir . $new_filename;

            // Pindahkan file
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                $query = "SELECT avatar FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_avatar = $result->fetch_assoc()['avatar'];

                if ($old_avatar && file_exists("../" . $old_avatar)) {
                    unlink("../" . $old_avatar);
                }

                // Update path avatar di database
                $avatar_path = "uploads/avatars/" . $new_filename;
                $query = "UPDATE users SET avatar = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("si", $avatar_path, $user_id);
                $stmt->execute();

                $_SESSION['success'] = "Profil dan foto berhasil diperbarui";
            } else {
                $_SESSION['error'] = "Gagal mengupload foto";
                error_log("Failed to move uploaded file");
            }
        } else {
            $_SESSION['error'] = "Tipe file tidak diizinkan. Gunakan format: jpg, jpeg, png, atau gif";
        }
    } else {
        $_SESSION['success'] = "Profil berhasil diperbarui";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan saat memperbarui profil";
    error_log("Error updating profile: " . $e->getMessage());
}

// Redirect kembali ke halaman profil
header("Location: profile.php");
exit;
?> 