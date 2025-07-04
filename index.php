<?php
session_start();

// If the user is already logged in, redirect them to their respective dashboard
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit();
    } else {
        header('Location: user_dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PetPals Adoption</title>
    <style>
        /* Basic Reset & Font */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            overflow: hidden; /* Prevents scrollbars from the background */
        }

        /* Hero Section Styling */
        .hero-section {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            
            /* --- IMPORTANT: BACKGROUND IMAGE --- */
            /* This creates a dark overlay so text is readable */
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('assets/images/hero-background.jpg'); /* <-- CHANGE THIS PATH IF NEEDED */
            
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .hero-content {
            max-width: 600px;
            padding: 20px;
            animation: fadeIn 2s ease-in-out;
        }

        h1 {
            font-size: 3.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
        }

        p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            font-weight: 300;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        }

        /* Call-to-Action Buttons */
        .cta-buttons a {
            text-decoration: none;
            color: white;
            padding: 15px 30px;
            margin: 0 10px;
            border-radius: 50px; /* Pill-shaped buttons */
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-login {
            background-color: #3498db; /* A nice blue */
            border: 2px solid #3498db;
        }

        .btn-login:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-register {
            background-color: transparent;
            border: 2px solid #fff;
        }

        .btn-register:hover {
            background-color: #fff;
            color: #333;
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        /* Simple fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.5rem;
            }
            p {
                font-size: 1.1rem;
            }
            .cta-buttons a {
                padding: 12px 25px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <div class="hero-section">
        <div class="hero-content">
            <h1>Find Your New Best Friend</h1>
            <p>Connecting loving homes with pets in need. Join our community to adopt or help a pet find its forever family.</p>
            <div class="cta-buttons">
                <!-- These links assume you have login.php and register.php files -->
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-register">Register</a>
            </div>
        </div>
    </div>

</body>
</html>