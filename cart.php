<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart functionality
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['add_to_cart'])) {
    $product_id = $_GET['add_to_cart'];
    
    // Check if product exists and has available quantity
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND quantity > 0");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += 1;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1,
                'max_quantity' => $product['quantity']
            ];
        }
        
        $_SESSION['message'] = "Product added to cart!";
    } else {
        $_SESSION['error'] = "Product not available or out of stock";
    }
    
    header("Location: user_products.php");
    exit();
}

// Update cart quantities
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $index => $quantity) {
        if (isset($_SESSION['cart'][$index])) {
            $quantity = max(1, min($quantity, $_SESSION['cart'][$index]['max_quantity']));
            $_SESSION['cart'][$index]['quantity'] = $quantity;
        }
    }
    $_SESSION['message'] = "Cart updated!";
    header("Location: cart.php");
    exit();
}

// Remove item from cart
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['remove_item'])) {
    $index = $_GET['remove_item'];
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        $_SESSION['message'] = "Item removed from cart";
    }
    header("Location: cart.php");
    exit();
}

// Calculate cart totals (removed tax calculation)
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }


        /* Messages */
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            animation: fadeIn 0.5s ease-out;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Page Title */
        h1 {
            font-size: 2.5rem;
            color: #222;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

        /* Cart Layout */
        .cart-container {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
        }

        /* Cart Items */
        .cart-items, .cart-summary {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .cart-items:hover, .cart-summary:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        /* Cart Item */
        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 10px;
            background-color: #f5f5f5;
            transition: all 0.3s ease;
        }

        .cart-item-image:hover {
            transform: scale(1.05);
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .cart-item-price {
            font-size: 1.2rem;
            color: #6c5ce7;
            margin-bottom: 10px;
            font-weight: 600;
        }

        /* Quantity Controls */
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .quantity-input:focus {
            border-color: #6c5ce7;
            outline: none;
        }

        /* Remove Item */
        .remove-item {
            color: #ff6f61;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .remove-item:hover {
            color: #e74c3c;
            text-decoration: underline;
        }

        /* Cart Summary */
        .summary-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
            font-weight: 700;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #eee;
        }

        .summary-total span {
            color: #6c5ce7;
        }

        /* Buttons */
        .update-cart-btn, .checkout-btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .update-cart-btn {
            background: linear-gradient(45deg, #3498db, #2c3e50);
            color: white;
        }

        .update-cart-btn:hover {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .checkout-btn {
            background: linear-gradient(45deg, #6c5ce7, #ff6f61);
            color: white;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background: linear-gradient(45deg, #ff6f61, #6c5ce7);
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 50px;
            font-size: 1.5rem;
            color: #555;
        }

        .empty-cart a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: linear-gradient(45deg, #6c5ce7, #ff6f61);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .empty-cart a:hover {
            background: linear-gradient(45deg, #ff6f61, #6c5ce7);
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .cart-container {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            h1 {
                font-size: 2rem;
            }
            
            .cart-item {
                flex-direction: column;
                text-align: center;
            }
            
            .cart-item-image {
                margin: 0 auto 15px;
            }
            
            .cart-item-quantity {
                justify-content: center;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include 'header.php'; ?>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <h1>Your Shopping Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="user_products.php">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <form method="POST" action="cart.php">
                        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <div class="cart-item">
                                <?php if ($item['image']): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="cart-item-image">
                                <?php endif; ?>
                                <div class="cart-item-details">
                                    <h3 class="cart-item-title"><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="cart-item-price">Rs<?= number_format($item['price'], 2) ?></p>
                                    <div class="cart-item-quantity">
                                        <label for="quantity-<?= $index ?>">Quantity:</label>
                                        <input type="number" 
                                               id="quantity-<?= $index ?>" 
                                               name="quantities[<?= $index ?>]" 
                                               class="quantity-input" 
                                               min="1" 
                                               max="<?= $item['max_quantity'] ?>" 
                                               value="<?= $item['quantity'] ?>">
                                    </div>
                                    <a href="cart.php?remove_item=<?= $index ?>" class="remove-item">Remove</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" name="update_cart" class="update-cart-btn">Update Cart</button>
                    </form>
                </div>

                <div class="cart-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span>Rs<?= number_format($total, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    <a href="user_products.php" class="checkout-btn" style="background-color: #3498db;">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>