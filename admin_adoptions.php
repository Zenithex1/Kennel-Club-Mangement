<?php
session_start();
include 'db.php';

// Redirect to login if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Handle marking adoption as completed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adoption_id'])) {
    $adoption_id = $_POST['adoption_id'];
    
    try {
        // Get adoption details before updating
        $stmt = $conn->prepare("SELECT * FROM adoptions WHERE id = ?");
        $stmt->execute([$adoption_id]);
        $adoption = $stmt->fetch();
        
        if ($adoption) {
            // Update status to completed
            $stmt = $conn->prepare("UPDATE adoptions SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->execute([$adoption_id]);
            
            $_SESSION['message'] = "Adoption #$adoption_id marked as completed!";
        } else {
            $_SESSION['message'] = "Adoption not found!";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
    
    header('Location: admin_adoptions.php');
    exit();
}

// Fetch all valid adoptions (excluding cases where user is adopting their own donated dog)
try {
    $stmt = $conn->query("
        SELECT a.*, 
               u.username, 
               d.name AS dog_name,
               d.image AS dog_image,
               d.listed_by
        FROM adoptions a
        JOIN users u ON a.user_id = u.id
        JOIN dogs d ON a.dog_id = d.id
        WHERE a.status != 'completed'
        AND (d.listed_by IS NULL OR d.listed_by != a.user_id)
        ORDER BY a.created_at DESC
    ");
    $adoptions = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Adoptions</title>
    <link rel="stylesheet" href="csss/admin_adoption.css">
  
</head>
<body>
    <header>
        <?php include('admin_header.php'); ?>
    </header>
    <main>
        <h1>Adoption Management</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?= strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success' ?>">
                <?= $_SESSION['message'] ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <h2>Pending Adoptions</h2>
        <div class="adoption-list">
            <?php if (empty($adoptions)): ?>
                <p>No pending adoptions found.</p>
            <?php else: ?>
                <?php foreach ($adoptions as $adoption): ?>
                    <div class="adoption-card">
                        <?php if ($adoption['dog_image']): ?>
                            <img src="<?= htmlspecialchars($adoption['dog_image']) ?>" alt="<?= htmlspecialchars($adoption['dog_name']) ?>">
                        <?php endif; ?>
                        <h3><?= htmlspecialchars($adoption['dog_name']) ?></h3>
                        <p><strong>Adopted by:</strong> <?= htmlspecialchars($adoption['username']) ?></p>
                        <p><strong>Status:</strong> <?= ucfirst($adoption['status']) ?></p>
                        <p><strong>Date:</strong> <?= date('M j, Y', strtotime($adoption['created_at'])) ?></p>
                        
                        <form method="POST" onsubmit="return confirm('Mark this adoption as completed?');">
                            <input type="hidden" name="adoption_id" value="<?= $adoption['id'] ?>">
                            <button type="submit">Mark as Completed</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>