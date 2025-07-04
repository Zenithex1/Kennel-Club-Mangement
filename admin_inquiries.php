<?php
session_start();
include 'db.php';

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize deleted inquiries tracker
if (!isset($_SESSION['deleted_inquiries'])) {
    $_SESSION['deleted_inquiries'] = [];
}

// Handle manual deletion (session-based)
if (isset($_POST['delete_inquiry'])) {
    $inquiry_id = $_POST['inquiry_id'];
    $_SESSION['deleted_inquiries'][$inquiry_id] = true;
    $_SESSION['inquiry_deletion_message'] = "Conversation removed from admin view!";
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Build query to exclude "deleted" inquiries
$inquiry_ids_to_exclude = array_keys($_SESSION['deleted_inquiries']);
$placeholders = implode(',', array_fill(0, count($inquiry_ids_to_exclude), '?'));

$sql = "
    SELECT 
        i.*, 
        u.email AS user_email,
        r.message AS reply_message,
        r.created_at AS reply_date
    FROM inquiries i
    JOIN users u ON i.user_id = u.id
    LEFT JOIN inquiry_replies r ON i.id = r.inquiry_id
";

if (!empty($inquiry_ids_to_exclude)) {
    $sql .= " WHERE i.id NOT IN ($placeholders) ";
}

$sql .= " ORDER BY i.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($inquiry_ids_to_exclude)) {
    $stmt->execute($inquiry_ids_to_exclude);
} else {
    $stmt->execute();
}

$inquiries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Inquiries</title>
    <style>
        .inquiry-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .user-message {
            background-color: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .admin-reply {
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .reply-form textarea {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            min-height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        button, input[type="submit"] {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .reply-btn {
            background-color: #4CAF50;
            color: white;
        }
        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        .timestamp {
            font-size: 0.8em;
            color: #666;
            text-align: right;
            margin-top: 5px;
        }
        .admin-message {
            padding: 10px;
            margin: 0 auto 20px;
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
            border-radius: 4px;
            max-width: 800px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <?php include('admin_header.php'); ?>
    </header>

    <h1>Inquiries from Users</h1>

    <main>
        <?php if (isset($_SESSION['inquiry_deletion_message'])): ?>
            <div class="admin-message">
                <?php echo htmlspecialchars($_SESSION['inquiry_deletion_message']); ?>
                <?php unset($_SESSION['inquiry_deletion_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($inquiries)): ?>
            <p>No inquiries found.</p>
        <?php else: ?>
            <?php foreach ($inquiries as $inquiry): ?>
                <div class="inquiry-card">
                    <div class="user-message">
                        <h3>Inquiry about: <?php echo htmlspecialchars($inquiry['breed']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                        <div class="timestamp">
                            From: <?php echo htmlspecialchars($inquiry['user_email']); ?> 
                            at <?php echo date('M j, Y g:i a', strtotime($inquiry['created_at'])); ?>
                        </div>
                    </div>

                    <?php if (!empty($inquiry['reply_message'])): ?>
                        <div class="admin-reply">
                            <h4>Your Reply:</h4>
                            <p><?php echo nl2br(htmlspecialchars($inquiry['reply_message'])); ?></p>
                            <div class="timestamp">
                                Replied at <?php echo date('M j, Y g:i a', strtotime($inquiry['reply_date'])); ?>
                            </div>
                        </div>
                        
                        <form method="POST" action="" class="action-buttons">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                            <button type="submit" name="delete_inquiry" class="delete-btn" 
                                onclick="return confirm('Remove this conversation from your view?')">
                                Remove Conversation
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="send_reply.php" class="reply-form">
                            <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                            <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($inquiry['user_email']); ?>">
                            <textarea name="reply_message" placeholder="Write your reply..." required></textarea>
                            <div class="action-buttons">
                                <input type="submit" value="Send Reply" class="reply-btn">
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>
</html>