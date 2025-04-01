<?php
// Array gambar default dari Unsplash
$default_images = [
    'cleanser' => 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8',
    'toner' => 'https://images.unsplash.com/photo-1573575155376-b5010099301b',
    'serum' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be',
    'moisturizer' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883',
    'sunscreen' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03',
    'default' => 'https://images.unsplash.com/photo-1556228841-5e100c6df2e6'
];

// Update query di database
$query = "UPDATE products 
          SET image = CASE 
              WHEN category = 'cleanser' THEN '{$default_images['cleanser']}'
              WHEN category = 'toner' THEN '{$default_images['toner']}'
              WHEN category = 'serum' THEN '{$default_images['serum']}'
              WHEN category = 'moisturizer' THEN '{$default_images['moisturizer']}'
              WHEN category = 'sunscreen' THEN '{$default_images['sunscreen']}'
              ELSE '{$default_images['default']}'
          END";

require_once 'config/db.php';
if ($conn->query($query)) {
    echo "Gambar default berhasil diupdate";
} else {
    echo "Error updating images: " . $conn->error;
}
?> 