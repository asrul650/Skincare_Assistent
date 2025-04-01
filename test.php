<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Test query
    $query = "SELECT * FROM products LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Koneksi database berhasil dan data dapat diakses!";
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($product);
        echo "</pre>";
    } else {
        echo "Database terhubung tapi tidak ada data.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 