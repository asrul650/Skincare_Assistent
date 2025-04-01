<?php
require_once '../config/db.php';
require_once '../database/connection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($product = $result->fetch_assoc()) {
        // Jika gambar tidak ada, gunakan gambar default berdasarkan kategori
        if (empty($product['image'])) {
            $product['image'] = getDefaultProductImage($product['category']);
        }
        
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}

function getDefaultProductImage($category) {
    $default_images = [
        'cleanser' => 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8',
        'toner' => 'https://images.unsplash.com/photo-1573575155376-b5010099301b',
        'serum' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be',
        'moisturizer' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883',
        'sunscreen' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03'
    ];
    
    $category = strtolower($category);
    
    if (isset($default_images[$category])) {
        return $default_images[$category] . '?auto=format&fit=crop&w=400&q=80';
    }
    
    return 'https://images.unsplash.com/photo-1556228841-5e100c6df2e6?auto=format&fit=crop&w=400&q=80';
}
?> 