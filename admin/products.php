<?php
session_start();
require_once '../config/db.php';

// Cek apakah sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Delete Product
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    
    // Get image name first
    $query = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    // Delete image file if exists
    if ($product['image']) {
        $image_path = "../uploads/products/" . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete product from database
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
}

// Handle Add/Edit Product
if (isset($_POST['save_product'])) {
    $name = $_POST['name'];
    $brand = $_POST['brand'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $skin_type = $_POST['skin_type'];
    
    // Create uploads directory if it doesn't exist
    $upload_dir = "../uploads/products/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Handle file upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $image = time() . '_' . $filename;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }
    }

    if (isset($_POST['product_id'])) {
        // Update existing product
        $product_id = $_POST['product_id'];
        
        if ($image) {
            // Delete old image if exists
            $query = "SELECT image FROM products WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $old_product = $result->fetch_assoc();
    
            if ($old_product['image']) {
                $old_image_path = $upload_dir . $old_product['image'];
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
    
            // Update dengan gambar baru
            $query = "UPDATE products SET name=?, brand=?, category=?, price=?, description=?, ingredients=?, skin_type=?, image=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssdssssi", $name, $brand, $category, $price, $description, $ingredients, $skin_type, $image, $product_id);
        } else {
            // Update tanpa mengubah gambar
            $query = "UPDATE products SET name=?, brand=?, category=?, price=?, description=?, ingredients=?, skin_type=? WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssdsssi", $name, $brand, $category, $price, $description, $ingredients, $skin_type, $product_id); // Ganti $status dengan $product_id
        }
    } else {
        // Add new product
        $query = "INSERT INTO products (name, brand, category, price, description, ingredients, skin_type, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssdssss", $name, $brand, $category, $price, $description, $ingredients, $skin_type, $image); // Perbaikan jumlah parameter
    }
      
    if ($stmt->execute()) {
        header("Location: products.php");
        exit;
    }
}

// Get all products
$query = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk | Admin Skincare Assistant</title>
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

        /* Products Section */
        .products-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }

        .image-preview {
            max-width: 200px;
            margin-top: 10px;
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
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 10px;
            position: relative;
            max-height: 80vh; /* Membatasi tinggi modal agar tidak melebihi 80% tinggi layar */
            overflow-y: auto; /* Menambahkan scrollbar jika kontennya lebih panjang dari modal */
            padding: 20px;
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

        .product-form {
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
        .form-group select,
        .form-group textarea {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
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
                    <a href="products.php" class="active">
                        <i class="fas fa-box"></i>
                        <span>Produk</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="articles.php">
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
                <h1 class="admin-title">Kelola Produk</h1>
                <button class="add-btn" onclick="openModal()">
                    <i class="fas fa-plus"></i>
                    Tambah Produk
                </button>
            </div>

            <div class="products-section">
                <!-- Products Table -->
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Merek</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="../uploads/products/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-image">
                                    <?php else: ?>
                                        <img src="../assets/default-product.jpg" alt="Default" class="product-image">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['brand']; ?></td>
                                <td><?php echo $row['category']; ?></td>
                                <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                <td class="action-buttons">
                                    <button class="btn-edit" onclick="editProduct(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-delete" onclick="deleteProduct(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Tambah Produk Baru</h2>
            <form class="product-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="product_id" name="product_id">
                <div class="form-group">
                    <label for="name">Nama Produk</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="brand">Merek</label>
                    <input type="text" id="brand" name="brand" required>
                </div>
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category" required>
                        <option value="cleanser">Pembersih</option>
                        <option value="toner">Toner</option>
                        <option value="serum">Serum</option>
                        <option value="moisturizer">Pelembab</option>
                        <option value="sunscreen">Sunscreen</option>
                        <option value="exfoliant">Exfoliant</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" id="price" name="price" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="ingredients">Kandungan</label>
                    <textarea id="ingredients" name="ingredients" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label for="skin_type">Jenis Kulit</label>
                    <select id="skin_type" name="skin_type">
                        <option value="All Skin Types">Semua Jenis Kulit</option>
                        <option value="Dry Skin">Kulit Kering</option>
                        <option value="Oily Skin">Kulit Berminyak</option>
                        <option value="Combination Skin">Kulit Kombinasi</option>
                        <option value="Sensitive Skin">Kulit Sensitif</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk</label>
                    <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                    <div id="imagePreview"></div>
                </div>
                <button type="submit" name="save_product" class="add-btn">
                    <i class="fas fa-save"></i>
                    Simpan Produk
                </button>
            </form>
        </div>
    </div>

    <script>
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

        function openModal(productId = null) {
            document.getElementById('productModal').style.display = 'block';
            document.getElementById('product_id').value = '';
            document.querySelector('.product-form').reset();
            document.getElementById('imagePreview').innerHTML = '';
            
            if (productId) {
                document.getElementById('modalTitle').textContent = 'Edit Produk';
                // Load product data
                fetch(`get_product.php?id=${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('product_id').value = data.id;
                        document.getElementById('name').value = data.name;
                        document.getElementById('brand').value = data.brand;
                        document.getElementById('category').value = data.category;
                        document.getElementById('price').value = data.price;
                        document.getElementById('description').value = data.description;
                        document.getElementById('ingredients').value = data.ingredients;
                        document.getElementById('skin_type').value = data.skin_type;
                        
                        if (data.image) {
                            const preview = document.getElementById('imagePreview');
                            const img = document.createElement('img');
                            img.src = `../uploads/products/${data.image}`;
                            img.className = 'image-preview';
                            preview.appendChild(img);
                        }
                    });
            } else {
                document.getElementById('modalTitle').textContent = 'Tambah Produk Baru';
            }
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function editProduct(id) {
            openModal(id);
        }

        function deleteProduct(id) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="delete_product" value="1">
                    <input type="hidden" name="product_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('productModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html> 