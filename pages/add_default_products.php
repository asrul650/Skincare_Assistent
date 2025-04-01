<?php
require_once '../config/db.php';

$default_products = [
    [
        'name' => "Gentle Foam Cleanser",
        'brand' => "Pure Beauty",
        'category' => "cleanser",
        'price' => 125000,
        'description' => "Pembersih wajah lembut dengan pH seimbang, cocok untuk semua jenis kulit",
        'image' => "https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types",
        'ingredients' => "Air, Glycerin, Sodium Laureth Sulfate, Cocamidopropyl Betaine",
        'how_to_use' => "Basahi wajah, aplikasikan cleanser, pijat lembut, bilas hingga bersih"
    ],
    [
        'name' => "Hydrating Toner",
        'brand' => "Skin Essentials",
        'category' => "toner",
        'price' => 180000,
        'description' => "Toner dengan kandungan hyaluronic acid untuk melembabkan kulit",
        'image' => "https://images.unsplash.com/photo-1573575155376-b5010099301b?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "Dry Skin",
        'ingredients' => "Air, Hyaluronic Acid, Glycerin, Niacinamide",
        'how_to_use' => "Aplikasikan pada wajah yang telah dibersihkan menggunakan kapas"
    ],
    [
        'name' => "Vitamin C Serum",
        'brand' => "Glow Lab",
        'category' => "serum",
        'price' => 350000,
        'description' => "Serum vitamin C 10% untuk mencerahkan dan meratakan warna kulit",
        'image' => "https://images.unsplash.com/photo-1620916566398-39f1143ab7be?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types",
        'ingredients' => "Air, Vitamin C (Ascorbic Acid), Ferulic Acid, Vitamin E",
        'how_to_use' => "Aplikasikan 2-3 tetes pada wajah di pagi hari"
    ],
    [
        'name' => "Moisture Cream",
        'brand' => "Hydra Plus",
        'category' => "moisturizer",
        'price' => 200000,
        'description' => "Krim pelembab ringan dengan ekstrak aloe vera",
        'image' => "https://images.unsplash.com/photo-1556228578-8c89e6adf883?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types",
        'ingredients' => "Air, Aloe Vera Extract, Glycerin, Ceramide",
        'how_to_use' => "Aplikasikan secukupnya pada wajah pagi dan malam"
    ],
    [
        'name' => "UV Shield SPF50+ PA++++",
        'brand' => "Sun Protection",
        'category' => "sunscreen",
        'price' => 275000,
        'description' => "Sunscreen dengan perlindungan tinggi, tidak lengket",
        'image' => "https://images.unsplash.com/photo-1556228720-195a672e8a03?ixlib=rb-1.2.1&auto=format&fit=crop&w=400",
        'skin_type' => "All Skin Types",
        'ingredients' => "Air, UV Filters, Niacinamide, Vitamin E",
        'how_to_use' => "Aplikasikan secukupnya sebagai langkah terakhir skincare pagi"
    ]
];

foreach ($default_products as $product) {
    $query = "INSERT INTO products (name, brand, category, price, description, image, skin_type, ingredients, how_to_use) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssdsssss", 
        $product['name'],
        $product['brand'],
        $product['category'],
        $product['price'],
        $product['description'],
        $product['image'],
        $product['skin_type'],
        $product['ingredients'],
        $product['how_to_use']
    );
    $stmt->execute();
}

echo "Produk default berhasil ditambahkan!";
?> 