<?php
session_start();
require_once '../config/db.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit;
}

// Validasi CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $doctor_name = $_POST['doctor_name'];
    $consultation_date = $_POST['consultation_date'] . ' ' . $_POST['consultation_time']; // Gabungkan tanggal dan waktu
    $consultation_type = $_POST['consultation_type'];
    $symptoms = $_POST['symptoms'];

    try {
        // Persiapkan query
        $query = "INSERT INTO consultations (user_id, doctor_name, consultation_date, consultation_type, symptoms, status) 
                 VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $user_id, $doctor_name, $consultation_date, $consultation_type, $symptoms);
        
        // Eksekusi query
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Konsultasi berhasil dijadwalkan!";
            header("Location: consultation.php");
            exit;
        } else {
            throw new Exception("Gagal menyimpan jadwal konsultasi");
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: consultation.php");
        exit;
    }
} 