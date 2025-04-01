-- Hapus database jika sudah ada
DROP DATABASE IF EXISTS skincare_assistant;

-- Buat database baru
CREATE DATABASE skincare_assistant;

-- Gunakan database
USE skincare_assistant;

-- Buat tabel admin
CREATE TABLE admin (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buat tabel users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    birthdate DATE,
    skin_type VARCHAR(50),
    avatar VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'inactive',
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hapus tabel products yang lama jika ada
DROP TABLE IF EXISTS products;

-- Buat tabel products dengan struktur yang benar
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    ingredients TEXT,
    how_to_use TEXT,
    skin_type ENUM('oily', 'dry', 'combination', 'sensitive', 'normal', 'all') NOT NULL,
    concerns JSON,
    category VARCHAR(50),
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buat tabel product_reviews
CREATE TABLE product_reviews (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    product_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    rating INT(11) NOT NULL,
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Buat tabel reviews
CREATE TABLE reviews (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    rating INT(11) NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Buat tabel articles
CREATE TABLE articles (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    author_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admin(id) ON DELETE CASCADE
);

-- Buat tabel schedules
CREATE TABLE schedules (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    routine_name VARCHAR(255) NOT NULL,
    schedule_time DATETIME NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Buat tabel skin_concerns
CREATE TABLE skin_concerns (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    recommended_ingredients TEXT
);

-- Buat tabel activities
CREATE TABLE activities (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    activity_name VARCHAR(255) NOT NULL,
    user_name VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buat tabel consultations
CREATE TABLE IF NOT EXISTS consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_name VARCHAR(100) NOT NULL,
    consultation_date DATETIME NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    symptoms TEXT,
    consultation_type ENUM('online', 'offline') DEFAULT 'online',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Membuat tabel skin_analysis
CREATE TABLE IF NOT EXISTS skin_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skin_type ENUM('oily', 'dry', 'combination', 'sensitive', 'normal') NOT NULL,
    concerns TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Buat tabel skincare_routines
CREATE TABLE IF NOT EXISTS skincare_routines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    routine_name VARCHAR(100) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    frequency VARCHAR(50) NOT NULL,
    time_of_day VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert data admin default
INSERT INTO admin (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('sarah_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert data users
INSERT INTO users (username, email, password, full_name, phone, birthdate, skin_type, status) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '08123456789', '1990-05-15', 'Normal to Dry Skin', 'inactive'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '08123456780', '1995-07-20', 'Oily Skin', 'inactive'),
('maria_garcia', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maria Garcia', '08123456781', '1988-12-10', 'Kombinasi', 'inactive'),
('david_lee', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Lee', '08123456782', '1985-03-25', 'Sensitive Skin', 'inactive');

/* Insert data produk untuk setiap jenis kulit */
INSERT INTO products (name, brand, price, description, ingredients, how_to_use, skin_type, concerns) VALUES
/* Produk untuk kulit berminyak */
('Oil Control Facial Wash', 'SkinCare Plus', 75000, 'Pembersih wajah khusus untuk kulit berminyak', 'Salicylic Acid, Tea Tree Oil, Zinc', 'Gunakan 2 kali sehari, pagi dan malam', 'oily', '["acne", "blackheads"]'),
('Mattifying Toner', 'ClearSkin', 89000, 'Toner untuk mengurangi minyak berlebih', 'Witch Hazel, Niacinamide', 'Aplikasikan setelah mencuci wajah', 'oily', '["oily", "acne"]'),
('Oil-Free Moisturizer', 'HydraPlus', 120000, 'Pelembab ringan non-comedogenic', 'Hyaluronic Acid, Aloe Vera', 'Gunakan setelah toner', 'oily', '["oily", "hydration"]'),

/* Produk untuk kulit kering */
('Hydrating Cleanser', 'MoistCare', 85000, 'Pembersih lembut untuk kulit kering', 'Ceramides, Glycerin', 'Gunakan dengan lembut di wajah', 'dry', '["dryness", "sensitive"]'),
('Moisture Boost Toner', 'HydraSkin', 95000, 'Toner pelembab intensif', 'Hyaluronic Acid, Vitamin E', 'Aplikasikan dengan kapas', 'dry', '["dryness", "dullness"]'),
('Rich Moisturizer', 'DeepHydra', 150000, 'Krim pelembab kaya nutrisi', 'Shea Butter, Squalane', 'Aplikasikan ke wajah dan leher', 'dry', '["dryness", "wrinkles"]'),

/* Produk untuk kulit kombinasi */
('Balanced Foam Cleanser', 'BalancePlus', 82000, 'Pembersih untuk kulit kombinasi', 'Green Tea, Chamomile', 'Gunakan 2 kali sehari', 'combination', '["combination", "acne"]'),
('Balancing Toner', 'EquiSkin', 88000, 'Toner penyeimbang pH kulit', 'Centella Asiatica, BHA', 'Gunakan setelah cleansing', 'combination', '["combination", "blackheads"]'),
('Dual-Action Moisturizer', 'ZoneCare', 135000, 'Pelembab untuk berbagai zona wajah', 'Niacinamide, Peptides', 'Aplikasikan sesuai zona', 'combination', '["combination", "oily"]'),

/* Produk untuk kulit sensitif */
('Gentle Milk Cleanser', 'SoftCare', 89000, 'Pembersih super lembut', 'Colloidal Oatmeal, Allantoin', 'Usap dengan lembut', 'sensitive', '["sensitive", "redness"]'),
('Calming Toner', 'SootheSkin', 92000, 'Toner menenangkan kulit', 'Chamomile, Calendula', 'Tepuk lembut ke wajah', 'sensitive', '["sensitive", "irritation"]'),
('Soothing Moisturizer', 'CalmPlus', 145000, 'Pelembab untuk kulit sensitif', 'Centella Asiatica, Madecassoside', 'Aplikasikan dengan lembut', 'sensitive', '["sensitive", "dryness"]'),

/* Produk untuk kulit normal */
('Fresh Gel Cleanser', 'BasicCare', 78000, 'Pembersih seimbang untuk kulit normal', 'Glycerin, Vitamin B5', 'Gunakan pagi dan malam', 'normal', '["maintenance"]'),
('Hydrating Toner', 'NormalPlus', 85000, 'Toner untuk maintenance', 'Rose Water, Vitamin C', 'Aplikasikan setelah cleansing', 'normal', '["maintenance", "hydration"]'),
('Daily Moisturizer', 'Everyday', 115000, 'Pelembab sehari-hari', 'Vitamin E, Jojoba Oil', 'Gunakan rutin pagi dan malam', 'normal', '["maintenance", "protection"]');

-- Insert data product_reviews
INSERT INTO product_reviews (product_id, user_id, rating, review_text) VALUES
(1, 1, 5, 'Pembersih wajah yang sangat lembut dan tidak membuat kulit kering'),
(1, 2, 4, 'Bagus untuk kulit sensitif'),
(2, 1, 5, 'Toner terbaik yang pernah saya gunakan'),
(3, 3, 4, 'Vitamin C nya bekerja dengan baik'),
(4, 4, 5, 'Melembabkan tanpa membuat berminyak');

-- Insert data reviews
INSERT INTO reviews (user_id, product_id, rating, comment) VALUES
(1, 1, 5, 'Produk sangat bagus untuk kulit sensitif'),
(2, 2, 4, 'Toner yang menyegarkan'),
(3, 3, 5, 'Serum vitamin C yang efektif'),
(4, 4, 4, 'Krim pelembab yang ringan');

-- Insert data articles
INSERT INTO articles (title, content, image_url, author_id) VALUES
('Tips Merawat Kulit di Musim Panas', 'Artikel tentang cara merawat kulit saat cuaca panas...', 'summer-skincare.jpg', 1),
('Panduan Lengkap Skincare untuk Pemula', 'Artikel untuk pemula yang ingin mulai skincare...', 'beginner-guide.jpg', 1),
('Mengenal Kandungan Skincare', 'Penjelasan tentang bahan-bahan dalam produk skincare...', 'ingredients-guide.jpg', 2),
('Rutinitas Pagi vs Malam', 'Perbedaan perawatan kulit pagi dan malam...', 'routine-guide.jpg', 2);

-- Insert data schedules
INSERT INTO schedules (user_id, routine_name, schedule_time, notes) VALUES
(1, 'Pembersihan Pagi', '2024-02-20 07:00:00', 'Gunakan cleanser dengan lembut'),
(1, 'Rutinitas Malam', '2024-02-20 20:00:00', 'Jangan lupa double cleansing'),
(2, 'Masker Mingguan', '2024-02-25 10:00:00', 'Gunakan clay mask'),
(3, 'Eksfoliasi', '2024-02-22 19:00:00', 'Gunakan dengan hati-hati');

-- Insert data skin_concerns
INSERT INTO skin_concerns (name, description, recommended_ingredients) VALUES 
('Acne', 'Masalah jerawat dan breakouts', 'Salicylic Acid, Benzoyl Peroxide, Niacinamide'),
('Aging', 'Tanda-tanda penuaan seperti kerutan', 'Retinol, Peptides, Vitamin C'),
('Hyperpigmentation', 'Noda hitam dan ketidakrataan warna kulit', 'Vitamin C, Alpha Arbutin, Kojic Acid'),
('Dehydration', 'Kulit kering dan kurang hidrasi', 'Hyaluronic Acid, Glycerin, Ceramides'),
('Sensitivity', 'Kulit sensitif dan mudah iritasi', 'Centella Asiatica, Aloe Vera, Green Tea');

-- Insert data activities
INSERT INTO activities (activity_name, user_name, status) VALUES
('Login', 'john_doe', 'success'),
('Review Product', 'jane_smith', 'completed'),
('Add Schedule', 'maria_garcia', 'completed'),
('Update Profile', 'david_lee', 'success'),
('Purchase Product', 'john_doe', 'completed');

-- Masukkan data contoh untuk consultations
INSERT INTO consultations (user_id, doctor_name, consultation_date, status, symptoms, consultation_type) VALUES
(1, 'Dr. Sarah Wijaya, Sp.KK', NOW(), 'pending', 'Kulit kering dan sensitif', 'online'),
(1, 'Dr. Amanda Putri, Sp.KK', DATE_SUB(NOW(), INTERVAL 1 WEEK), 'completed', 'Jerawat di area dagu', 'offline');

-- Masukkan data contoh untuk skin_analysis
INSERT INTO skin_analysis (user_id, skin_type, concerns) VALUES
(1, 'oily', '["acne", "blackheads"]'),
(1, 'dry', '["dryness", "wrinkles"]'),
(1, 'combination', '["combination", "blackheads"]'),
(1, 'sensitive', '["sensitive", "redness"]'),
(1, 'normal', '["maintenance"]');

-- Tambahkan data contoh untuk testing
INSERT INTO users (username, email, password, full_name, phone, birthdate, skin_type) VALUES
('user_test', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User Test', '081234567890', '2000-01-01', 'Normal');

-- Tambahkan data contoh untuk consultations
INSERT INTO consultations (user_id, doctor_name, consultation_date, status, symptoms, consultation_type) VALUES
(1, 'Dr. Sarah Wijaya, Sp.KK', NOW(), 'pending', 'Kulit kering dan sensitif', 'online'),
(1, 'Dr. Amanda Putri, Sp.KK', DATE_SUB(NOW(), INTERVAL 1 WEEK), 'completed', 'Jerawat di area dagu', 'offline');

-- Tambahkan data contoh untuk skin_analysis
INSERT INTO skin_analysis (user_id, skin_type, concerns) VALUES
(1, 'oily', '["acne", "blackheads"]'),
(1, 'dry', '["dryness", "wrinkles"]'),
(1, 'combination', '["combination", "blackheads"]'),
(1, 'sensitive', '["sensitive", "redness"]'),
(1, 'normal', '["maintenance"]');

-- Tambahkan data contoh untuk skincare_routines
INSERT INTO skincare_routines (user_id, routine_name, product_name, frequency, time_of_day, notes) VALUES
(1, 'Pembersihan Pagi', 'Facial Wash Brand A', 'Setiap Hari', 'Pagi', 'Gunakan dengan lembut dan bilas dengan air hangat'),
(1, 'Pelembab Pagi', 'Moisturizer Brand B', 'Setiap Hari', 'Pagi', 'Aplikasikan setelah toner'),
(1, 'Sunscreen', 'Sunscreen SPF 50 Brand C', 'Setiap Hari', 'Pagi', 'Aplikasikan sebagai langkah terakhir skincare pagi'),
(1, 'Pembersihan Malam', 'Facial Wash Brand A', 'Setiap Hari', 'Malam', 'Double cleansing untuk membersihkan makeup'),
(1, 'Treatment', 'Serum Niacinamide', 'Setiap Hari', 'Malam', 'Gunakan setelah toner, sebelum moisturizer');

-- Tabel untuk analisis kulit
CREATE TABLE IF NOT EXISTS `skin_analysis` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `skin_type` VARCHAR(50) NOT NULL,
    `concerns` TEXT,
    `analysis_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk produk skincare
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `brand` VARCHAR(100) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `description` TEXT,
    `ingredients` TEXT,
    `how_to_use` TEXT,
    `skin_type` VARCHAR(50),
    `category` VARCHAR(50),
    `image` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data sample untuk produk
INSERT INTO `products` (`name`, `brand`, `price`, `description`, `ingredients`, `how_to_use`, `skin_type`, `category`) VALUES
-- Produk untuk kulit berminyak
('Oil Control Facial Wash', 'SkinCare Pro', 150000, 'Pembersih wajah untuk kulit berminyak', 'Salicylic Acid, Tea Tree Oil, Glycerin', 'Gunakan 2 kali sehari pagi dan malam', 'oily', 'cleanser'),
('Oil Control Toner', 'SkinCare Pro', 180000, 'Toner untuk kulit berminyak', 'BHA, Niacinamide, Witch Hazel', 'Aplikasikan setelah mencuci wajah', 'oily', 'toner'),
('Light Moisturizer', 'SkinCare Pro', 200000, 'Pelembab ringan untuk kulit berminyak', 'Hyaluronic Acid, Aloe Vera, Glycerin', 'Aplikasikan setelah toner', 'oily', 'moisturizer'),

-- Produk untuk kulit kering
('Hydrating Facial Wash', 'SkinCare Pro', 150000, 'Pembersih wajah untuk kulit kering', 'Ceramide, Hyaluronic Acid, Glycerin', 'Gunakan 2 kali sehari pagi dan malam', 'dry', 'cleanser'),
('Hydrating Toner', 'SkinCare Pro', 180000, 'Toner untuk kulit kering', 'Hyaluronic Acid, Glycerin, Panthenol', 'Aplikasikan setelah mencuci wajah', 'dry', 'toner'),
('Rich Moisturizer', 'SkinCare Pro', 220000, 'Pelembab kaya untuk kulit kering', 'Ceramide, Peptide, Shea Butter', 'Aplikasikan setelah toner', 'dry', 'moisturizer'),

-- Produk untuk kulit kombinasi
('Balanced Facial Wash', 'SkinCare Pro', 150000, 'Pembersih wajah untuk kulit kombinasi', 'Centella Asiatica, Green Tea, Glycerin', 'Gunakan 2 kali sehari pagi dan malam', 'combination', 'cleanser'),
('Balancing Toner', 'SkinCare Pro', 180000, 'Toner untuk kulit kombinasi', 'AHA/BHA, Niacinamide, Centella Asiatica', 'Aplikasikan setelah mencuci wajah', 'combination', 'toner'),
('Gel Cream Moisturizer', 'SkinCare Pro', 200000, 'Pelembab gel krim untuk kulit kombinasi', 'Hyaluronic Acid, Niacinamide, Ceramide', 'Aplikasikan setelah toner', 'combination', 'moisturizer'),

-- Produk untuk kulit sensitif
('Gentle Facial Wash', 'SkinCare Pro', 160000, 'Pembersih wajah untuk kulit sensitif', 'Centella Asiatica, Chamomile, Allantoin', 'Gunakan 2 kali sehari pagi dan malam', 'sensitive', 'cleanser'),
('Calming Toner', 'SkinCare Pro', 190000, 'Toner untuk kulit sensitif', 'Centella Asiatica, Aloe Vera, Panthenol', 'Aplikasikan setelah mencuci wajah', 'sensitive', 'toner'),
('Soothing Moisturizer', 'SkinCare Pro', 210000, 'Pelembab menenangkan untuk kulit sensitif', 'Ceramide, Madecassoside, Panthenol', 'Aplikasikan setelah toner', 'sensitive', 'moisturizer'),

-- Produk untuk kulit normal
('Daily Facial Wash', 'SkinCare Pro', 150000, 'Pembersih wajah untuk kulit normal', 'Glycerin, Panthenol, Allantoin', 'Gunakan 2 kali sehari pagi dan malam', 'normal', 'cleanser'),
('Refreshing Toner', 'SkinCare Pro', 180000, 'Toner untuk kulit normal', 'Hyaluronic Acid, Vitamin B5, Allantoin', 'Aplikasikan setelah mencuci wajah', 'normal', 'toner'),
('Daily Moisturizer', 'SkinCare Pro', 200000, 'Pelembab untuk kulit normal', 'Ceramide, Vitamin E, Glycerin', 'Aplikasikan setelah toner', 'normal', 'moisturizer');

-- Tambahkan sunscreen untuk semua jenis kulit
INSERT INTO `products` (`name`, `brand`, `price`, `description`, `ingredients`, `how_to_use`, `skin_type`, `category`) VALUES
('Daily Protection Sunscreen', 'SkinCare Pro', 250000, 'Sunscreen SPF 50 PA++++ untuk semua jenis kulit', 'UV Filters, Niacinamide, Vitamin E', 'Aplikasikan sebagai langkah terakhir skincare pagi', 'all', 'sunscreen');

UPDATE products 
SET image = CASE 
    WHEN category = 'cleanser' THEN '../assets/images/products/cleanser.jpg'
    WHEN category = 'toner' THEN '../assets/images/products/toner.jpg'
    WHEN category = 'serum' THEN '../assets/images/products/serum.jpg'
    WHEN category = 'moisturizer' THEN '../assets/images/products/moisturizer.jpg'
    WHEN category = 'sunscreen' THEN '../assets/images/products/sunscreen.jpg'
    ELSE '../assets/images/products/default.jpg'
END;

DROP TABLE IF EXISTS feedback;

CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);