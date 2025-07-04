<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the item is already in the cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch();

    if ($item) {
        // If already in cart, increase quantity
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $update_stmt->execute([$item['id']]);
    } else {
        // If not, insert new record
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert_stmt->execute([$user_id, $product_id]);
    }

    $_SESSION['message'] = "Product added to cart!";
}

header("Location: user_products.php");
exit();
