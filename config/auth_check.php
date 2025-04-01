<?php
session_start();

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk memastikan user harus login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login_user.php");
        exit;
    }
}

// Fungsi untuk memastikan user yang sudah login tidak bisa akses halaman login/register
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: pages/home.php");
        exit;
    }
}
?> 