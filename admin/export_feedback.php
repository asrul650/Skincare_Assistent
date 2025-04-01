<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Set header untuk download file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="feedback_report.xls"');

// Ambil data feedback
$query = "SELECT f.*, u.username, u.full_name, u.email 
          FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          ORDER BY f.created_at DESC";
$result = $conn->query($query);
$feedbacks = $result->fetch_all(MYSQLI_ASSOC);

// Buat header Excel
echo "Feedback Report\n\n";
echo "No\tNama\tEmail\tRating\tPesan\tTanggal\n";

// Isi data
$no = 1;
foreach ($feedbacks as $feedback) {
    echo $no . "\t";
    echo ($feedback['full_name'] ?: $feedback['username']) . "\t";
    echo $feedback['email'] . "\t";
    echo $feedback['rating'] . "\t";
    echo str_replace("\n", " ", $feedback['message']) . "\t";
    echo date('d/m/Y H:i', strtotime($feedback['created_at'])) . "\n";
    $no++;
}
?> 