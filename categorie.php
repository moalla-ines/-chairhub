<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);
require_once 'config.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection not established");
}

$page_title = "Comfort Chairs - Categories";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header>
    <div class="container">
        <div class="logo">
            <h1>Comfort Chairs</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Our Chairs</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li>
                    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart 
                    <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                    </a>
                </li>
                <li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard.php" class="admin-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" class="logout-link" onclick="return confirm('Do you really want to log out?')">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <section class="categories-page">
        <div class="container">
            <h2>All Chair Categories</h2>
            <div class="categories-grid">
                <?php
                try {
                    $query = "SELECT * FROM categories";
                    $stmt = $pdo->query($query);
                    
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $imagePath = 'images/' . $row['image_url'];
                            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/chairhub/' . $imagePath;

                            echo '<div class="category-card">';
                            echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($row['name']) . '" 
                                 onerror="this.src=\'images/default.jpg\';this.alt=\'Image not available\'">';
                            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                            echo '<a href="products.php?category=' . $row['id'] . '" class="btn">View Collection</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No categories found.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error loading categories: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
                ?>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="container">
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Customer Service</h3>
            <ul>
                <li><a href="shipping.php">Shipping Policy</a></li>
                <li><a href="returns.php">Returns & Refunds</a></li>
                <li><a href="faq.php">FAQ</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Contact Us</h3>
            <p>Email: info@comfortchairs.com</p>
            <p>Phone: (123) 456-7890</p>
        </div>
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-pinterest"></i></a>
            </div>
        </div>
    </div>
    <div class="copyright">
        <p>&copy; <?= date("Y") ?> Comfort Chairs. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>