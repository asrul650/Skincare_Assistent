<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <nav class="nav-container">
        <div class="logo">
            <i class="fas fa-spa"></i> Skincare Assistant
        </div>
        <ul class="nav-links">
            <li><a href="home.html" <?php echo ($current_page == 'home.html') ? 'class="active"' : ''; ?>>Beranda</a></li>
            <li><a href="products.html" <?php echo ($current_page == 'products.html') ? 'class="active"' : ''; ?>>Produk</a></li>
            <li><a href="schedule.html" <?php echo ($current_page == 'schedule.html') ? 'class="active"' : ''; ?>>Jadwal</a></li>
            <li><a href="about.html" <?php echo ($current_page == 'about.html') ? 'class="active"' : ''; ?>>Tentang</a></li>
        </ul>
    </nav>
</header> 