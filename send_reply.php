<?php
session_start();
include 'db.php';

// Only admin can send replies
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inquiry_id = $_POST['inquiry_id'];
    $reply_message = trim($_POST['reply_message']);

    if (!empty($inquiry_id) && !empty($reply_message)) {
        // Save reply (don't delete the inquiry)
        $stmt = $conn->prepare("INSERT INTO inquiry_replies (inquiry_id, sender, message, created_at) VALUES (?, 'admin', ?, NOW())");
        $stmt->execute([$inquiry_id, $reply_message]);
    }

    header('Location: admin_inquiries.php');
    exit();
}
?>
