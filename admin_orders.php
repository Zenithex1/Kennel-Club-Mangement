<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Update order status if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];
        
        // If changing to cancelled, restore product quantity
        if ($new_status == 'cancelled') {
            // Get order quantity and product ID first
            $stmt = $conn->prepare("SELECT product_id, quantity FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order_info = $stmt->fetch();
            
            // Restore the quantity
            $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
                 ->execute([$order_info['quantity'], $order_info['product_id']]);
        }
        
        $stmt = $conn->prepare("UPDATE orders SET delivery_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $_SESSION['message'] = "Order status updated!";
    }
    
    // Handle order deletion
    if (isset($_POST['delete_order'])) {
        $order_id = $_POST['order_id'];
        
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $_SESSION['message'] = "Order deleted successfully!";
    }
}

// Fetch all orders with shipping address
$stmt = $conn->query("SELECT o.*, p.name as product_name, u.username, o.shipping_address
                      FROM orders o
                      JOIN products p ON o.product_id = p.id
                      JOIN users u ON o.user_id = u.id
                      ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Orders</title>
    <link rel="stylesheet" href="csss/admin_orders.css">
    <style>
        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<header>
    <?php include('admin_header.php'); ?>
</header>

<h1>Order Management</h1>

<?php if (isset($_SESSION['message'])): ?>
    <div class="message success"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
<?php endif; ?>

<div class="order-list">
    <?php if (empty($orders)): ?>
        <p style="text-align: center;">No orders found.</p>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <h3>Order #<?= $order['id'] ?></h3>
                <p>Product: <?= htmlspecialchars($order['product_name']) ?></p>
                <p>Customer: <?= htmlspecialchars($order['username']) ?></p>
                <p>Quantity: <?= $order['quantity'] ?></p>
                <p>Shipping Address: <?= htmlspecialchars($order['shipping_address']) ?></p>
                <p>Total: Rs <?= number_format($order['total_price'], 2) ?></p>
                <p>Order Date: <?= date('M j, Y g:i a', strtotime($order['created_at'])) ?></p>
                <p>
                    Status: 
                    <span class="order-status status-<?= $order['delivery_status'] ?>">
                        <?= ucfirst($order['delivery_status']) ?>
                    </span>
                </p>
                
                <form method="POST" class="status-form">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <select name="status">
                        <option value="pending" <?= $order['delivery_status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="shipped" <?= $order['delivery_status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $order['delivery_status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $order['delivery_status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status">Update Status</button>
                </form>
                
                <?php if ($order['delivery_status'] == 'cancelled' || $order['delivery_status'] == 'delivered'): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this order?');">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <button type="submit" name="delete_order" class="delete-btn">Delete Order</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>