<?php
// Start session and output buffering at the VERY TOP (no whitespace before)
ob_start();
// Démarrage de session sécurisé
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Check database connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    header("Location: maintenance.php");
    exit();
}

// Set page title
$page_title = "Your Shopping Cart - Comfort Chairs";

// Process cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle adding to cart
    if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        $_SESSION['flash_message'] = "Product added to cart!";
        header("Location: cart.php");
        exit();
    }

    // Handle item removal
    if (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
        $remove_id = intval($_POST['product_id']);
        if (isset($_SESSION['cart'][$remove_id])) {
            unset($_SESSION['cart'][$remove_id]);
            $_SESSION['flash_message'] = "Item removed from cart";
        }
    }

    // Handle quantity update
    if (isset($_POST['update_quantities']) && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $product_id = intval($product_id);
            $quantity = max(1, intval($quantity));
           
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        $_SESSION['flash_message'] = "Cart updated successfully";
    }

    // Redirect to prevent form resubmission
    header("Location: cart.php");
    exit();
}

// Calculate totals
$cart_total = 0;
$cart_items = [];

if (!empty($_SESSION['cart'])) {
    // Get product details for items in cart
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    
    // Prepare and execute query with PDO
    $sql = "SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($product_ids);
    
    while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $cart_total += $subtotal;
        
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image_url'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
    $stmt->closeCursor();
}

// End output buffering
ob_end_flush();
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
                    <li><a href="cart.php" class="active">Cart</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="cart-page">
        <div class="container">
            <h1>Your Shopping Cart</h1>
            
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="flash-message">
                    <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
                    <?php unset($_SESSION['flash_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($cart_items)): ?>
                <form action="cart.php" method="post">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td class="product-info">
                                        <img src="images/<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             onerror="this.src='images/default.jpg'">
                                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                                    </td>
                                    <td class="price">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="quantity">
                                        <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                                               value="<?php echo $item['quantity']; ?>" min="1">
                                    </td>
                                    <td class="subtotal">$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    <td class="action">
                                        <button type="submit" name="remove_item" class="remove-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="total-label">Total:</td>
                                <td colspan="2" class="total-amount">$<?php echo number_format($cart_total, 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn continue-shopping">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <button type="submit" name="update_quantities" class="btn update-cart">
                            <i class="fas fa-sync-alt"></i> Update Cart
                        </button>
                        <a href="checkout.php" class="btn checkout">
                            Proceed to Checkout <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is currently empty.</p>
                    <a href="products.php" class="btn">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <!-- Your existing footer code from other pages -->
        </div>
    </footer>

    <script>
        // Confirm before removing item
        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to remove this item from your cart?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>