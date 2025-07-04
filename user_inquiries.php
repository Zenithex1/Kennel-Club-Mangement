<?php
session_start();

// Strong cache prevention
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

include 'db.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Initialize user's deleted inquiries tracker
if (!isset($_SESSION['user_deleted_inquiries'])) {
    $_SESSION['user_deleted_inquiries'] = [];
}

// Handle user deletion of conversation
if (isset($_POST['delete_inquiry'])) {
    $inquiry_id = $_POST['inquiry_id'];
    $_SESSION['user_deleted_inquiries'][$inquiry_id] = true;
    $_SESSION['user_message'] = "Conversation removed from your view!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch user's inquiries with filtering
$user_id = $_SESSION['user_id'];
$inquiry_ids_to_exclude = array_keys($_SESSION['user_deleted_inquiries']);
$placeholders = implode(',', array_fill(0, count($inquiry_ids_to_exclude), '?'));

$sql = "
    SELECT i.*, r.message AS admin_reply, r.created_at AS reply_date, r.sender
    FROM inquiries i
    LEFT JOIN inquiry_replies r ON i.id = r.inquiry_id
    WHERE i.user_id = ?
";

if (!empty($inquiry_ids_to_exclude)) {
    $sql .= " AND i.id NOT IN ($placeholders) ";
}

$sql .= " ORDER BY i.created_at DESC";

$stmt = $conn->prepare($sql);

// Bind parameters
$params = array_merge([$user_id], $inquiry_ids_to_exclude);
$stmt->execute($params);

$inquiries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Inquiries</title>
    <style>
      
       margin-bottom: 30px;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .message {
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 10px 15px;
            background-color: #e0f7e9;
            border-left: 5px solid #2ecc71;
            color: #2d7a4e;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
        }
        .inquiry-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .inquiry-card h3 {
            font-size: 1.2rem;
            color: #34495e;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .user-message {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #2196F3;
        }
        .admin-reply {
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            border-left: 4px solid #4CAF50;
        }
        .timestamp {
            font-size: 0.8em;
            color: #666;
            margin-top: 10px;
        }
        .no-reply {
            font-style: italic;
            color: #666;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #f1c40f;
        }
        .action-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        p {
            margin: 8px 0;
            color: #555;
        }
        strong {
            color: #333;
        }
    </style>
</head>
<body>
    <header>
       <?php include('header.php') ?>
    </header>

    <main>
        <div class="header-container"><br>

            <h1 align = "center" >Your Inquiries</h1>
        </div>

        <?php if (isset($_SESSION['user_message'])): ?>
            <div class="message"><?php echo htmlspecialchars($_SESSION['user_message']); ?></div>
            <?php unset($_SESSION['user_message']); ?>
        <?php endif; ?>

        <?php if (empty($inquiries)): ?>
            <p style="text-align: center;">You have not made any inquiries yet.</p>
        <?php else: ?>
            <?php foreach ($inquiries as $inquiry): ?>
                <div class="inquiry-card">
                    <div class="user-message">
                        <h3>Inquiry about: <?php echo htmlspecialchars($inquiry['breed']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                        <div class="timestamp">
                            <strong>Sent:</strong> <?php echo date('M j, Y g:i a', strtotime($inquiry['created_at'])); ?>
                        </div>
                    </div>

                    <?php if ($inquiry['sender'] === 'admin' && $inquiry['admin_reply']): ?>
                        <div class="admin-reply">
                            <h4>Admin Reply:</h4>
                            <p><?php echo nl2br(htmlspecialchars($inquiry['admin_reply'])); ?></p>
                            <div class="timestamp">
                                <strong>Replied:</strong> <?php echo date('M j, Y g:i a', strtotime($inquiry['reply_date'])); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-reply">
                            <p>Admin has not replied yet.</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="action-buttons">
                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                        <button type="submit" name="delete_inquiry" class="delete-btn" 
                            onclick="return confirm('Are you sure you want to remove this conversation from your view?')">
                            Remove Conversation
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>