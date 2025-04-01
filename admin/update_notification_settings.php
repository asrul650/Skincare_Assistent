<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['update_notification'])) {
    $smtp_email = $_POST['smtp_email'];
    $smtp_password = $_POST['smtp_password'];
    $notification_time = $_POST['notification_time'];
    
    // Simpan ke file konfigurasi
    $config_content = "<?php
define('SMTP_EMAIL', '{$smtp_email}');
define('SMTP_PASSWORD', '{$smtp_password}');
define('NOTIFICATION_TIME', {$notification_time});
";
    
    file_put_contents('../config/email_config.php', $config_content);
    
    $_SESSION['success_message'] = "Pengaturan email berhasil diperbarui!";
    header("Location: settings.php");
    exit;
} 