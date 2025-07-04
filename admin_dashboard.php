<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'db.php';

// Redirect to login if user is not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$admin_id = (int)$_SESSION['user_id'];

// Handle dog deletion
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    $stmt = $conn->prepare("SELECT image FROM dogs WHERE id = ? AND listed_by = ?");
    $stmt->execute([$delete_id, $admin_id]);
    $dog = $stmt->fetch();

    if ($dog) {
        if (!empty($dog['image']) && file_exists(__DIR__ . '/' . $dog['image'])) {
            unlink(__DIR__ . '/' . $dog['image']);
        }

        // First delete related adoptions
        $stmt = $conn->prepare("DELETE FROM adoptions WHERE dog_id = ?");
        $stmt->execute([$delete_id]);

        // Then delete from dogs
        $stmt = $conn->prepare("DELETE FROM dogs WHERE id = ?");
        $stmt->execute([$delete_id]);

        $_SESSION['message'] = 'Dog deleted successfully.';
        header('Location: admin_dashboard.php');
        exit();
    }
}

// Handle form submission to add a new dog
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $age = (int)$_POST['age']; // Cast to integer for safety
    $description = $_POST['description'];
    $listed_by = $admin_id;
    $image = '';

    // --- AGE VALIDATION ---
    if ($age < 0 || $age > 15) {
        $error_message = "Age must be a number between 0 and 15.";
    }

    // Image validation (only runs if age is valid)
    if ($error_message === '' && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            $error_message = "Only PNG or JPEG images are allowed.";
        } else {
            $newFileName = uniqid('dog_', true) . '.' . $fileExtension;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                $image = $destination;
            } else {
                $error_message = "Error uploading the image.";
            }
        }
    } elseif ($error_message === '') {
        $error_message = "Please upload an image.";
    }

    if ($error_message === '') {
        $stmt = $conn->prepare("INSERT INTO dogs (name, breed, age, description, image, listed_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $breed, $age, $description, $image, $listed_by]);

        $_SESSION['message'] = 'Dog listed successfully!';
        header('Location: admin_dashboard.php');
        exit();
    }
}

// Fetch all available dogs (admin-listed and donated) that haven't been completedly adopted
$stmt = $conn->prepare("
    SELECT d.id, d.name, d.breed, d.age, d.description, d.image, d.listed_by,
        CASE 
            WHEN d.listed_by = :admin_id THEN 'admin'
            ELSE 'donated'
        END AS source
    FROM dogs d
    WHERE d.id NOT IN (
        SELECT dog_id FROM adoptions WHERE status = 'completed' AND dog_id IS NOT NULL
    )
    ORDER BY d.age ASC
");

$stmt->execute(['admin_id' => $admin_id]);
$dogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dog breeds array
$dogBreeds = [
    "Labrador Retriever", "German Shepherd", "Golden Retriever", "Bulldog",
    "Beagle", "Poodle", "Rottweiler", "Yorkshire Terrier", "Boxer",
    "Dachshund", "Siberian Husky", "Doberman Pinscher", "Shih Tzu",
    "Great Dane", "Chihuahua", "Border Collie", "Australian Shepherd",
    "Cocker Spaniel", "Pug", "Saint Bernard", "Akita", "Maltese", "Vizsla",
    "Newfoundland", "Bichon Frise", "Cane Corso", "Boston Terrier", "Weimaraner",
    "Bernese Mountain Dog", "English Springer Spaniel", "Basset Hound", "Collie", "Other"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="csss/admin_dashboard.css">
    <style>
        .dog-card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 20px; position: relative; }
        .dog-source { position: absolute; top: 10px; right: 10px; background: #f0f0f0; padding: 3px 8px; border-radius: 4px; font-size: 0.8em; }
        .source-admin { background: #d4edda; color: #155724; }
        .source-donated { background: #fff3cd; color: #856404; }
        .dog-card img { max-width: 100%; height: auto; border-radius: 5px; margin-bottom: 10px; }
        .delete-button { background: #f44336; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <?php include('admin_header.php'); ?>
    </header>
    <main>
        <h2>List a New Dog for Adoption</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Dog Name" required>
            <select name="breed" required>
                <option value="" disabled selected>Select Breed</option>
                <?php foreach ($dogBreeds as $breed): ?>
                    <option value="<?php echo htmlspecialchars($breed); ?>"><?php echo htmlspecialchars($breed); ?></option>
                <?php endforeach; ?>
            </select>
            <!-- AGE INPUT FIX -->
            <input type="number" name="age" placeholder="Age" min="0" max="15" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="file" name="image" accept="image/png, image/jpeg" required>
            <input type="submit" name="submit" value="List Dog">
        </form>

        <h2>Available Dogs for Adoption</h2>
        <div class="dog-list">
            <?php if (empty($dogs)): ?>
                <p>No dogs available for adoption.</p>
            <?php else: ?>
                <?php foreach ($dogs as $dog): ?>
                    <div class="dog-card">
                        <span class="dog-source <?= $dog['source'] === 'admin' ? 'source-admin' : 'source-donated' ?>">
                            <?= $dog['source'] === 'admin' ? 'Admin Listed' : 'Donated Dog' ?>
                        </span>
                        <?php
                        $imagePath = htmlspecialchars($dog['image']);
                        $fullImagePath = __DIR__ . '/' . $dog['image'];
                        if (!empty($dog['image']) && file_exists($fullImagePath)): ?>
                            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($dog['name']); ?>">
                        <?php else: ?>
                            <img src="uploads/default_dog.png" alt="No Image Available">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($dog['name']); ?></h3>
                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($dog['breed']); ?></p>
                        <p><strong>Age:</strong> <?php echo htmlspecialchars($dog['age']); ?> years</p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($dog['description']); ?></p>

                        <?php if ($dog['listed_by'] == $admin_id): ?>
                            <form method="GET" onsubmit="return confirm('Are you sure you want to delete this dog?');">
                                <input type="hidden" name="delete_id" value="<?php echo (int)$dog['id']; ?>">
                                <button type="submit" class="delete-button">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>