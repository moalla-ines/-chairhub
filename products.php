<?php
session_start();
require_once 'config.php';

// Set page title
$page_title = "Product Details - Comfort Chairs";

// Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET['id']);

// Fetch product details
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Update page title with product name
$page_title = $product['name'] . " - Comfort Chairs";

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (array_key_exists($product_id, $_SESSION['cart'])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $_SESSION['message'] = "Product added to cart successfully!";
    header("Location: cart.php");
    exit();
}
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Our Chairs</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart 
                        <?php 
                        if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
                            echo '<span class="cart-count">'.count($_SESSION['cart']).'</span>';
                        }
                        ?>
                    </a></li>
                    <li>
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="account.php"><i class="fas fa-user"></i> My Account</a>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
                        <?php else: ?>
                            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
                <div class="search-bar">
                    <form action="search.php" method="GET">
                        <input type="text" name="query" placeholder="Search chairs...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </nav>
        </div>
    </header>

    <main class="product-page">
        <div class="container">
            <div class="product-details">
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <!-- You could add more images here in a gallery -->
                </div>
                
                <div class="product-info">
                    <h1><?php echo $product['name']; ?></h1>
                    
                    <div class="price">
                        $<?php echo number_format($product['price'], 2); ?>
                        <?php if ($product['original_price'] > $product['price']): ?>
                            <span class="original-price">$<?php echo number_format($product['original_price'], 2); ?></span>
                            <span class="discount"><?php echo round(100 - ($product['price'] / $product['original_price'] * 100)); ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="rating">
                        <?php
                        $full_stars = floor($product['rating']);
                        $half_star = ($product['rating'] - $full_stars) >= 0.5;
                        
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<i class="fas fa-star"></i>';
                        }
                        
                        if ($half_star) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                            $full_stars++; // Increment for the half star
                        }
                        
                        for ($i = $full_stars; $i < 5; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                        ?>
                        <span>(<?php echo $product['review_count']; ?> reviews)</span>
                    </div>
                    
                    <div class="description">
                        <p><?php echo $product['description']; ?></p>
                    </div>
                    
                    <div class="product-meta">
                        <div class="availability">
                            <span>Availability:</span>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="in-stock">In Stock (<?php echo $product['stock']; ?> available)</span>
                            <?php else: ?>
                                <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="sku">
                            <span>SKU:</span> <?php echo $product['sku']; ?>
                        </div>
                        
                        <div class="category">
                            <span>Category:</span> 
                            <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                <?php 
                                // Fetch category name
                                $cat_query = "SELECT name FROM categories WHERE id = ?";
                                $cat_stmt = $db->prepare($cat_query);
                                $cat_stmt->bind_param("i", $product['category_id']);
                                $cat_stmt->execute();
                                $cat_result = $cat_stmt->get_result();
                                $category = $cat_result->fetch_assoc();
                                echo $category['name'];
                                ?>
                            </a>
                        </div>
                    </div>
                    
                    <form method="post" class="add-to-cart-form">
                        <div class="quantity">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                   max="<?php echo $product['stock']; ?>">
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="btn" 
                                <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        
                        <?php if ($product['stock'] <= 0): ?>
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
                    <p><?php echo $product['description']; ?></p>
                    <?php if (!empty($product['features'])): ?>
                        <h4>Key Features:</h4>
                        <ul>
                            <?php 
                            $features = explode("\n", $product['features']);
                            foreach ($features as $feature) {
                                if (!empty(trim($feature))) {
                                    echo '<li>' . trim($feature) . '</li>';
                                }
                            }
                            ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <div class="tab-content" id="specifications">
                    <h3>Technical Specifications</h3>
                    <table>
                        <tr>
                            <th>Material</th>
                            <td><?php echo $product['material']; ?></td>
                        </tr>
                        <tr>
                            <th>Dimensions</th>
                            <td><?php echo $product['dimensions']; ?></td>
                        </tr>
                        <tr>
                            <th>Weight</th>
                            <td><?php echo $product['weight']; ?> lbs</td>
                        </tr>
                        <tr>
                            <th>Color</th>
                            <td><?php echo $product['color']; ?></td>
                        </tr>
                        <tr>
                            <th>Warranty</th>
                            <td><?php echo $product['warranty']; ?> year warranty</td>
                        </tr>
                    </table>
                </div>
                
                <div class="tab-content" id="reviews">
                    <h3>Customer Reviews</h3>
                    <?php
                    // Fetch reviews for this product
                    $review_query = "SELECT * FROM reviews WHERE product_id = ? ORDER BY date DESC";
                    $review_stmt = $db->prepare($review_query);
                    $review_stmt->bind_param("i", $product_id);
                    $review_stmt->execute();
                    $reviews = $review_stmt->get_result();
                    
                    if ($reviews->num_rows > 0) {
                        while ($review = $reviews->fetch_assoc()) {
                            echo '<div class="review">';
                            echo '<div class="review-header">';
                            echo '<span class="review-author">' . htmlspecialchars($review['author']) . '</span>';
                            echo '<span class="review-date">' . date('F j, Y', strtotime($review['date'])) . '</span>';
                            echo '<div class="review-rating">';
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $review['rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            echo '</div>';
                            echo '</div>';
                            echo '<div class="review-content">';
                            echo '<h4>' . htmlspecialchars($review['title']) . '</h4>';
                            echo '<p>' . nl2br(htmlspecialchars($review['content'])) . '</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No reviews yet. Be the first to review this product!</p>';
                    }
                    ?>
                    
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="add-review">
                            <h4>Add Your Review</h4>
                            <form method="post" action="submit_review.php">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <div class="form-group">
                                    <label for="review_title">Review Title</label>
                                    <input type="text" id="review_title" name="review_title" required>
                                </div>
                                <div class="form-group">
                                    <label>Rating</label>
                                    <div class="rating-input">
                                        <input type="radio" id="star5" name="rating" value="5"><label for="star5"></label>
                                        <input type="radio" id="star4" name="rating" value="4"><label for="star4"></label>
                                        <input type="radio" id="star3" name="rating" value="3"><label for="star3"></label>
                                        <input type="radio" id="star2" name="rating" value="2"><label for="star2"></label>
                                        <input type="radio" id="star1" name="rating" value="1"><label for="star1"></label>
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
            
            <!-- Related Products -->
            <section class="related-products">
                <h2>You May Also Like</h2>
                <div class="products-grid">
                    <?php
                    // Fetch related products (same category)
                    $related_query = "SELECT * FROM products WHERE category_id = ? AND id != ? LIMIT 4";
                    $related_stmt = $db->prepare($related_query);
                    $related_stmt->bind_param("ii", $product['category_id'], $product_id);
                    $related_stmt->execute();
                    $related_products = $related_stmt->get_result();
                    
                    if ($related_products->num_rows > 0) {
                        while ($related = $related_products->fetch_assoc()) {
                            echo '<div class="product-card">';
                            echo '<a href="product.php?id=' . $related['id'] . '">';
                            echo '<img src="' . $related['image_url'] . '" alt="' . $related['name'] . '">';
                            echo '<h3>' . $related['name'] . '</h3>';
                            echo '<p class="price">$' . number_format($related['price'], 2) . '</p>';
                            echo '</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>No related products found.</p>';
                    }
                    ?>
                </div>
            </section>
        </div>
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

    <script src="js/script.js"></script>
    <script src="js/script.js"></script>
<script>
    // Tab functionality
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            btn.classList.add('active');
            const tabId = btn.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
</script>   