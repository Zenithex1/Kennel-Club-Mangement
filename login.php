<?php
session_start();
include 'db.php';

// Basic rate limiting (5 attempts per IP)
$ip = $_SERVER['REMOTE_ADDR'];
$key = 'login_attempts_' . $ip;
$attempts = $_SESSION[$key] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($attempts >= 5) {
        $_SESSION['message'] = "Too many failed attempts. Please <a href='register.php'>create a new account</a>.";
        header('Location: login.php');
        exit();
    }

    // Original query
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password rehashing for security upgrades
        if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
            $newHash = password_hash($password, PASSWORD_ARGON2ID);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$newHash, $user['id']]);
        }

        // Original session setup
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // Reset attempt counter
        unset($_SESSION[$key]);
        
        // Original redirect logic
        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: user_dashboard.php');
        }
        exit();
    } else {
        $_SESSION[$key] = $attempts + 1;
        $remaining_attempts = 5 - $_SESSION[$key];
        
        if ($remaining_attempts <= 0) {
            $_SESSION['message'] = "Too many failed attempts. Please <a href='register.php'>create a new account</a>.";
        } else {
            $_SESSION['message'] = 'Invalid email or password. Attempts remaining: ' . $remaining_attempts;
        }
        
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
    /* Your existing CSS remains exactly the same */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Helvetica Neue', Arial, sans-serif;
        background: linear-gradient(135deg, rgba(6, 95, 70, 0.9), rgba(0, 72, 90, 0.8));
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
    }

    .container {
        background: rgba(255, 255, 255, 0.95);
        padding: 40px 50px;
        width: 100%;
        max-width: 450px;
        border-radius: 15px;
        box-shadow: 0 15px 45px rgba(0, 0, 0, 0.1);
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 20px;
        backdrop-filter: blur(10px);
    }

    header h1 {
        font-size: 36px;
        font-weight: 700;
        color: #065f46;
        letter-spacing: 1px;
        margin-bottom: 20px;
        text-transform: uppercase;
        background: linear-gradient(45deg, #2d3748, #065f46);
        background-clip: text;
        -webkit-background-clip: text;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 8px;
        font-weight: bold;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .error-message a {
        color: #065f46;
        text-decoration: underline;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    input[type="email"],
    input[type="password"] {
        padding: 15px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #f9fafb;
        transition: all 0.4s ease;
        outline: none;
    }

    input[type="email"]:focus,
    input[type="password"]:focus {
        border-color: #065f46;
        box-shadow: 0 0 10px rgba(6, 95, 70, 0.3);
    }

    input[type="submit"] {
        padding: 15px;
        font-size: 16px;
        background-color: #065f46;
        color: #fff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    input[type="submit"]:hover {
        background-color: #047857;
        transform: translateY(-4px);
    }

    input[type="submit"]:active {
        transform: translateY(2px);
    }

    p {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        text-transform: uppercase;
        margin-top: 20px;
    }

    a {
        color: #065f46;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    a:hover {
        color: #047857;
        text-decoration: underline;
    }

    .reset-password {
        display: block;
        margin-top: -10px;
        margin-bottom: 10px;
        font-size: 13px;
        text-align: right;
    }

    @media (max-width: 768px) {
        .container {
            padding: 30px;
        }

        header h1 {
            font-size: 28px;
        }

        input[type="email"],
        input[type="password"] {
            font-size: 14px;
            padding: 12px;
        }

        input[type="submit"] {
            font-size: 14px;
            padding: 12px;
        }

        p {
            font-size: 13px;
        }
    }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Login</h1>
        </header>
        <main>
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="error-message">' . $_SESSION['message'] . '</div>';
                unset($_SESSION['message']);
            }
            
            // Only show the login form if attempts < 5
            if (!isset($_SESSION['login_attempts_' . $_SERVER['REMOTE_ADDR']]) || $_SESSION['login_attempts_' . $_SERVER['REMOTE_ADDR']] < 5): 
            ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>
            <?php endif; ?>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </main>
    </div>
</body>
</html>