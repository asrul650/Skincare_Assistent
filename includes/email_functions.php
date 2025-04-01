<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function sendScheduleReminder($to_email, $user_name, $routine_name, $schedule_time) {
    $config = require_once __DIR__ . '/../config/email_config.php';
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['smtp_port'];
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to_email, $user_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Pengingat Rutinitas Skincare';
        $mail->Body = "
            <h2>Hai {$user_name}!</h2>
            <p>Ini pengingat untuk rutinitas skincare Anda yang akan datang:</p>
            <p><strong>{$routine_name}</strong> dijadwalkan pada {$schedule_time}</p>
            <p>Jangan lupa untuk melakukan rutinitas skincare Anda!</p>
            <br>
            <p>Salam,<br>Tim Skincare Assistant</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return false;
    }
} 