<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT a.*, admin.username as author_name 
              FROM articles a 
              LEFT JOIN admin ON a.author_id = admin.id 
              WHERE a.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $article = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> | Skincare Assistant</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="article-detail">
        <h1><?php echo htmlspecialchars($article['title']); ?></h1>
        <div class="article-meta">
            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author_name']); ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($article['created_at'])); ?></span>
        </div>
        <?php if (!empty($article['image_url'])): ?>
            <img src="../uploads/articles/<?php echo htmlspecialchars($article['image_url']); ?>" 
                 alt="<?php echo htmlspecialchars($article['title']); ?>" class="article-image">
        <?php endif; ?>
        <div class="article-content">
            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
        </div>
        <a href="../index.php#articles" class="back-btn">Kembali ke Artikel</a>
    </div>
</body>
</html> 