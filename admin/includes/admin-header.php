<nav class="admin-nav">
    <div class="admin-nav-container">
        <div class="admin-logo">
            <i class="fas fa-spa"></i> Admin Panel
        </div>
        <ul class="admin-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products/manage.php"><i class="fas fa-box"></i> Produk</a></li>
            <li><a href="articles/manage.php"><i class="fas fa-newspaper"></i> Artikel</a></li>
            <li><a href="reviews/manage.php"><i class="fas fa-star"></i> Review</a></li>
            <li><a href="schedules/manage.php"><i class="fas fa-calendar"></i> Jadwal</a></li>
        </ul>
        <div class="admin-profile">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</nav> 