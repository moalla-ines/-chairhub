<?php
session_start();
require_once 'config.php'; // Inclure avant toute utilisation de $db

// Vérifier que la connexion existe
if (!isset($db) || !($db instanceof mysqli)) {
    die("Database connection not established");
}

$page_title = "Comfort Chairs - Home";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
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
                    <li><a href="index.php" class="active">Home</a></li>
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
                            <!-- Debug: Affiche les infos de session en commentaire HTML -->
                            <!-- Session: <?= htmlspecialchars(json_encode($_SESSION)) ?> -->
                            
                            
                            <?php if($_SESSION['role'] === 'admin'): ?>
                                <a href="admin/dashboard.php" class="admin-link">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            <?php endif; ?>
                            
                            <a href="logout.php" class="logout-link" onclick="return confirm('Do you really want to log out?')">
    <i class="fas fa-sign-out-alt"></i></a>
                        <?php else: ?>
                            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                        <?php endif; ?>
                    </li>
                </ul>
                
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h2>Discover Perfect Seating for Every Space</h2>
                <p>From ergonomic office chairs to luxurious lounge chairs, we have what you need.</p>
                <a href="products.php" class="btn">Shop Now</a>
            </div>
        </section>
        <section class="featured-categories">
    <div class="container">
        <h2>Our Chair Categories</h2>
        <div class="categories-grid">
            <?php
            $query = "SELECT * FROM categories LIMIT 4";
            $result = $db->query($query);
            
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Chemin relatif depuis la racine du site
                    $imagePath = 'images/' . $row['image_url'];
                    
                    // Debug: vérifiez que le fichier existe
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/chairhub/' . $imagePath;
                    if (!file_exists($fullPath)) {
                        echo "<!-- Fichier introuvable: $fullPath -->";
                    }
                    
                    echo '<div class="category-card">';
                    echo '<img src="'.$imagePath.'" alt="'.htmlspecialchars($row['name']).'" 
                         onerror="this.src=\'images/default.jpg\';this.alt=\'Image non disponible\'">';
                    echo '<h3>'.htmlspecialchars($row['name']).'</h3>';
                    echo '<a href="products.php?category='.$row['id'].'" class="btn">View Collection</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No categories found.</p>';
            }
            ?>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <h2>Featured Chairs</h2>
        <div class="products-grid">
            <?php
            $query = "SELECT * FROM products WHERE featured=1 LIMIT 4";
            $result = $db->query($query);
            
            if($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $imagePath = 'images/' . $row['image_url'];
                    echo '<div class="product-card">';
                    echo '<img src="'.$imagePath.'" alt="'.htmlspecialchars($row['name']).'" 
                         onerror="this.src=\'images/default.jpg\';this.alt=\'Image non disponible\'">';
                    echo '<h3>'.htmlspecialchars($row['name']).'</h3>';
                    echo '<p class="price">$'.number_format($row['price'], 2).'</p>';
                    echo '<a href="product.php?id='.$row['id'].'" class="btn">View Details</a>';
                    echo '</div>';
                }
            } else {
                echo '<p>No featured products available.</p>';
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
            <p>&copy; <?php echo date("Y"); ?> Comfort Chairs. All Rights Reserved.</p>
        </div>
    </footer>

   
</body>
<?php
$db->close();
?>
</html>

