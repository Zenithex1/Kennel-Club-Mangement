<?php
session_start();

// Strong cache prevention headers
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Clear any existing session data
    session_unset();
    session_destroy();
    
    // Redirect to login with cache prevention
    header("Location: login.php");
    exit();
}

include 'db.php';

// Fetch the user's orders
$stmt = $conn->prepare("SELECT orders.*, products.name AS product_name, products.price 
                        FROM orders 
                        JOIN products ON orders.product_id = products.id 
                        WHERE orders.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <!-- Cache prevention meta tags -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="css/user_orders.css">
    <script>
        // Prevent back button from showing cached page
        history.pushState(null, null, document.URL);
        window.addEventListener('popstate', function() {
            history.pushState(null, null, document.URL);
            if (performance.navigation.type === 2) { // If back/forward button used
                window.location.reload();
            }
        });
    </script>
</head>
<body>
    <header>
        <?php include 'header.php'; ?>
        <h1>My Orders</h1>
    </header>
    
    <main>
        <h2>Order History</h2>
        <div class="order-list">
            <?php if (empty($orders)): ?>
                <p>You have no orders yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <h3><?php echo htmlspecialchars($order['product_name']); ?></h3>
                        <p><strong>Quantity:</strong> <?php echo htmlspecialchars($order['quantity']); ?></p>
                        <p><strong>Total Price:</strong> $<?php echo htmlspecialchars(number_format($order['total_price'], 2)); ?></p>
                        <p><strong>Status:</strong> <span class="status-<?php echo htmlspecialchars(strtolower($order['status'])); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></p>
                        <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($order['order_date']))); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>