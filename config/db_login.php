<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'user_login';

// Create connection
$conn_login = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn_login->connect_error) {
    die("Connection failed: " . $conn_login->connect_error);
}

// Set charset to utf8
$conn_login->set_charset("utf8");
?> 