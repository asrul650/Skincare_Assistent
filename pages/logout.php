<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['user_id'])) {
    // Update status menjadi inactive
    $query = "UPDATE users SET status = 'inactive' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    // Hapus semua session
    session_destroy();
}

header("Location: login_user.php");
exit;
?> 