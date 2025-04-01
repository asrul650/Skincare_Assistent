<?php
// Konfigurasi email
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your_email@gmail.com', // Ganti dengan email Gmail Anda
    'smtp_password' => 'your_app_password', // Ganti dengan App Password Gmail
    'smtp_encryption' => 'tls',
    'from_email' => 'your_email@gmail.com', // Ganti dengan email Gmail Anda
    'from_name' => 'Skincare Assistant'
];

// Konfigurasi email default
define('SMTP_EMAIL', ''); // Email Gmail Anda
define('SMTP_PASSWORD', ''); // App Password dari Gmail
define('NOTIFICATION_TIME', 30); // Waktu default notifikasi (menit) 