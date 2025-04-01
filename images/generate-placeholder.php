<?php
function generatePlaceholder($width, $height, $text, $type = 'product') {
    $im = imagecreatetruecolor($width, $height);
    
    // Set warna background
    $bgColor = imagecolorallocate($im, 240, 240, 240);
    $textColor = imagecolorallocate($im, 100, 100, 100);
    
    // Isi background
    imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);
    
    // Tambah text
    $font = 5; // Built-in font
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    
    // Posisi text di tengah
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    // Tulis text
    imagestring($im, $font, $x, $y, $text, $textColor);
    
    // Set header
    header('Content-Type: image/jpeg');
    
    // Output image
    imagejpeg($im);
    
    // Bersihkan memory
    imagedestroy($im);
}

// Generate default product image
if (isset($_GET['type']) && $_GET['type'] === 'product') {
    generatePlaceholder(300, 300, 'Product Image');
} else {
    generatePlaceholder(400, 250, 'Article Image', 'article');
}
?> 