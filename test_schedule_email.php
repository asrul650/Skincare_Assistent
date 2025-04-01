<?php
require_once 'includes/email_functions.php';

// Test kirim email
$result = sendScheduleReminder(
    'cursorai098@gmail.com', // Email tujuan (ganti sesuai email yang ingin ditest)
    'Admin', // Nama user
    'Rutinitas Pagi', // Nama rutinitas
    date('Y-m-d H:i:s', strtotime('+5 minutes')) // Waktu jadwal
);

if ($result) {
    echo "Email test berhasil dikirim!";
} else {
    echo "Gagal mengirim email test.";
} 