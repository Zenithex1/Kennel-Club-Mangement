<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['adoption_id'];
    $action = $_POST['action'];

    try {
        // Define status values
        $status_approved = 'approved';
        $status_rejected = 'rejected';
        $status_pending = 'pending';

        if ($action === 'approve') {
            $stmt = $conn->prepare("SELECT * FROM adoption_requests WHERE id = ?");
            $stmt->execute([$id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                throw new Exception("Adoption request not found.");
            }

            $dogId = $request['dog_id'];

            $dogExists = false;
            if ($dogId) {
                $stmt = $conn->prepare("SELECT id FROM dogs WHERE id = ?");
                $stmt->execute([$dogId]);
                $dogExists = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$dogExists) {
                $stmt = $conn->prepare("
                    INSERT INTO dogs (name, breed, age, description, image, listed_by) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                // This line is updated to prevent NULL user IDs
                $stmt->execute([
                    $request['dog_name'] ?: 'Unknown',
                    $request['breed'] ?: 'Unknown',
                    $request['age'] ?: null,
                    $request['description'] ?: '',
                    $request['dog_image'] ?: null,
                    $request['user_id'] ?: $_SESSION['user_id']  // FIX: Fallback to admin ID if user_id is missing
                ]);

                $dogId = $conn->lastInsertId();
            }
            
            if ($request['dog_id']) {
                // This is an adoption request for an existing dog
                $stmt = $conn->prepare("
                    INSERT INTO adoptions (user_id, dog_id, status, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$request['user_id'], $dogId, $status_approved]);
            }

            $stmt = $conn->prepare("
                UPDATE adoption_requests 
                SET status = ?, dog_id = ? 
                WHERE id = ?
            ");
            $stmt->execute([$status_approved, $dogId, $id]);

            $_SESSION['message'] = "Request approved successfully!";
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE adoption_requests SET status = ? WHERE id = ?");
            $stmt->execute([$status_rejected, $id]);
            $_SESSION['message'] = "Adoption request rejected.";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }

    header("Location: admin_approve_adoptions.php");
    exit();
}

// Fetch only pending requests
$status_pending = 'pending';
$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM adoption_requests r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.status = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$status_pending]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Approve Adoptions</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style>


/* Page Title */
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
}

/* Message Alert */
.message {
    max-width: 800px;
    margin: 0 auto 20px auto;
    padding: 10px 15px;
    background-color: #e0f7e9;
    border-left: 5px solid #2ecc71;
    color: #2d7a4e;
    border-radius: 4px;
    font-weight: bold;
}

/* Container */
.container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

/* Card */
.card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 320px;
    position: relative;
    transition: transform 0.2s ease;
}

.card:hover {
    transform: scale(1.02);
}

/* Card Header */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.card-header h3 {
    font-size: 1.2rem;
    color: #34495e;
}

.status.pending {
    background-color: #f1c40f;
    color: #fff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: bold;
}

/* Dog Image */
.dog-image {
    width: 100%;
    height: 200px;
    object-fit: contain;   /* Changed from cover to contain */
    background-color: #f0f0f0; /* optional: show a subtle background if image doesn't fill */
    margin-bottom: 15px;
    border-radius: 6px;
}


/* Paragraph Info */
.card p {
    margin: 5px 0;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

.btn {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: background-color 0.3s ease;
}

.btn-approve {
    background-color: #27ae60;
    color: white;
}

.btn-approve:hover {
    background-color: #1e8449;
}

.btn-reject {
    background-color: #e74c3c;
    color: white;
}

.btn-reject:hover {
    background-color: #c0392b;
}

/* No Requests */
.no-requests {
    text-align: center;
    color: #888;
    font-size: 1.1rem;
    margin-top: 50px;
}

    </style>
</head>
<body>
    <header>
        <?php include('admin_header.php'); ?>
    </header>
    <br>
    <h2>Pending Adoption Requests</h2>

    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if ($requests): ?>
            <?php foreach ($requests as $request): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?= htmlspecialchars($request['dog_name']) ?></h3>
                        <span class="status pending">Pending</span>
                    </div>
                    
                    <?php if ($request['dog_image']): ?>
                        <img src="<?= htmlspecialchars($request['dog_image']) ?>" class="dog-image" alt="Dog image">
                    <?php endif; ?>
                    
                    <p><strong>Breed:</strong> <?= htmlspecialchars($request['breed']) ?></p>
                    <p><strong>Age:</strong> <?= htmlspecialchars($request['age']) ?> years</p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($request['description']) ?></p>
                    <p><strong>Submitted by:</strong> <?= htmlspecialchars($request['username']) ?></p>
                    <p><strong>Date:</strong> <?= date('M j, Y g:i a', strtotime($request['created_at'])) ?></p>
                    
                    <form method="POST" class="action-buttons">
                        <input type="hidden" name="adoption_id" value="<?= htmlspecialchars($request['id']) ?>">
                        <button type="submit" name="action" value="approve" class="btn btn-approve">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-reject">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-requests">No pending adoption requests at this time.</p>
        <?php endif; ?>
    </div>
</body>
</html>