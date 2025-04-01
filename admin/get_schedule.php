<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Unauthorized');
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $admin_id = $_SESSION['admin_id'];
    
    $query = "SELECT * FROM schedules WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($schedule);
}
?> 