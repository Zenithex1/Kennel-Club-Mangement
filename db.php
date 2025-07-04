<?php
$host = 'localhost';
$dbname = 'dog';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET time_zone = '+00:00'");
    $conn->exec("SET sql_mode = 'STRICT_TRANS_TABLES'");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>