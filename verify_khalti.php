<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Validate CSRF token
if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize variables
$errors = [];
$required_fields = ['quantity', 'shipping_address', 'payment_method'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "$field is required";
    }
}

// Validate input data
$quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
if ($quantity === false) {
    $errors[] = "Invalid quantity";
}

$shipping_address = htmlspecialchars(trim($_POST['shipping_address']), ENT_QUOTES, 'UTF-8');
if (strlen($shipping_address) < 10) {
    $errors[] = "Shipping address too short";
}

$payment_method = in_array($_POST['payment_method'], ['cod', 'khalti']) ? $_POST['payment_method'] : null;
if (!$payment_method) {
    $errors[] = "Invalid payment method";
}

// Khalti-specific validation
$khalti_token = $_POST['khalti_token'] ?? null;
$khalti_amount = $_POST['khalti_amount'] ?? null;
if ($payment_method === 'khalti' && (empty($khalti_token) || empty($khalti_amount))) {
    $errors[] = "Khalti payment details missing";
}

// Product validation
$product_id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
if (!$product_id) {
    $errors[] = "Invalid product ID";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit();
}

try {
    $conn->beginTransaction();

    // Get product with locking to prevent race conditions
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception("Product not found");
    }

    // Validate stock
    if ($quantity > $product['quantity']) {
        throw new Exception("Requested quantity not available");
    }

    // Process Khalti payment if selected
    if ($payment_method === 'khalti') {
        // Dummy Khalti credentials - replace with your actual test keys
        $khalti_secret_key = '16c19115f524422eb7a034ca04259fd5';
        $khalti_public_key = 'ea98ec93c791427d95c6b9188bd863bb';
        
        $url = "https://khalti.com/api/v2/payment/verify/";
        $payload = http_build_query([
            'token' => $khalti_token,
            'amount' => $khalti_amount
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Key $khalti_secret_key",
                "Content-Type: application/x-www-form-urlencoded"
            ],
            CURLOPT_TIMEOUT => 20
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            throw new Exception("Payment verification failed");
        }

        $payment_data = json_decode($response, true);
        if (!$payment_data || $payment_data['amount'] != $khalti_amount) {
            throw new Exception("Payment amount mismatch");
        }
    }

    // Calculate total
    $total_price = $product['price'] * $quantity;

    // Update product stock
    $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
         ->execute([$quantity, $product_id]);

    // Create order
    $order_stmt = $conn->prepare("INSERT INTO orders 
        (user_id, product_id, quantity, total_price, shipping_address, 
         payment_method, status, delivery_status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'paid', 'pending', NOW())");
    
    $order_stmt->execute([
        $_SESSION['user_id'],
        $product_id,
        $quantity,
        $total_price,
        $shipping_address,
        $payment_method
    ]);

    $order_id = $conn->lastInsertId();

    // If Khalti payment, record payment details
    if ($payment_method === 'khalti') {
        $payment_stmt = $conn->prepare("INSERT INTO payments 
            (order_id, payment_method, transaction_id, amount, status, raw_response)
            VALUES (?, 'khalti', ?, ?, 'verified', ?)");
        $payment_stmt->execute([
            $order_id,
            $khalti_token,
            $khalti_amount,
            json_encode($payment_data ?? [])
        ]);
    }

    $conn->commit();

    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id,
        'total' => $total_price
    ]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Order Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Order processing failed',
        'error' => $e->getMessage()
    ]);
}