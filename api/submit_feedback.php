<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => ''];
    
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Silakan login terlebih dahulu';
        echo json_encode($response);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    if (!$rating || $rating < 1 || $rating > 5) {
        $response['message'] = 'Rating tidak valid';
        echo json_encode($response);
        exit;
    }

    $check_query = "SELECT id FROM feedback WHERE user_id = ? AND DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = 'Anda sudah memberikan feedback hari ini';
        echo json_encode($response);
        exit;
    }

    $insert_query = "INSERT INTO feedback (user_id, rating, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iis", $user_id, $rating, $message);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Terima kasih atas feedback Anda!';
    } else {
        $response['message'] = 'Gagal menyimpan feedback';
    }

    echo json_encode($response);
    exit;
}

$response = ['status' => 'error', 'message' => 'Metode request tidak valid'];
echo json_encode($response);
?> 