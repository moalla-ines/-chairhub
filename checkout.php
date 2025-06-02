<?php
// Start session and output buffering at the VERY TOP
ob_start();
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Set page title
$page_title = "Checkout - Comfort Chairs";

// Process checkout form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $delivery_address = trim($_POST['delivery_address']);
    $payment_method = $_POST['payment_method'];
    $notes = trim($_POST['notes']);
    
    // Basic validation
    if (empty($delivery_address)) {
        $error = "Delivery address is required";
    } else {
        // Calculate total amount
        $total_amount = 0;
        $product_ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
        
        $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id IN ($placeholders)");
        $stmt->execute($product_ids);
        
        $items = [];
        $out_of_stock = false;
        
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $quantity = $_SESSION['cart'][$product['id']];
            $subtotal = $product['price'] * $quantity;
            $total_amount += $subtotal;
            
            // Check stock availability
            if ($product['stock_quantity'] < $quantity) {
                $out_of_stock = true;
                $error = "Sorry, '{$product['name']}' doesn't have enough stock available";
                break;
            }
            
            $items[] = [
                'product_id' => $product['id'],
                'quantity' => $quantity,
                'price' => $product['price']
            ];
        }
        
        // ... [le début du fichier reste identique] ...

if (!$out_of_stock) {
    // Start transaction
    $pdo->beginTransaction();
    
    try {
    // Create order - version corrigée selon votre schéma
    $stmt = $pdo->prepare("INSERT INTO orders 
                         (user_id, total_amount, delivery_address, status) 
                         VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $delivery_address,
        'processing'  // status
        // Supprimé: $notes et $payment_method qui n'existent pas dans la table
    ]);
    
    $order_id = $pdo->lastInsertId();
        // Add order items
        $item_stmt = $pdo->prepare("INSERT INTO order_items 
                                   (order_id, product_id, quantity, price) 
                                   VALUES (?, ?, ?, ?)");
        
        $stock_stmt = $pdo->prepare("UPDATE products 
                                    SET stock_quantity = stock_quantity - ? 
                                    WHERE id = ? AND stock_quantity >= ?");
        
        foreach ($items as $item) {
            // Insert order item
            $item_stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Update stock with verification
            $stock_stmt->execute([
                $item['quantity'],
                $item['product_id'],
                $item['quantity']  // For the WHERE clause
            ]);
            
            if ($stock_stmt->rowCount() === 0) {
                throw new Exception("Insufficient stock for product ID: {$item['product_id']}");
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to confirmation page
        header("Location: order_confirmation.php?order_id=$order_id");
        exit();
        
     } catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Checkout Error: " . $e->getMessage());
    $error = "Erreur lors de la commande: " . $e->getMessage();
  // En production, vous pourriez vouloir afficher un message générique :
        // $error = "An error occurred during checkout. Please try again.";
    
}}}
}
// Get user details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, phone, country FROM users WHERE iduser = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate cart total
$cart_total = 0;
$cart_items = [];

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)");
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
                    <li><a href="cart.php">Cart</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="checkout-page">
        <div class="container">
            <h1>Checkout</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="checkout-grid">
                <div class="checkout-form">
                    <h2>Shipping Information</h2>
                    <form action="checkout.php" method="post">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" id="country" value="<?php echo htmlspecialchars($user['country']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_address">Delivery Address*</label>
                            <textarea id="delivery_address" name="delivery_address" required><?php echo isset($_POST['delivery_address']) ? htmlspecialchars($_POST['delivery_address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method">Payment Method*</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                                <option value="paypal" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'paypal') ? 'selected' : ''; ?>>PayPal</option>
                                <option value="bank_transfer" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                <option value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] == 'cash_on_delivery') ? 'selected' : ''; ?>>Cash on Delivery</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Order Notes (Optional)</label>
                            <textarea id="notes" name="notes"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn place-order-btn">
                            <i class="fas fa-lock"></i> Place Order
                        </button>
                    </form>
                </div>
                
                <div class="order-summary">
                    <h2>Your Order</h2>
                    <div class="summary-content">
                        <table class="order-items">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($item['name']); ?> 
                                            <strong>× <?php echo $item['quantity']; ?></strong>
                                        </td>
                                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Subtotal</th>
                                    <td>$<?php echo number_format($cart_total, 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Shipping</th>
                                    <td>Free Shipping</td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td>$<?php echo number_format($cart_total, 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Comfort Chairs</h3>
                    <p>Providing high-quality, comfortable chairs for your home and office since 2020.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Chair Street, Furniture City</p>
                    <p><i class="fas fa-phone"></i> (123) 456-7890</p>
                    <p><i class="fas fa-envelope"></i> info@comfortchairs.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Comfort Chairs. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>