
<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch user's orders
$stmt = $conn->prepare("SELECT o.*, p.name as product_name, p.image as product_image, p.quantity as product_stock
                        FROM orders o
                        JOIN products p ON o.product_id = p.id
                        WHERE o.user_id = ? AND o.is_cancelled = 0
                        ORDER BY o.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order History</title>
    <link rel="stylesheet" href="css/order_history.css">
 
</head>
<body>

<?php include 'header.php'; ?>

    <h1>Your Order History</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="order-list">
        <?php if (empty($orders)): ?>
            <p style="text-align: center; font-size: 18px;">You haven't placed any orders yet.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <?php
                    $statusClass = match ($order['delivery_status']) {
                        'pending' => 'status-pending',
                        'shipped' => 'status-shipped',
                        'delivered' => 'status-delivered',
                        'cancelled' => 'status-cancelled',
                        default => 'status-pending',
                    };
                ?>
                <div class="order-card">
                    <img src="<?= htmlspecialchars($order['product_image']) ?>" alt="Product Image">
                    <div class="order-details">
                        <h3><?= htmlspecialchars($order['product_name']) ?></h3>
                        <p>Quantity: <strong><?= $order['quantity'] ?></strong></p>
                        <p>Total: <strong>Rs <?= number_format($order['total_price'], 2) ?></strong></p>
                        <p>Order Date: <?= date('M j, Y g:i a', strtotime($order['created_at'])) ?></p>
                        <span class="order-status <?= $statusClass ?>">
                            <?= ucfirst($order['delivery_status']) ?>
                        </span>
                    </div>
                    
                    <?php if ($order['delivery_status'] !== 'delivered' && $order['delivery_status'] !== 'cancelled'): ?>
                        <form method="POST" action="cancel_order.php" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <input type="hidden" name="product_id" value="<?= $order['product_id'] ?>">
                            <input type="hidden" name="quantity" value="<?= $order['quantity'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="cancel-button">Cancel Order</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</body>
</html>