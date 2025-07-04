<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit();
}

$thread_id = filter_input(INPUT_GET, 'thread_id', FILTER_VALIDATE_INT) ?? 0;

try {
    $stmt = $conn->prepare("
        SELECT n.*, 
               CASE WHEN n.admin_id IS NOT NULL THEN 'admin' ELSE 'user' END AS sender
        FROM notifications n
        WHERE n.thread_id = ?
          AND n.is_deleted = 0
          AND (n.user_id = ? OR n.admin_id IS NOT NULL)
        ORDER BY n.created_at ASC
    ");
    $stmt->execute([$thread_id, $_SESSION['user_id']]);
    $replies = $stmt->fetchAll();
    
    foreach ($replies as $reply) {
        echo '<div class="message ' . ($reply['sender'] === 'admin' ? 'admin' : 'user') . '">';
        echo '<p>' . nl2br(htmlspecialchars($reply['message'])) . '</p>';
        echo '<div class="message-date">' . date('M j, Y g:i a', strtotime($reply['created_at'])) . '</div>';
        
        // Add delete button for user's own messages or admin's messages
        if (($reply['sender'] === 'user' && $reply['user_id'] == $_SESSION['user_id']) || 
            ($reply['sender'] === 'admin' && $_SESSION['role'] === 'admin')) {
            echo '<form method="POST" class="message-actions">';
            echo '<input type="hidden" name="message_id" value="' . $reply['id'] . '">';
            echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
            echo '<button type="submit" name="delete_message" class="btn btn-delete" 
                    onclick="return confirm(\'Delete this message?\')">Delete</button>';
            echo '</form>';
        }
        
        echo '</div>';
    }
    
    if (empty($replies)) {
        echo '<p>No replies yet</p>';
    }
} catch (PDOException $e) {
    echo '<p>Error loading replies: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>