<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle product purchase
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['buy_product_id'])) {
    $product_id = $_GET['buy_product_id'];
    
    try {
        // Check if product exists and has available quantity
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND quantity > 0");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Decrease quantity
            $conn->beginTransaction();
            $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - 1 WHERE id = ?");
            $update_stmt->execute([$product_id]);
            
            // Create order record
            $order_stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price, status) 
                                        VALUES (?, ?, 1, ?, 'pending')");
            $order_stmt->execute([
                $_SESSION['user_id'],
                $product_id,
                $product['price']
            ]);
            
            $conn->commit();
            $_SESSION['message'] = "Product purchased successfully!";
        } else {
            $_SESSION['error'] = "Product not available or out of stock";
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error processing purchase: " . $e->getMessage();
    }
    
    header("Location: user_products.php");
    exit();
}

// Fetch all available products (quantity > 0)
$stmt = $conn->query("SELECT * FROM products WHERE quantity > 0");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dog Products</title>
    <link rel="stylesheet" href="css/user_products.css">
    <style>
        /* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Body styling */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    color: #333;
}





/* Messages */
.message {
    max-width: 800px;
    margin: 20px auto;
    padding: 12px;
    border-radius: 5px;
    text-align: center;
    font-size: 1rem;
}

.message.success {
    background-color: #d4edda;
    color: #155724;
}

.message.error {
    background-color: #f8d7da;
    color: #721c24;
}

/* Main content */
main {
    max-width: 1100px;
    margin: 30px auto;
    padding: 0 20px;
}

/* Page heading */
main h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 25px;
}

/* Product list layout */
.product-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
}

/* Product card styling */
.product-card {
    background-color: #fff;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease-in-out;
    text-align: center;
}

.product-card:hover {
    transform: translateY(-5px);
}

/* Product image */
/* Product image */
.product-card img {
    width: 100%;
    max-height: 180px; /* Restrict the image height */
    object-fit: contain; /* Ensure the entire image is visible */
    border-radius: 8px;
    margin-bottom: 15px;
}


/* Product info */
.product-card h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: #222;
}

.product-card p {
    font-size: 0.95rem;
    margin-bottom: 8px;
    color: #444;
}

/* Buy button */
.buy-button {
    display: inline-block;
    background-color: #ff6f61;
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-size: 1rem;
    transition: background-color 0.3s ease;
    margin-top: 10px;
}

.buy-button:hover {
    background-color: #e85b4f;
}

/* Out of stock message */
.out-of-stock {
    color: red;
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 600px) {
    .product-card img {
        height: 150px;
    }

    .product-card h3 {
        font-size: 1.1rem;
    }

    .buy-button {
        font-size: 0.9rem;
        padding: 8px 14px;
    }
}

    </style>
</head>
<body>
<header>
    <?php include 'header.php'; ?>

    </header>

    <main>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <h2 style="text-align:center;">Dog Products Available</h2>
        <div class="product-list">
            <?php if (empty($products)): ?>
                <p>No products available at the moment.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
                        <p><strong>Price:</strong> Rs<?= htmlspecialchars($product['price']) ?></p>
                        <p><strong>Quantity Available:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
                        
                        <?php if ($product['quantity'] > 0): ?>
                            <a href="cart.php?add_to_cart=<?= $product['id'] ?>" class="buy-button">Add to Cart</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>