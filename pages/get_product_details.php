<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No product ID provided']);
}
?> 