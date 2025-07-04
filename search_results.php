<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the search query from the URL
$search = $_GET['search'] ?? '';

// Fetch dogs that match the searched breed
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM dogs WHERE breed LIKE ?");
    $stmt->execute(["%$search%"]);
} else {
    // If no search query, redirect to the dashboard
    header('Location: user_dashboard.php');
    exit();
}
$dogs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="user_style.css">
</head>
<body>
    <header>
        <h1>Search Results for "<?php echo htmlspecialchars($search); ?>"</h1>
        <nav>
            <a href="index.php">Back to Dashboard</a>
            
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <h2>Dogs Matching Your Search</h2>
        <div class="dog-list">
            <?php if (empty($dogs)): ?>
                <p>No dogs found for the breed "<?php echo htmlspecialchars($search); ?>".</p>
            <?php else: ?>
                <?php foreach ($dogs as $dog): ?>
                    <div class="dog-card">
                        <?php if ($dog['image']): ?>
                            <img src="<?php echo $dog['image']; ?>" alt="<?php echo $dog['name']; ?>">
                        <?php endif; ?>
                        <h3><?php echo $dog['name']; ?></h3>
                        <p><strong>Breed:</strong> <?php echo $dog['breed']; ?></p>
                        <p><strong>Age:</strong> <?php echo $dog['age']; ?> years</p>
                        <p><strong>Description:</strong> <?php echo $dog['description']; ?></p>
                        <a href="checkout.php?dog_id=<?php echo $dog['id']; ?>" class="checkout-button">Adopt Now</a>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>