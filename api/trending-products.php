<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT p.*, COUNT(pr.id) as review_count, AVG(pr.rating) as avg_rating
             FROM products p
             LEFT JOIN product_reviews pr ON p.id = pr.product_id
             GROUP BY p.id
             ORDER BY avg_rating DESC, review_count DESC
             LIMIT 5";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 