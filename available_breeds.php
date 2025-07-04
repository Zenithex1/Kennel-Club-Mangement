<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch all dogs that are not completed adoptions
$stmt = $conn->query("
    SELECT d.* 
    FROM dogs d
    WHERE NOT EXISTS (
        SELECT 1 FROM adoptions a 
        WHERE a.dog_id = d.id AND a.status = 'completed'
    )
    ORDER BY d.breed, d.name
");
$dogs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dogs Available for Adoption</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f8fa;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }
        header {
            background-color: #2c3e50;
            padding: 15px;
            color: white;
        }
        header a {
            color: #fff;
            text-decoration: none;
            padding: 8px 20px;
            font-weight: 600;
            transition: background-color 0.3s ease, padding 0.3s ease;
        }
        header a:hover {
            background-color: #ff6f61;
            padding: 8px 25px;
        }
        main {
            padding: 20px;
            margin-top: 20px;
        }
        h2 {
            font-size: 2.5rem;
            color: #222;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 600;
            animation: fadeIn 0.5s ease-out;
        }
        .search-box {
            margin: 20px auto;
            padding: 10px 20px;
            width: 80%;
            max-width: 600px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            text-align: center;
            display: block;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            transform: scale(0.98);
        }
        .search-box:focus {
            border-color: #6c5ce7;
            outline: none;
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .breed-list {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            width: 100%;
            margin-top: 40px;
        }
        .breed-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 15px;
            padding: 20px;
            width: calc(33.333% - 40px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            transform: scale(0.98);
            max-width: calc(33.333% - 40px);
            box-sizing: border-box;
            text-align: center;
        }
        .breed-card:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        .breed-card h3 {
            font-size: 1.8rem;
            color: #333;
            margin-top: 15px;
            font-weight: 700;
        }
        .inquire-button {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 24px;
            background: linear-gradient(45deg, #6c5ce7, #ff6f61);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .inquire-button:hover {
            background: linear-gradient(45deg, #ff6f61, #6c5ce7);
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #777;
        }
        .dog-image {
            width: 100%;
            height: auto;
            object-fit: contain;
            max-height: 200px;
            border-radius: 10px;
        }
        .dog-details {
            margin-top: 10px;
        }
        .dog-details p {
            margin: 5px 0;
            font-size: 0.95rem;
        }
        @media screen and (max-width: 1200px) {
            .breed-card {
                width: calc(50% - 40px);
            }
        }
        @media screen and (max-width: 768px) {
            .breed-card {
                width: calc(100% - 40px);
            }
        }
        @media screen and (max-width: 480px) {
            h2 {
                font-size: 2rem;
            }
            .breed-card h3 {
                font-size: 1.5rem;
            }
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <header>
        <?php include 'header.php'; ?>
    </header>
    <main>
        <h2>Dogs Available for Adoption</h2>
        
        <input type="text" class="search-box" placeholder="Search dogs by breed or name..." id="dogSearch" onkeyup="debounceSearch()">
        
        <div class="breed-list" id="dogContainer">
            <?php if (count($dogs) > 0): ?>
                <?php foreach ($dogs as $dog): ?>
                    <div class="breed-card" data-search="<?= htmlspecialchars(strtolower($dog['name'] . ' ' . $dog['breed'])) ?>">
                        <?php if (!empty($dog['image']) && file_exists($dog['image'])): ?>
                            <img src="<?= htmlspecialchars($dog['image']) ?>" alt="<?= htmlspecialchars($dog['name']) ?>" class="dog-image" />
                        <?php else: ?>
                            <img src="assets/images/default_dog.jpg" alt="No image available" class="dog-image" />
                        <?php endif; ?>
                        
                        <h3><?= htmlspecialchars($dog['name']) ?></h3>
                        
                        <div class="dog-details">
                            <p><strong>Breed:</strong> <?= htmlspecialchars($dog['breed']) ?></p>
                            <p><strong>Age:</strong> <?= htmlspecialchars($dog['age']) ?> years</p>
                            <p><strong>Description:</strong> <?= htmlspecialchars($dog['description']) ?></p>
                        </div>
                        
                        <a href="send_inquiry.php?breed=<?= urlencode($dog['breed']) ?>&dog_id=<?= $dog['id'] ?>&dog_name=<?= urlencode($dog['name']) ?>" class="inquire-button">
                            Inquire About <?= htmlspecialchars($dog['name']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-results">No dogs available for adoption at the moment.</p>
            <?php endif; ?>
        </div>
        
        <script>
            let debounceTimer;

            function debounceSearch() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(searchDogs, 300);
            }

            function searchDogs() {
                const input = document.getElementById('dogSearch').value.toLowerCase();
                const cards = document.querySelectorAll('.breed-card');
                const container = document.getElementById('dogContainer');
                let hasResults = false;

                const existingMessage = document.getElementById('noResults');
                if (existingMessage) existingMessage.remove();

                if (input.trim() === '') {
                    cards.forEach(card => card.style.display = 'block');
                    return;
                }

                cards.forEach(card => {
                    const searchText = card.getAttribute('data-search');
                    if (searchText.includes(input)) {
                        card.style.display = 'block';
                        hasResults = true;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (!hasResults) {
                    const noResultDiv = document.createElement('div');
                    noResultDiv.className = 'no-results';
                    noResultDiv.id = 'noResults';
                    noResultDiv.textContent = `No dogs found matching "${input}"`;
                    container.appendChild(noResultDiv);
                }
            }
        </script>
    </main>
</body>
</html>