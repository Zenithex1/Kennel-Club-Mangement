<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle adoption form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dog_id'])) {
    $dog_id = $_POST['dog_id'];
    
    try {
        $conn->beginTransaction();
        
        // Check if dog is already adopted or has pending requests
        $stmt = $conn->prepare("SELECT * FROM adoptions WHERE dog_id = ? AND status IN ('pending', 'completed') FOR UPDATE");
        $stmt->execute([$dog_id]);
        $existingAdoptions = $stmt->fetchAll();
        
        if (count($existingAdoptions) > 0) {
            $_SESSION['adoption_message'] = "This dog already has an adoption request in progress.";
            $conn->rollBack();
        } else {
            // Check if user already has pending/completed adoption for this dog
            $stmt = $conn->prepare("SELECT * FROM adoptions WHERE user_id = ? AND dog_id = ? AND status IN ('pending', 'completed')");
            $stmt->execute([$user_id, $dog_id]);
            $userExistingAdoption = $stmt->fetch();
            
            if ($userExistingAdoption) {
                $_SESSION['adoption_message'] = "You have already requested to adopt this dog.";
                $conn->rollBack();
            } else {
                // Insert adoption request
                $stmt = $conn->prepare("INSERT INTO adoptions (user_id, dog_id, status, created_at) VALUES (?, ?, 'pending', NOW())");
                $stmt->execute([$user_id, $dog_id]);
                
                // Mark dog as pending adoption
                $stmt = $conn->prepare("UPDATE dogs SET adoption_status = 'pending' WHERE id = ?");
                $stmt->execute([$dog_id]);
                
                $conn->commit();
                $_SESSION['adoption_message'] = "Adoption request submitted successfully!";
            }
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['adoption_message'] = "Error processing your request. Please try again.";
    }
    
    header('Location: user_dashboard.php');
    exit();
}

// Get selected letter filter
$letter = $_GET['letter'] ?? 'all';

// Fetch available dogs (only those not pending or completed)
if ($letter === 'all') {
    $stmt = $conn->prepare("SELECT * FROM dogs 
                           WHERE id NOT IN (
                               SELECT dog_id FROM adoptions WHERE status IN ('pending', 'completed')
                           )
                           AND (listed_by IS NULL OR listed_by != ?)
                           ORDER BY breed, name");
    $stmt->execute([$user_id]);
} else {
    $stmt = $conn->prepare("SELECT * FROM dogs 
                           WHERE breed LIKE ? 
                           AND id NOT IN (
                               SELECT dog_id FROM adoptions WHERE status IN ('pending', 'completed')
                           )
                           AND (listed_by IS NULL OR listed_by != ?)
                           ORDER BY breed, name");
    $stmt->execute(["$letter%", $user_id]);
}
$dogs = $stmt->fetchAll();

// Sort dogs by age ascending
usort($dogs, function($a, $b) {
    return $a['age'] <=> $b['age'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Available Dogs</title>
    <link rel="stylesheet" href="css/user_dashboard.css" />
    <script>
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>
</head>
<body>
    <header>
        <?php include 'header.php'; ?>
    </header>

    <main>
        <h2>Browse Dogs by Breed</h2>

        <?php if (isset($_SESSION['adoption_message'])): ?>
            <div class="message">
                <?= htmlspecialchars($_SESSION['adoption_message']) ?>
            </div>
            <?php unset($_SESSION['adoption_message']); ?>
        <?php endif; ?>

        <div class="alphabet-filter">
            <?php foreach (range('A', 'Z') as $char): ?>
                <a href="?letter=<?= $char ?>" class="<?= ($letter === $char) ? 'active' : '' ?>"><?= $char ?></a>
            <?php endforeach; ?>
            <a href="?letter=all" class="<?= ($letter === 'all') ? 'active' : '' ?>">All</a>
        </div>

        <div class="dog-list">
            <?php if (empty($dogs)): ?>
                <p>No dogs available at this time.</p>
            <?php else: ?>
                <?php foreach ($dogs as $dog): ?>
                    <div class="dog-card">
                        <?php 
                        $imagePath = !empty($dog['image']) ? $dog['image'] : 'assets/images/default_dog.jpg';
                        if (file_exists($imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($dog['name']) ?>" />
                        <?php else: ?>
                            <img src="assets/images/default_dog.jpg" alt="Image not available" />
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($dog['name']) ?></h3>
                        <p><strong>Breed:</strong> <?= htmlspecialchars($dog['breed']) ?></p>
                        <p><strong>Age:</strong> <?= htmlspecialchars($dog['age']) ?> years</p>

                        <form method="POST" action="">
                            <input type="hidden" name="dog_id" value="<?= $dog['id'] ?>" />
                            <button type="submit" class="checkout-button">
                                Adopt Now
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>