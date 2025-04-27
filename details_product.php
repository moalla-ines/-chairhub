<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);
require_once 'config.php';

if (!isset($db) || !($db instanceof mysqli)) {
    die("Database connection not established");
}

// Vérifier si un ID produit est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Récupérer les détails du produit
$query = "SELECT p.*, c.name AS category_name 
          FROM products p 
          JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: products.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

// Gestion de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
    
    $_SESSION['flash_message'] = "Product added to cart!";
    header("Location: cart.php");
    exit();
}

$page_title = $product['name'] . " - Comfort Chairs";
?>
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-detail-container {
            display: flex;
            gap: 40px;
            margin: 40px 0;
        }
        .product-images {
            flex: 1;
        }
        .product-info {
            flex: 1;
        }
        .main-image {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }
        .main-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        .price {
            font-size: 24px;
            font-weight: bold;
            color: #e63946;
            margin: 15px 0;
        }
        .original-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 10px;
        }
        .discount {
            background: #e63946;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .add-to-cart-form {
            margin-top: 30px;
        }
        .quantity {
            margin-bottom: 15px;
        }
        .quantity input {
            width: 60px;
            padding: 8px;
            text-align: center;
        }
        .product-meta {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .product-meta div {
            margin-bottom: 8px;
        }
        .in-stock {
            color: #2a9d8f;
            font-weight: bold;
        }
        .out-of-stock {
            color: #e63946;
            font-weight: bold;
        }
        .tab-content {
            display: none;
            padding: 20px 0;
        }
        .tab-content.active {
            display: block;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin: 30px 0 0;
        }
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            position: relative;
        }
        .tab-btn.active {
            font-weight: bold;
        }
        .tab-btn.active:after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #e63946;
        }
    </style>
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
                    <?php if(isset($_SESSION['cart']) && is_array($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                        <span class="cart-count"><?= count($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                    </a>
                </li>
                <li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard.php" class="admin-link">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" class="logout-link" onclick="return confirm('Do you really want to log out?')">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
</header>

<main class="container">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <?php unset($_SESSION['flash_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($product) && is_array($product)): ?>
    <div class="product-detail-container">
        <div class="product-images">
            <div class="main-image">
                <img src="images/<?= htmlspecialchars($product['image_url'] ?? 'default.jpg') ?>" 
                     alt="<?= htmlspecialchars($product['name'] ?? 'Product image') ?>"
                     onerror="this.src='images/default.jpg'">
            </div>
        </div>
        
        <div class="product-info">
            <h1><?= htmlspecialchars($product['name'] ?? 'Product Name') ?></h1>
            
            <div class="price">
                $<?= number_format($product['price'] ?? 0, 2) ?>
            </div>
            
            <?php if (isset($product['rating'])): ?>
            <div class="rating">
                <?php
                $rating = (float)$product['rating'];
                $full_stars = floor($rating);
                $half_star = ($rating - $full_stars) >= 0.5;
                
                for ($i = 0; $i < $full_stars; $i++) {
                    echo '<i class="fas fa-star"></i>';
                }
                
                if ($half_star) {
                    echo '<i class="fas fa-star-half-alt"></i>';
                    $full_stars++;
                }
                
                for ($i = $full_stars; $i < 5; $i++) {
                    echo '<i class="far fa-star"></i>';
                }
                ?>
                <span>(<?= htmlspecialchars($product['review_count'] ?? 0) ?> reviews)</span>
            </div>
            <?php endif; ?>
            
            <div class="description">
                <p><?= isset($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'No description available.' ?></p>
            </div>
            
            <div class="product-meta">
                <div class="availability">
                    <span>Availability:</span>
                    <?php if (isset($product['stock_quantity']) && $product['stock_quantity'] > 0): ?>
                        <span class="in-stock">In Stock (<?= (int)$product['stock_quantity'] ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($product['id'])): ?>
                <div class="sku">
                    <span>SKU:</span> CH-<?= htmlspecialchars($product['id']) ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($product['category_id'])): ?>
                <div class="category">
                    <span>Category:</span> 
                    <a href="products.php?category=<?= (int)$product['category_id'] ?>">
                        <?= htmlspecialchars($categories[$product['category_id']] ?? 'Uncategorized') ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <form method="post" class="add-to-cart-form">
                <div class="quantity">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                           max="<?= isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 0 ?>" 
                           <?= (!isset($product['stock_quantity']) || $product['stock_quantity'] <= 0) ? 'disabled' : '' ?>>
                </div>
                
                <button type="submit" name="add_to_cart" class="btn" 
                        <?= (!isset($product['stock_quantity']) || $product['stock_quantity'] <= 0) ? 'disabled' : '' ?>>
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
                
                <?php if (!isset($product['stock_quantity']) || $product['stock_quantity'] <= 0): ?>
                    <div class="out-of-stock-notice">
                        This item is currently out of stock
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="product-tabs">
        <div class="tabs">
            <button class="tab-btn active" data-tab="description">Description</button>
            <button class="tab-btn" data-tab="specifications">Specifications</button>
            <button class="tab-btn" data-tab="reviews">Reviews</button>
        </div>
        
        <div class="tab-content active" id="description">
            <h3>Product Description</h3>
            <p><?= isset($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'No description available.' ?></p>
        </div>
        
        <div class="tab-content" id="specifications">
            <h3>Technical Specifications</h3>
            <table>
                <?php if (isset($product['material'])): ?>
                <tr>
                    <th>Material</th>
                    <td><?= htmlspecialchars($product['material']) ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (isset($product['dimensions'])): ?>
                <tr>
                    <th>Dimensions</th>
                    <td><?= htmlspecialchars($product['dimensions']) ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (isset($product['weight_capacity'])): ?>
                <tr>
                    <th>Weight Capacity</th>
                    <td><?= htmlspecialchars($product['weight_capacity']) ?></td>
                </tr>
                <?php endif; ?>
                
                <?php if (isset($product['colors'])): ?>
                <tr>
                    <th>Colors</th>
                    <td>
                        <?php 
                        $colors = explode(',', $product['colors']);
                        foreach ($colors as $color) {
                            echo '<span class="color-chip" style="background-color:'.trim($color).'"></span>';
                        }
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="tab-content" id="reviews">
            <h3>Customer Reviews</h3>
            <?php
            if (isset($product_id)):
                $review_query = "SELECT * FROM reviews WHERE product_id = ? ORDER BY date DESC";
                $review_stmt = $db->prepare($review_query);
                $review_stmt->bind_param("i", $product_id);
                $review_stmt->execute();
                $reviews = $review_stmt->get_result();
                
                if ($reviews->num_rows > 0): ?>
                    <?php while ($review = $reviews->fetch_assoc()): ?>
                        <div class="review">
                            <div class="review-header">
                                <span class="review-author"><?= htmlspecialchars($review['author']) ?></span>
                                <span class="review-date"><?= date('F j, Y', strtotime($review['date'])) ?></span>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= ($review['rating'] ?? 0)): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <h4><?= htmlspecialchars($review['title'] ?? '') ?></h4>
                                <p><?= isset($review['content']) ? nl2br(htmlspecialchars($review['content'])) : '' ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No reviews yet. Be the first to review this product!</p>
                <?php endif;
                
                $review_stmt->close();
            endif; ?>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="add-review">
                    <h4>Add Your Review</h4>
                    <form method="post" action="submit_review.php">
                        <input type="hidden" name="product_id" value="<?= $product_id ?? '' ?>">
                        <div class="form-group">
                            <label for="review_title">Review Title</label>
                            <input type="text" id="review_title" name="review_title" required>
                        </div>
                        <div class="form-group">
                            <label>Rating</label>
                            <div class="rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                    <label for="star<?= $i ?>"></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="review_content">Your Review</label>
                            <textarea id="review_content" name="review_content" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn">Submit Review</button>
                    </form>
                </div>
            <?php else: ?>
                <p><a href="login.php">Login</a> to leave a review.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
        <div class="error-message">
            <p>Product not found or invalid product data.</p>
            <a href="products.php" class="btn">Back to Products</a>
        </div>
    <?php endif; ?>
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

    <script>
        // Gestion des onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Retirer la classe active de tous les boutons et contenus
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Ajouter la classe active au bouton cliqué et au contenu correspondant
                btn.classList.add('active');
                const tabId = btn.getAttribute('data-tab');
                if (tabId) {
                    const tabContent = document.getElementById(tabId);
                    if (tabContent) {
                        tabContent.classList.add('active');
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
if (isset($db) && $db instanceof mysqli) {
    $db->close();
}
?>