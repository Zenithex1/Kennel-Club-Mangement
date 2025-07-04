<?php
session_start();
include 'db.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle inquiry deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inquiry_id = $_POST['inquiry_id'];

    // Delete the inquiry
    $stmt = $conn->prepare("DELETE FROM inquiries WHERE id = ? AND user_id = ?");
    $stmt->execute([$inquiry_id, $_SESSION['user_id']]);

    $_SESSION['message'] = 'Inquiry deleted successfully!';
    header('Location: user_notifications.php');
    exit();
}
?>