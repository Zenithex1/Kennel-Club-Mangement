<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Dog breeds array for the dropdown
$dogBreeds = [
    "Labrador Retriever", "German Shepherd", "Golden Retriever", "Bulldog",
    "Beagle", "Poodle", "Rottweiler", "Yorkshire Terrier", "Boxer",
    "Dachshund", "Siberian Husky", "Doberman Pinscher", "Shih Tzu",
    "Great Dane", "Chihuahua", "Border Collie", "Australian Shepherd",
    "Cocker Spaniel", "Pug", "Saint Bernard", "Akita", "Maltese", "Vizsla",
    "Newfoundland", "Bichon Frise", "Cane Corso", "Boston Terrier", "Weimaraner",
    "Bernese Mountain Dog", "English Springer Spaniel", "Basset Hound", "Collie", "Other"
];

$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dog_name = trim($_POST['dog_name']);
    $breed = trim($_POST['breed']);
    $age = intval($_POST['age']);
    $description = trim($_POST['description']);
    $dog_image = null;

    // --- Server-side validation ---
    if (empty($dog_name) || empty($breed) || empty($description)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($age < 0 || $age > 20) {
        $error_message = "Age must be between 0 and 20.";
    }

    if ($error_message === '') {
        if (isset($_FILES['dog_image']) && $_FILES['dog_image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $filename = uniqid('dog_', true) . '_' . basename($_FILES["dog_image"]["name"]);
            $target_file = $target_dir . $filename;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                $error_message = "Sorry, only JPG, JPEG, & PNG files are allowed.";
            } else {
                if (move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)) {
                    $dog_image = $target_file;
                } else {
                    $error_message = "Error uploading the image.";
                }
            }
        }
    }

    if ($error_message === '') {
        try {
            // ==========================================================
            // START OF THE FIX (NO DATABASE CHANGE REQUIRED)
            // ==========================================================
            // We insert a placeholder '0' for dog_id to satisfy the NOT NULL constraint.
            // The admin approval page will handle this '0' correctly.

            $stmt = $conn->prepare("INSERT INTO adoption_requests 
                                  (dog_id, user_id, dog_name, dog_image, breed, age, description, status, created_at) 
                                  VALUES 
                                  (:dog_id, :user_id, :dog_name, :dog_image, :breed, :age, :description, 'pending', NOW())");
            
            $placeholder_dog_id = 0; // Use 0 as the placeholder for a new donation

            $stmt->bindParam(':dog_id', $placeholder_dog_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':dog_name', $dog_name, PDO::PARAM_STR);
            $stmt->bindParam(':dog_image', $dog_image, PDO::PARAM_STR);
            $stmt->bindParam(':breed', $breed, PDO::PARAM_STR);
            $stmt->bindParam(':age', $age, PDO::PARAM_INT);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Your donation request has been successfully submitted and is now under review.";
            } else {
                $_SESSION['message'] = "Error submitting request. Please try again.";
            }
            // ==========================================================
            // END OF THE FIX
            // ==========================================================
        } catch (PDOException $e) {
            error_log("Adoption Submit Error: " . $e->getMessage());
            $_SESSION['message'] = "A database error occurred. Please contact support.";
        }
        
        header("Location: user_submit_adoption.php");
        exit();
    } else {
        $_SESSION['message'] = $error_message;
        header("Location: user_submit_adoption.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Dog for Adoption</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background-color: #f2f4f7; color: #333; line-height: 1.6; }
        .message { max-width: 800px; margin: 20px auto; padding: 12px; border-radius: 5px; text-align: center; font-size: 1rem; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .main-content { max-width: 800px; margin: 30px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 25px; color: #333; }
        form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        label { font-weight: 600; color: #555; }
        input[type="text"], input[type="number"], textarea, select { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; font-family: 'Arial', sans-serif; }
        input[type="file"] { padding: 8px; border: 1px dashed #ccc; border-radius: 8px; }
        textarea { min-height: 120px; resize: vertical; }
        button[type="submit"] { background-color: #4CAF50; color: white; padding: 14px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background-color 0.3s; }
        button[type="submit"]:hover { background-color: #45a049; }
        @media (max-width: 600px) { .main-content { padding: 20px; margin: 15px; } }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="main-content">
        <h2>Submit a Dog for Adoption</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?= (strpos(strtolower($_SESSION['message']), 'error') !== false || strpos(strtolower($_SESSION['message']), 'sorry') !== false) ? 'error' : 'success' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="dog_name">Dog Name *</label>
                <input type="text" id="dog_name" name="dog_name" required>
            </div>

            <div class="form-group">
                <label for="breed">Breed *</label>
                <select id="breed" name="breed" required>
                    <option value="" disabled selected>Select a breed</option>
                    <?php foreach ($dogBreeds as $breed): ?>
                        <option value="<?= htmlspecialchars($breed) ?>"><?= htmlspecialchars($breed) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="age">Age (years)</label>
                <input type="number" id="age" name="age" min="0" max="20">
            </div>

            <div class="form-group">
                <label for="dog_image">Dog Photo (Optional)</label>
                <input type="file" id="dog_image" name="dog_image" accept="image/jpeg, image/png">
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required 
                          placeholder="Tell us about the dog's personality, health, and any special needs. Please also include your contact phone number here."></textarea>
            </div>

            <button type="submit">Submit Donation Request</button>
        </form>
    </main>
</body>
</html>