<?php
session_start();
require_once '../config/db.php';

// Cek apakah sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Delete Article
if (isset($_POST['delete_article'])) {
    $article_id = $_POST['article_id'];
    $query = "DELETE FROM articles WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $article_id);
    $stmt->execute();
}

// Handle Add/Edit Article
if (isset($_POST['save_article'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['admin_id'];
    
    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/articles/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_url = time() . '_' . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_url;
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
    }

    if (isset($_POST['article_id'])) { 
        // Update existing article
        $article_id = $_POST['article_id'];
        $query = "UPDATE articles SET title=?, content=? WHERE id=?";
        if ($image_url) {
            $query = "UPDATE articles SET title=?, content=?, image_url=? WHERE id=?";
        }
        $stmt = $conn->prepare($query);
        if ($image_url) {
            $stmt->bind_param("sssi", $title, $content, $image_url, $article_id);
        } else {
            $stmt->bind_param("ssi", $title, $content, $article_id);
        }
    } else {
        // Add new article
        $query = "INSERT INTO articles (title, content, image_url, author_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $title, $content, $image_url, $author_id);
    }
    $stmt->execute();
    
    // Redirect to refresh the page
    header("Location: articles.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel | Admin Skincare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f6f9fc;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 1rem;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1rem;
        }

        .admin-logo i {
            font-size: 1.5rem;
        }

        .admin-menu {
            list-style: none;
        }

        .menu-item {
            margin-bottom: 0.5rem;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu-item a:hover,
        .menu-item a.active {
            background: #3498db;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 2rem;
            background: linear-gradient(135deg, #f6f9fc 0%, #e9f2f9 100%);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .admin-title {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        /* Articles Section */
        .articles-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .add-btn {
            padding: 0.8rem 1.5rem;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .article-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .article-content {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit, .btn-delete {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
        }

        .btn-edit:hover {
            background: #d68910;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 10px;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-modal {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: #333;
        }

        .article-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: grid;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: #2c3e50;
        }

        .form-group input,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        /* Image preview style */
        .image-preview {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        #imagePreview {
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="admin-logo">
                <i class="fas fa-spa"></i>
                <span>Admin Panel</span>
            </div>
            <ul class="admin-menu">
                <li class="menu-item">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="products.php">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="articles.php" class="active">
                        <i class="fas fa-newspaper"></i>
                        <span>Artikel</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="schedules.php">
                        <i class="fas fa-calendar"></i>
                        <span>Jadwal</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="feedback.php">
                        <i class="fas fa-comments"></i>
                        <span>Feedback</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="admin-header">
                <h1 class="admin-title">Kelola Artikel</h1>
                <button class="add-btn" onclick="openModal()">
                    <i class="fas fa-plus"></i>
                    Tambah Artikel
                </button>
            </div>

            <div class="articles-section">
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Konten</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM articles ORDER BY created_at DESC";
                            $result = $conn->query($query);
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>";
                                if (isset($row['image_url']) && !empty($row['image_url'])) {
                                    echo "<img src='../uploads/articles/{$row['image_url']}' alt='{$row['title']}' class='article-image'>";
                                } else {
                                    echo "<img src='../assets/default-article.jpg' alt='Default' class='article-image'>";
                                }
                                echo "</td>";
                                echo "<td>{$row['title']}</td>";
                                echo "<td class='article-content'>{$row['content']}</td>";
                                echo "<td>" . date('d M Y', strtotime($row['created_at'])) . "</td>";
                                echo "<td class='action-buttons'>
                                        <button class='btn-edit' onclick='editArticle({$row['id']})'>
                                            <i class='fas fa-edit'></i> Edit
                                        </button>
                                        <button class='btn-delete' onclick='deleteArticle({$row['id']})'>
                                            <i class='fas fa-trash'></i> Hapus
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Article Modal -->
    <div id="articleModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah Artikel Baru</h2>
            <form class="article-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="article_id" name="article_id">
                <div class="form-group">
                    <label for="title">Judul Artikel</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="content">Konten Artikel</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Artikel</label>
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <div id="imagePreview"></div>
                </div>
                <button type="submit" name="save_article" class="add-btn">
                    <i class="fas fa-save"></i>
                    Simpan Artikel
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(articleId = null) {
            document.getElementById('articleModal').style.display = 'block';
            if (articleId) {
                // Load article data for editing
                fetch(`get_article.php?id=${articleId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('modalTitle').textContent = 'Edit Artikel';
                        document.getElementById('article_id').value = data.id;
                        document.getElementById('title').value = data.title;
                        document.getElementById('content').value = data.content;
                    });
            } else {
                document.getElementById('modalTitle').textContent = 'Tambah Artikel Baru';
                document.getElementById('article_id').value = '';
                document.querySelector('.article-form').reset();
            }
        }

        function closeModal() {
            document.getElementById('articleModal').style.display = 'none';
        }

        function editArticle(id) {
            openModal(id);
        }

        function deleteArticle(id) {
            if (confirm('Apakah Anda yakin ingin menghapus artikel ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_article" value="1">
                    <input type="hidden" name="article_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('articleModal')) {
                closeModal();
            }
        }

        // Tambahkan fungsi preview image
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html> 