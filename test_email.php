<?php
require 'vendor/autoload.php';
require_once 'config/db.php';
require_once 'includes/email_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendTestEmail($to_email) {
    $config = require 'config/email_config.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_encryption'];
        $mail->Port = $config['smtp_port'];

        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Test Email dari Skincare Assistant';
        $mail->Body = '
            <h2>Test Email</h2>
            <p>Ini adalah email test dari Skincare Assistant.</p>
            <p>Jika Anda menerima email ini, berarti konfigurasi email berhasil!</p>
        ';

        $mail->send();
        echo "Email berhasil dikirim ke $to_email";
    } catch (Exception $e) {
        echo "Email gagal terkirim. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Panggil fungsi test
sendTestEmail('email-tujuan@example.com'); // Ganti dengan email tujuan Anda 

// Test pengiriman email
$result = sendScheduleReminder(
    'email_tujuan@gmail.com', // Ganti dengan email Anda untuk testing
    'Nama User Test',
    'Rutinitas Pagi',
    date('Y-m-d H:i:s', strtotime('+30 minutes'))
);

if ($result) {
    echo "Email berhasil dikirim!";
} else {
    echo "Gagal mengirim email.";
} 