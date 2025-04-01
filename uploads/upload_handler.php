<?php
class UploadHandler {
    private $uploadDir;
    private $allowedTypes;
    private $maxSize;
    private $fileName;

    public function __construct($uploadDir = 'products/') {
        $this->uploadDir = dirname(__FILE__) . '/' . $uploadDir;
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $this->maxSize = 5 * 1024 * 1024; // 5MB
        
        // Buat direktori jika belum ada
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function handleUpload($file) {
        try {
            // Validasi file
            $this->validateFile($file);
            
            // Generate nama file unik
            $this->fileName = $this->generateFileName($file['name']);
            
            // Pindahkan file
            if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $this->fileName)) {
                return [
                    'success' => true,
                    'filename' => $this->fileName,
                    'path' => $this->uploadDir . $this->fileName
                ];
            } else {
                throw new Exception('Gagal mengupload file.');
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function validateFile($file) {
        // Cek apakah ada error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error dalam upload file.');
        }

        // Cek ukuran file
        if ($file['size'] > $this->maxSize) {
            throw new Exception('Ukuran file terlalu besar. Maksimal 5MB.');
        }

        // Cek tipe file
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $this->allowedTypes)) {
            throw new Exception('Tipe file tidak diizinkan. Hanya JPG, PNG, dan GIF yang diperbolehkan.');
        }
    }

    private function generateFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    public function deleteFile($filename) {
        $filePath = $this->uploadDir . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public function getUploadedFiles() {
        $files = [];
        if (is_dir($this->uploadDir)) {
            $files = array_diff(scandir($this->uploadDir), ['.', '..']);
        }
        return $files;
    }
}

// Contoh penggunaan untuk API endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploader = new UploadHandler();
    $result = $uploader->handleUpload($_FILES['image']);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Endpoint untuk menghapus file
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['filename'])) {
    $uploader = new UploadHandler();
    $result = $uploader->deleteFile($_GET['filename']);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $result]);
    exit;
}

// Endpoint untuk mendapatkan daftar file
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $uploader = new UploadHandler();
    $files = $uploader->getUploadedFiles();
    
    header('Content-Type: application/json');
    echo json_encode(['files' => $files]);
    exit;
}
?> 