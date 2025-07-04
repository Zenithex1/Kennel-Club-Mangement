<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get the breed, dog_id, and dog_name from the query string
$breed = $_GET['breed'] ?? '';
$dog_id = $_GET['dog_id'] ?? '';
$dog_name = $_GET['dog_name'] ?? '';

// If the breed is not specified, redirect to the breed list
if (empty($breed)) {
    header('Location: available_breeds.php');
    exit();
}

// Fetch dog name if dog_id is provided but dog_name isn't
if (!empty($dog_id) && empty($dog_name)) {
    $stmt = $conn->prepare("SELECT name FROM dogs WHERE id = ?");
    $stmt->execute([$dog_id]);
    $dog = $stmt->fetch();
    if ($dog) {
        $dog_name = $dog['name'];
    }
}

// Process the inquiry if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $inquiry_message = $_POST['message'];

    // Insert the inquiry into the database (without dog_id)
    $stmt = $conn->prepare("INSERT INTO inquiries (user_id, breed, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $breed, $inquiry_message]);

    // Send the inquiry to the admin
    $admin_email = "admin@example.com";
    $subject = "Inquiry about dog: $dog_name ($breed)";
    $message = "User ID: $user_id\nDog: $dog_name ($breed)\nDog ID: $dog_id\nMessage: $inquiry_message";
    $headers = "From: no-reply@example.com";

    mail($admin_email, $subject, $message, $headers);

    header('Location: available_breeds.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquire About <?= htmlspecialchars($dog_name) ?></title>
    <link rel="stylesheet" href="css/send_inquiry.css">
</head>
<body>
<?php include 'header.php'; ?>

    <header>
        <h1>Inquire About <?= htmlspecialchars($dog_name) ?></h1>
    </header>
    <main>
        <h2>Send Your Inquiry</h2>
        
        <form action="send_inquiry.php?breed=<?= urlencode($breed) ?>&dog_id=<?= $dog_id ?>&dog_name=<?= urlencode($dog_name) ?>" method="POST">
            <label for="message">Your Message:</label>
            <textarea name="message" id="message" rows="4" required><?= 
                htmlspecialchars("I'm interested in adopting $dog_name.\n\n")
            ?></textarea>
            <button type="submit">Send Inquiry</button>
        </form>
    </main>
</body>
</html>