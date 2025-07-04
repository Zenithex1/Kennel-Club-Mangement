<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dog Adoption Platform</title>
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            font-family: Arial, sans-serif;
        }

        .main-header {
            background-color: #2C3E50;
            color: white;
            padding: 10px 20px;
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 100%;
            padding: 0 20px;
        }

        .logo a {
            font-size: 1.8rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .logo a:hover {
            color: #E74C3C;
            transform: scale(1.1);
        }

        .main-nav {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .main-nav a {
            font-size: 1rem;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 12px;
            background-color: #34495E;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .main-nav a:hover {
            background-color: #3F5C75;
            transform: translateY(-2px);
            color: #E74C3C;
            border-color: #E74C3C;
        }

        .main-nav a.active {
            background-color: #E74C3C;
            color: white;
            border-color: #E74C3C;
        }

        .logout-btn {
            font-size: 1.1rem;
            font-weight: 600;
            background-color: #E74C3C;
            padding: 12px 20px;
            border-radius: 25px;
            color: white;
            text-align: center;
            display: inline-block;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #C0392B;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(192, 57, 43, 0.3);
        }

        .logout-btn:active {
            transform: scale(1.1);
            box-shadow: 0 4px 25px rgba(192, 57, 43, 0.4);
        }

        .logout-btn:disabled {
            background-color: #BDC3C7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .main-header {
                padding: 10px 15px;
            }

            .main-nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding-top: 10px;
            }

            .main-nav a, .logout-btn {
                width: 100%;
                text-align: center;
            }

            .logo a {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="container">
        <div class="logo">
            <a href="admin_dashboard.php">🐶 Kennel Club</a>
        </div>
        <nav class="main-nav">
                        <a href="admin_dashboard.php">Dashboard</a>

            <a href="admin_products.php">Products</a>
            <a href="admin_orders.php">Orders</a>
            <a href="admin_inquiries.php">Message</a>
            <a href="admin_adoptions.php">Adoptions</a>
            <a href="admin_approve_adoptions.php">Review</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="main-content">
