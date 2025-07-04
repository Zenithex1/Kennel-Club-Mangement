<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ===================================================================
// START OF MODIFICATIONS (SIMPLIFIED DELETE/CANCEL LOGIC)
// ===================================================================

// Handle REMOVING a donation request (works for 'pending' and 'rejected')
if (isset($_GET['remove_request_id'])) {
    $remove_id = $_GET['remove_request_id'];
    try {
        // We only allow removing requests that are 'pending' or 'rejected'.
        // This prevents users from deleting a request after it has been approved and listed.
        $stmt = $conn->prepare("DELETE FROM adoption_requests WHERE id = ? AND user_id = ? AND (status = 'pending' OR status = 'rejected')");
        
        if ($stmt->execute([$remove_id, $user_id])) {
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "The donation request has been removed successfully.";
            } else {
                $_SESSION['message'] = "Could not remove this request. It might have already been approved or does not exist.";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Database Error: " . $e->getMessage();
    }
    header('Location: user_adoption.php');
    exit();
}
// ===================================================================
// END OF MODIFICATIONS
// ===================================================================


// Handle delete or archive an adoption APPLICATION
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        // This part remains unchanged, for managing adoption APPLICATIONS, not requests.
        $conn->beginTransaction();
        $stmt = $conn->prepare("SELECT * FROM adoptions WHERE id = ? AND user_id = ?");
        $stmt->execute([$delete_id, $user_id]);
        $adoption = $stmt->fetch();

        if ($adoption) {
            if ($adoption['status'] === 'completed') {
                $stmt = $conn->prepare("UPDATE adoptions SET archived = 1 WHERE id = ?");
                $stmt->execute([$delete_id]);
                $_SESSION['message'] = "Adoption record archived successfully!";
            } else {
                $stmt = $conn->prepare("DELETE FROM adoptions WHERE id = ?");
                $stmt->execute([$delete_id]);
                $_SESSION['message'] = "Adoption application deleted successfully!";
            }
        } else {
            $_SESSION['message'] = "Adoption application not found.";
        }
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['message'] = "Error: " . $e->getMessage();
    }
    header('Location: user_adoption.php');
    exit();
}

