<?php
session_start();
include 'db.php';

// Redirect to login if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new product
    if (isset($_POST['submit'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $category = $_POST['category'];
        $quantity = $_POST['quantity'];
        $listed_by = $_SESSION['user_id'];

        // Handle image upload
        $target_dir = "assets/images/products/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is valid
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['message'] = "File is not an image.";
            header('Location: admin_products.php');
            exit();
        }

        // Check file size (max 5MB)
        if ($_FILES["image"]["size"] > 5000000) {
            $_SESSION['message'] = "Sorry, your file is too large (max 5MB).";
            header('Location: admin_products.php');
            exit();
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header('Location: admin_products.php');
            exit();
        }

        // Generate unique filename
        $new_filename = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            try {
                $stmt = $conn->prepare("INSERT INTO products 
                                      (name, description, price, image, category, quantity, listed_by) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $target_file, $category, $quantity, $listed_by]);
                
                $_SESSION['message'] = 'Product added successfully!';
            } catch (PDOException $e) {
                $_SESSION['message'] = "Database error: " . $e->getMessage();
            }
        } else {
            $_SESSION['message'] = "Sorry, there was an error uploading your file.";
        }
    }
    // Update product quantity
    elseif (isset($_POST['update_quantity'])) {
        $product_id = $_POST['product_id'];
        $new_quantity = $_POST['new_quantity'];
        
        try {
            $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
            $stmt->execute([$new_quantity, $product_id]);
            
            $_SESSION['message'] = 'Product quantity updated successfully!';
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error updating quantity: " . $e->getMessage();
        }
    }
    // Delete product
    elseif (isset($_POST['delete'])) {
        $product_id = $_POST['product_id'];

        try {
            // Delete the product from the database
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);

            // Check if an image exists for the product and delete it
            $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($product && file_exists($product['image'])) {
                unlink($product['image']);  // Delete the image file
            }

            $_SESSION['message'] = 'Product deleted successfully!';
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error deleting product: " . $e->getMessage();
        }
    }
    
    header('Location: admin_products.php');
    exit();
}

// Fetch all products
try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY name ASC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products</title>
    <link rel="stylesheet" href="csss/admin_products.css">
    <style>
        /* Additional styles for quantity management */
     
        .quantity-form {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .quantity-form input {
            width: 60px;
            padding: 5px;
            margin-right: 5px;
        }
        .btn-update {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-update:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <header>
        <?php include('admin_header.php'); ?>
    </header>
    
    <main>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?= strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success' ?>">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <h2>Add New Product</h2>
        <form method="POST" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price (Rs):</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Food">Food</option>
                    <option value="Toy">Toy</option>
                    <option value="Accessory">Accessory</option>
                    <option value="Grooming">Grooming </option>
                    
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image:</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Max file size: 5MB (JPG, JPEG, PNG, GIF)</small>
            </div>
            
            <button type="submit" name="submit" class="btn-submit">Add Product</button>
        </form>

        <h2>Product Inventory</h2>
        <div class="product-list">
            <?php if (empty($products)): ?>
                <p>No products found.</p>
            <?php else: ?>
                <?php foreach ($products as $product): 
                    // Determine stock status
                    $stock_class = '';
                    $stock_percentage = 0;
                    if ($product['quantity'] > 10) {
                        $stock_class = 'in-stock';
                        $stock_percentage = 100;
                    } elseif ($product['quantity'] > 0) {
                        $stock_class = 'low-stock';
                        $stock_percentage = ($product['quantity'] / 10) * 100;
                    } else {
                        $stock_class = 'out-of-stock';
                    }
                ?>
                    <div class="product-card">
                        <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php else: ?>
                            <img src="assets/images/default_product.jpg" alt="No image available">
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p><strong>Description:</strong> <?= htmlspecialchars($product['description']) ?></p>
                        <p><strong>Price:</strong> Rs<?= number_format($product['price'], 2) ?></p>
                        
                        <div class="stock-info">
                            <strong>Stock:</strong> <?= htmlspecialchars($product['quantity']) ?>
                            <div class="stock-level">
                                <div class="stock-fill <?= $stock_class ?>" style="width: <?= $stock_percentage ?>%"></div>
                            </div>
                        </div>
                        
                        <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>

                        <!-- Quantity Update Form -->
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <input type="number" name="new_quantity" min="0" value="<?= $product['quantity'] ?>" required>
                            <button type="submit" name="update_quantity" class="btn-update">Update</button>
                        </form>

                        <!-- Delete Button Form -->
                        <form action="admin_products.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="delete" class="btn-delete">Delete Product</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
    
    <script>
        // Client-side validation for quantity updates
        document.querySelectorAll('.quantity-form input[type="number"]').forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 0) {
                    this.value = 0;
                    alert('Quantity cannot be negative');
                }
            });
        });
    </script>
</body>
</html>