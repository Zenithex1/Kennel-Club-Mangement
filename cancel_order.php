<?php
session_start();
include 'db.php';

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method");
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    die("Not authenticated");
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token mismatch");
}

// Get order details
$order_id = (int)$_POST['order_id'];
$user_id = (int)$_SESSION['user_id'];

try {
    $conn->beginTransaction();
    
    // 1. Verify and lock the order
    $stmt = $conn->prepare("SELECT o.*, p.id as product_id 
                          FROM orders o JOIN products p ON o.product_id = p.id
                          WHERE o.id = ? AND o.user_id = ?
                          AND o.is_cancelled = 0
                          FOR UPDATE");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception("Order not found or already cancelled");
    }

    // 2. Restore product quantity
    $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")
         ->execute([$order['quantity'], $order['product_id']]);

    // 3. Mark order as cancelled (removed cancelled_at because column doesn't exist)
    $conn->prepare("UPDATE orders SET 
                  delivery_status = 'cancelled',
                  is_cancelled = 1
                  WHERE id = ?")
         ->execute([$order_id]);

    $conn->commit();
    $_SESSION['message'] = "Order #$order_id cancelled successfully";
    
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Cancellation failed: " . $e->getMessage());
    $_SESSION['error'] = "Failed to cancel order: " . $e->getMessage();
}

header("Location: order_history.php");
exit();
?>
