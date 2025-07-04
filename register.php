<?php
session_start();
include 'db.php';

function validatePassword($password) {
    // Keep your original 8-character requirement with letters, numbers, and symbols
    if (strlen($password) < 8) {
        return "Your password must be at least 8 characters long and include at least one letter, one number, and one special character.";
    }

    if (!preg_match('/[A-Za-z]/', $password)) {
        return "Your password must be at least 8 characters long and include at least one letter, one number, and one special character.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        return "Your password must be at least 8 characters long and include at least one letter, one number, and one special character.";
    }

    if (!preg_match('/[!@#$%^&*]/', $password)) {
        return "Your password must be at least 8 characters long and include at least one letter, one number, and one special character.";
    }

    return true;
}

function validateEmail($email) {
    // Check if email has valid format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Please enter a valid email address (e.g., example@gmail.com)";
    }
    
    // Check if domain is gmail.com (you can add more domains if needed)
    $domain = explode('@', $email)[1] ?? '';
    if (strtolower($domain) !== 'gmail.com') {
        return "Currently, we only accept Gmail addresses (example@gmail.com)";
    }
    
    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email
    $emailValidation = validateEmail($email);
    if ($emailValidation !== true) {
        $_SESSION['message'] = $emailValidation;
        header('Location: register.php');
        exit();
    }

    // Validate password (keeping your original requirements)
    $passwordValidation = validatePassword($password);
    if ($passwordValidation !== true) {
        $_SESSION['message'] = $passwordValidation;
        header('Location: register.php');
        exit();
    }

    // Hash with Argon2id (works with existing password column)
    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

    // Check if email exists (original query)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['message'] = "Email already registered";
        header('Location: register.php');
        exit();
    }

    // Insert user (original table structure)
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        
        // Determine role (original logic)
        $admin_email = 'jnishxrestha@gmail.com';
        $role = ($email == $admin_email) ? 'admin' : 'user';
        
        $stmt->execute([$username, $email, $hashedPassword, $role]);
        
        $_SESSION['message'] = 'Registration successful! Please login.';
        header('Location: login.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = "Registration failed. Please try again.";
        header('Location: register.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
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

    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    input[type="text"],
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

    input[type="text"]:focus,
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

    @media (max-width: 768px) {
        .container {
            padding: 30px;
        }

        header h1 {
            font-size: 28px;
        }

        input[type="text"],
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
            <h1>Register</h1>
        </header>
        <main>
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="error-message">' . htmlspecialchars($_SESSION['message']) . '</div>';
                unset($_SESSION['message']);
            }
            ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required />
                <input type="email" name="email" placeholder="example@gmail.com" required />
                <input type="password" name="password" placeholder="Password" required />
                <input type="submit" value="Register" />
            </form>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </main>
    </div>
</body>
</html>