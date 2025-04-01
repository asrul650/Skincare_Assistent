<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM articles ORDER BY created_at DESC LIMIT 6";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($articles);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 