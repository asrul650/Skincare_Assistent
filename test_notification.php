<?php
require_once 'includes/email_functions.php';

// Test kirim email
$result = sendScheduleReminder(
    'cursorai098@gmail.com', // Email tujuan
    'Admin', // Nama user
    'Rutinitas Skincare Pagi', // Nama rutinitas
    date('Y-m-d H:i:s', strtotime('+5 minutes')) // Waktu jadwal
);

if ($result) {
    echo "<h2>Email test berhasil dikirim!</h2>";
    echo "<p>Silakan cek inbox email Anda.</p>";
} else {
    echo "<h2>Gagal mengirim email test.</h2>";
    echo "<p>Silakan cek error log untuk detailnya.</p>";
} 