// === Fetch user's ADOPTION APPLICATIONS ===
$stmt = $conn->prepare("
    SELECT a.*, COALESCE(d.name, 'Deleted Dog') AS dog_name, COALESCE(d.breed, 'Unknown') AS breed, COALESCE(d.age, 'Unknown') AS age, d.image
    FROM adoptions a LEFT JOIN dogs d ON a.dog_id = d.id
    WHERE a.user_id = ? AND a.archived = 0 ORDER BY a.status = 'completed' DESC, a.created_at DESC
");
$stmt->execute([$user_id]);
$adoptions = $stmt->fetchAll();

// === Fetch user's DONATION REQUESTS ===
$stmt_donations = $conn->prepare("SELECT * FROM adoption_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt_donations->execute([$user_id]);
$donation_requests = $stmt_donations->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>My Adoptions & Donations</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; color: #333; line-height: 1.6; }
        main { max-width: 900px; margin: 20px auto; padding: 0 15px; }
        h2 { text-align: center; color: #444; margin-top: 40px; margin-bottom: 20px; }
        .adoption-list { display: flex; flex-direction: column; gap: 15px; }
        .adoption-card {
            background: #fff; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08); display: flex;
            gap: 20px; align-items: flex-start; border-left: 6px solid #ccc;
        }
        
        /* =========================================== */
        /* START OF CSS IMAGE FIX                      */
        /* =========================================== */
        .dog-image { 
            width: 120px; 
            height: 120px; 
            object-fit: contain; /* This shows the full image */
            background-color: #f7f7f7; /* Adds a background for any empty space */
            border: 1px solid #eee;
            border-radius: 5px; 
            flex-shrink: 0; 
        }
        /* =========================================== */
        /* END OF CSS IMAGE FIX                        */
        /* =========================================== */

        .card-content { flex-grow: 1; }
        .card-content h3 { margin-top: 0; }
        .card-content p { margin: 4px 0; font-size: 0.95rem; }
        .status { font-weight: bold; text-transform: capitalize; }
        .status.completed, .adoption-card.completed, .status.approved, .adoption-card.approved { border-left-color: #4CAF50; color: #4CAF50; }
        .status.pending, .adoption-card.pending { border-left-color: #ff9800; }
        .status.rejected, .adoption-card.rejected { border-left-color: #f44336; }
        .status.deleted-dog { border-left-color: #607d8b; }
        
        .action-btn {
            display: inline-block; margin-top: 10px; padding: 6px 12px;
            color: white; text-decoration: none; border-radius: 4px;
            font-size: 0.9em; border: none; cursor: pointer;
        }
        .delete-btn { background: #f44336; }
        .delete-btn:hover { background: #d32f2f; }
        .archive-btn { background: #2196F3; }
        .archive-btn:hover { background: #1976D2; }

        .message { padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .success { background: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; }
        .error { background: #f2dede; color: #a94442; border: 1px solid #ebccd1;}
        .no-records { text-align: center; color: #777; padding: 20px; background: #fff; border-radius: 8px; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?= strpos(strtolower($_SESSION['message']), 'error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- SECTION: MY DONATION REQUESTS -->
        <h2>My Donation Requests</h2>
        <div class="adoption-list">
            <?php if (empty($donation_requests)): ?>
                <p class="no-records">You have not submitted any dogs for adoption yet.</p>
            <?php else: ?>
                <?php foreach ($donation_requests as $request): ?>
                    <div class="adoption-card <?= htmlspecialchars($request['status']) ?>">
                        <?php if (!empty($request['dog_image'])): ?>
                            <img src="<?= htmlspecialchars($request['dog_image']) ?>" alt="Dog Image" class="dog-image" />
                        <?php endif; ?>
                        <div class="card-content">
                            <h3><?= htmlspecialchars($request['dog_name']) ?></h3>
                            <p><strong>Breed:</strong> <?= htmlspecialchars($request['breed']) ?></p>
                            <p><strong>Status:</strong> <span class="status <?= htmlspecialchars($request['status']) ?>"><?= ucfirst(htmlspecialchars($request['status'])) ?></span></p>
                            <p><strong>Submitted on:</strong> <?= date('M j, Y', strtotime($request['created_at'])) ?></p>
                            
                            <!-- ========================================================== -->
                            <!-- START OF HTML BUTTON LOGIC MODIFICATION                    -->
                            <!-- ========================================================== -->
                            <?php if ($request['status'] === 'pending' || $request['status'] === 'rejected'): ?>
                                <a href="?remove_request_id=<?= $request['id'] ?>" class="action-btn delete-btn" 
                                   onclick="return confirm('Are you sure you want to remove this request? This cannot be undone.')">
                                    Remove Record
                                </a>
                            <?php endif; ?>
                            <!-- Note: No action is available for 'approved' status to protect data. -->
                            <!-- ========================================================== -->
                            <!-- END OF HTML BUTTON LOGIC MODIFICATION                      -->
                            <!-- ========================================================== -->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- SECTION: MY ADOPTION APPLICATIONS -->
        <h2>My Adoption Applications</h2>
        <div class="adoption-list">
            <?php if (empty($adoptions)): ?>
                <p class="no-records">You have not made any adoption applications yet.</p>
            <?php else: ?>
                <?php foreach ($adoptions as $adoption): ?>
                    <div class="adoption-card 
                        <?= $adoption['status'] === 'completed' ? 'completed' : '' ?> 
                        <?= $adoption['dog_name'] === 'Deleted Dog' ? 'deleted-dog' : '' ?>">
                        <?php if (!empty($adoption['image'])): ?>
                            <img src="<?= htmlspecialchars($adoption['image']) ?>" alt="Dog Image" class="dog-image" />
                        <?php endif; ?>
                        <div class="card-content">
                            <h3><?= htmlspecialchars($adoption['dog_name']) ?></h3>
                            <p><strong>Breed:</strong> <?= htmlspecialchars($adoption['breed']) ?></p>
                            <p><strong>Status:</strong> <span class="status <?= htmlspecialchars($adoption['status']) ?>"><?= ucfirst(htmlspecialchars($adoption['status'])) ?></span></p>
                            <p><strong>Requested on:</strong> <?= date('M j, Y', strtotime($adoption['created_at'])) ?></p>

                            <?php if (!empty($adoption['completed_at'])): ?>
                                <p><strong>Completed on:</strong> <?= date('M j, Y', strtotime($adoption['completed_at'])) ?></p>
                            <?php endif; ?>
                            <?php if ($adoption['status'] !== 'completed'): ?>
                                <a href="?delete_id=<?= $adoption['id'] ?>" class="action-btn delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete this adoption application?')">
                                    Delete Application
                                </a>
                            <?php else: ?>
                                <a href="?delete_id=<?= $adoption['id'] ?>" class="action-btn archive-btn" 
                                   onclick="return confirm('Are you sure you want to archive this completed adoption?')">
                                    Archive Record
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>