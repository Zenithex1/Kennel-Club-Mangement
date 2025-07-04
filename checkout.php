<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty";
    header('Location: cart.php');
    exit();
}

if (isset($_GET['payment_success'])) {
    $_SESSION['message'] = "Payment successful! Your order has been placed.";
    unset($_SESSION['cart']);
    header('Location: order_history.php');
    exit();
}

$product_ids = array_column($_SESSION['cart'], 'product_id');
$placeholders = rtrim(str_repeat('?,', count($product_ids)), ',');
$stmt = $conn->prepare("SELECT id, name, price, quantity FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$product_data = [];
foreach ($products as $product) {
    $product_data[$product['id']] = $product;
}

foreach ($_SESSION['cart'] as $item) {
    if (!isset($product_data[$item['product_id']])) {
        $_SESSION['error'] = "Product '{$item['name']}' is no longer available";
        header('Location: cart.php');
        exit();
    }
    
    $db_product = $product_data[$item['product_id']];
    if ($item['quantity'] > $db_product['quantity']) {
        $_SESSION['error'] = "Product '{$item['name']}' only has {$db_product['quantity']} items available";
        header('Location: cart.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];
    
    try {
        $conn->beginTransaction();
        
        foreach ($_SESSION['cart'] as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $total_price = $price * $quantity;
            
            $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?")
                 ->execute([$quantity, $product_id]);
            
            $conn->prepare("INSERT INTO orders 
                           (user_id, product_id, quantity, total_price, shipping_address, payment_method, status, delivery_status) 
                           VALUES (?, ?, ?, ?, ?, ?, 'paid', 'pending')")
                 ->execute([
                     $_SESSION['user_id'],
                     $product_id,
                     $quantity,
                     $total_price,
                     $shipping_address,
                     $payment_method
                 ]);
        }
        
        $conn->commit();
        $_SESSION['message'] = "Order placed successfully!";
        unset($_SESSION['cart']);
        header('Location: order_history.php?payment_success=1');
        exit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: checkout.php");
        exit();
    }
}

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
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
       <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1, h2 {
            font-size: 2.5rem;
            color: #222;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
        }

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

        .checkout-container {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
        }

        .order-summary, .checkout-form {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }

        .order-summary:hover, .checkout-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        .order-item {
            display: flex;
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
            align-items: center;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-image {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-right: 20px;
            border-radius: 10px;
            background-color: #f5f5f5;
            transition: all 0.3s ease;
        }

        .order-item-image:hover {
            transform: scale(1.05);
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-size: 1.4rem;
            color: #333;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .order-item-quantity {
            font-size: 1.2rem;
            color: #6c5ce7;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .item-subtotal {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .subtotal-amount {
            color: #ff6f61;
        }

        .summary-total {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .summary-total span {
            color: #6c5ce7;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #6c5ce7;
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* Enhanced Payment Modal */
        .payment-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .payment-modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-origin: center;
            position: relative;
            overflow: hidden;
        }

        .payment-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .payment-modal-title {
            font-size: 1.8rem;
            color: #6c5ce7;
            font-weight: 700;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #ff6f61;
        }

        .payment-method-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .payment-tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            position: relative;
        }

        .payment-tab.active {
            color: #6c5ce7;
            border-bottom-color: #6c5ce7;
        }

        .payment-tab i {
            margin-right: 8px;
        }

        .payment-tab-content {
            display: none;
            animation: fadeIn 0.5s;
        }

        .payment-tab-content.active {
            display: block;
        }

        .payment-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: linear-gradient(45deg, #6c5ce7, #ff6f61);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            background: linear-gradient(45deg, #ff6f61, #6c5ce7);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-loading .btn-text {
            visibility: hidden;
            opacity: 0;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            border: 3px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: button-loading-spinner 1s ease infinite;
        }

        .payment-success {
            text-align: center;
            padding: 30px;
            display: none;
        }

        .payment-success i {
            font-size: 5rem;
            color: #4CAF50;
            margin-bottom: 20px;
            animation: bounceIn 0.8s;
        }

        .payment-success h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #4CAF50;
        }

        .payment-success p {
            color: #666;
            margin-bottom: 25px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlideIn {
            from { 
                opacity: 0;
                transform: scale(0.9) translateY(20px);
            }
            to { 
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes button-loading-spinner {
            from {
                transform: rotate(0turn);
            }
            to {
                transform: rotate(1turn);
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.1);
                opacity: 0;
            }
            60% {
                transform: scale(1.2);
                opacity: 1;
            }
            100% {
                transform: scale(1);
            }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .checkout-container {
                flex-direction: column;
            }
            
            .order-summary, .checkout-form {
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            h1, h2 {
                font-size: 2rem;
            }
            
            .order-item {
                flex-direction: column;
                text-align: center;
            }
            
            .order-item-image {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .payment-method-tabs {
                flex-direction: column;
            }

            .payment-tab {
                border-bottom: none;
                border-left: 3px solid transparent;
            }

            .payment-tab.active {
                border-bottom: none;
                border-left-color: #6c5ce7;
            }

            .payment-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="order-summary">
                <h2>Your Order</h2>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <div class="order-item" data-index="<?= $index ?>">
                        <?php if ($item['image']): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-item-image">
                        <?php endif; ?>
                        <div class="order-item-details">
                            <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="order-item-quantity">Quantity: <?= htmlspecialchars($item['quantity']) ?></div>
                            
                            <div class="item-subtotal">Subtotal: Rs <span class="subtotal-amount"><?= number_format($item['price'] * $item['quantity'], 2) ?></span></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="summary-total">
                    <div>Total: Rs <span id="cart-total"><?= number_format($total, 2) ?></span></div>
                </div>
            </div>
            
            <div class="checkout-form">
                <h2>Order Information</h2>
                <form method="POST" id="orderForm">
                    <input type="hidden" name="quantities" id="quantities-data">
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address</label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="paynow">PayNow</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="cash_on_delivery">Cash on Delivery</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn" id="placeOrderBtn">
                        <span class="btn-text">Place Order</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="payment-modal" id="paymentModal">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <div class="payment-modal-title">Complete Payment</div>
                <button class="close-modal" onclick="closePaymentModal()">&times;</button>
            </div>
            
            <div class="payment-method-tabs">
                <div class="payment-tab active" data-tab="paynow">
                    <i class="fas fa-wallet"></i> PayNow
                </div>
                <div class="payment-tab" data-tab="mobile-banking">
                    <i class="fas fa-mobile-alt"></i> Mobile Banking
                </div>
            </div>
            
            <div class="payment-tab-content active" id="paynow-tab">
                <div class="form-group">
                    <label for="paynow-username">PayNow Username</label>
                    <input type="text" id="paynow-username" placeholder="Your PayNow ID">
                </div>
                <div class="form-group">
                    <label for="paynow-password">PayNow Password</label>
                    <input type="password" id="paynow-password" placeholder="Password">
                </div>
                <div class="form-group">
                    <label for="paynow-mobile">Mobile Number</label>
                    <input type="tel" id="paynow-mobile" placeholder="98XXXXXXXX">
                </div>
            </div>
            
            <div class="payment-tab-content" id="mobile-banking-tab">
                <div class="form-group">
                    <label for="bank-name">Bank Name</label>
                    <select id="bank-name">
                        <option value="">Select your bank</option>
                        <option value="nabil">Nabil Bank</option>
                        <option value="nicasia">NIC Asia Bank</option>
                        <option value="himalayan">Himalayan Bank</option>
                        <option value="standard-chartered">Standard Chartered</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mobile-number">Mobile Number</label>
                    <input type="tel" id="mobile-number" placeholder="98XXXXXXXX">
                </div>
                <div class="form-group">
                    <label for="mpin">MPIN</label>
                    <input type="password" id="mpin" placeholder="6-digit MPIN" maxlength="6">
                </div>
            </div>
            
            <div class="payment-success" id="paymentSuccess">
                <i class="fas fa-check-circle"></i>
                <h3>Payment Successful!</h3>
                <p>Your order has been placed successfully.</p>
                <p>You'll be redirected shortly...</p>
            </div>
            
            <div class="payment-actions" id="paymentActions">
                <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">
                    <span class="btn-text">Cancel</span>
                </button>
                <button type="button" class="btn" id="confirmPaymentBtn">
                    <span class="btn-text">Pay Rs <?= number_format($total, 2) ?></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Payment Modal Functions
        const paymentModal = document.getElementById('paymentModal');
        const paymentTabs = document.querySelectorAll('.payment-tab');
        const paymentTabContents = document.querySelectorAll('.payment-tab-content');
        const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');
        const paymentSuccess = document.getElementById('paymentSuccess');
        const paymentActions = document.getElementById('paymentActions');
        const placeOrderBtn = document.getElementById('placeOrderBtn');

        // Switch between payment tabs
        paymentTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                paymentTabs.forEach(t => t.classList.remove('active'));
                paymentTabContents.forEach(c => c.classList.remove('active'));
                
                tab.classList.add('active');
                const tabId = tab.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });

        function openPaymentModal() {
            paymentModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closePaymentModal() {
            paymentModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Mock payment processing
        confirmPaymentBtn.addEventListener('click', function() {
            const activeTab = document.querySelector('.payment-tab.active').getAttribute('data-tab');
            let isValid = true;
            
            if (activeTab === 'paynow') {
                const username = document.getElementById('paynow-username').value;
                const password = document.getElementById('paynow-password').value;
                const mobile = document.getElementById('paynow-mobile').value;
                
                if (username.trim() === '') {
                    isValid = false;
                    alert('Please enter your PayNow username');
                } else if (password.trim() === '') {
                    isValid = false;
                    alert('Please enter your PayNow password');
                } else if (mobile.length !== 10 || !/^\d+$/.test(mobile)) {
                    isValid = false;
                    alert('Please enter a valid mobile number');
                }
            } else if (activeTab === 'mobile-banking') {
                const bank = document.getElementById('bank-name').value;
                const mobile = document.getElementById('mobile-number').value;
                const mpin = document.getElementById('mpin').value;
                
                if (bank === '') {
                    isValid = false;
                    alert('Please select your bank');
                } else if (mobile.length !== 10 || !/^\d+$/.test(mobile)) {
                    isValid = false;
                    alert('Please enter a valid mobile number');
                } else if (mpin.length !== 6 || !/^\d+$/.test(mpin)) {
                    isValid = false;
                    alert('Please enter a valid 6-digit MPIN');
                }
            }
            
            if (!isValid) return;
            
            this.classList.add('btn-loading');
            
            setTimeout(() => {
                paymentTabContents.forEach(c => c.style.display = 'none');
                paymentActions.style.display = 'none';
                paymentSuccess.style.display = 'block';
                
                setTimeout(() => {
                    document.getElementById('orderForm').submit();
                }, 2000);
            }, 2000);
        });

        // Modify form submission to handle online payments
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('payment_method').value;
            
            if (paymentMethod === 'cash_on_delivery') {
                return true;
            }
            
            e.preventDefault();
            placeOrderBtn.classList.add('btn-loading');
            
            setTimeout(() => {
                openPaymentModal();
                placeOrderBtn.classList.remove('btn-loading');
            }, 500);
        });

        paymentModal.addEventListener('click', function(e) {
            if (e.target === paymentModal) {
                closePaymentModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && paymentModal.style.display === 'flex') {
                closePaymentModal();
            }
        });
    </script>
</body>
</html>