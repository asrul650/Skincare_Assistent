<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$conn = $database->getConnection();

// Handle delete
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
}

// Get products
$stmt = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk | Admin</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/admin-header.php'; ?>
    
    <div class="admin-container">
        <div class="content-header">
            <h1>Kelola Produk</h1>
            <a href="add.php" class="btn">
                <i class="fas fa-plus"></i> Tambah Produk
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Brand</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="../../<?php echo $product['image_url']; ?>" 
                                 alt="<?php echo $product['name']; ?>"
                                 class="product-thumb">
                        </td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['brand']; ?></td>
                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td class="actions">
                            <a href="edit.php?id=<?php echo $product['id']; ?>" 
                               class="btn-small">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" class="delete-form" 
                                  onsubmit="return confirm('Yakin ingin menghapus?');">
                                <input type="hidden" name="id" 
                                       value="<?php echo $product['id']; ?>">
                                <button type="submit" name="delete" class="btn-small danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 