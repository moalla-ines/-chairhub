<?php
ob_start();
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);


require_once 'config.php';

// Vérification améliorée de la connexion PDO
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Connexion à la base de données non établie.");
}
// Initialisation
$is_admin = false;
$user_role = '';

// Vérification si l'utilisateur est connecté
if (isset($_SESSION['iduser'])) {
    $user_id = intval($_SESSION['iduser']);

    // Requête pour récupérer le rôle de l'utilisateur
    $stmt = $pdo->prepare("SELECT role FROM users WHERE iduser = ?");
$stmt->execute([$user_id]);
$user_role = $stmt->fetchColumn();
$stmt->closeCursor();

    if ($user_role === 'admin') {
        $is_admin = true;
    }
}

if (isset($_SESSION['product_added']) && $_SESSION['product_added']) {
    // Récupérer le dernier produit ajouté
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 1");
    $new_product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($new_product) {
        echo '<div class="alert alert-success">Produit ajouté avec succès!</div>';
        echo '<img src="'.$new_product['image_url'].'" class="img-thumbnail" style="max-width: 200px;">';
    }
    
    unset($_SESSION['product_added']);
    unset($_SESSION['new_product_image']);
}

// Redirection vers edit_product.php si on clique sur "Ajouter un produit"
if (isset($_GET['add_product'])) {
    header("Location: edit_product.php");
    exit();
}

// Ajout/Modification de produit
if (isset($_POST['save_product'])) {
    $product_id = intval($_POST['id'] ?? 0);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);

    if ($product_id > 0) {
        // Mise à jour
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=? WHERE id=?");
        $stmt->execute([$name, $description, $price, $category_id, $product_id]);
        $message = "Produit mis à jour avec succès";
    } else {
        // Insertion
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $category_id]);
        $message = "Produit ajouté avec succès";
    }

    $_SESSION['flash_message'] = $message;
    header("Location: product.php");
    exit();
}

// Ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $product_id = intval($_POST['id']);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
    $_SESSION['flash_message'] = "Produit ajouté au panier !";

    header("Location: product.php?id=" . $product_id);
    exit();
}

// Récupérer toutes les catégories avec leurs produits
$categories = [];
$stmt = $pdo->query("SELECT c.id, c.name, c.image_url FROM categories c");
$categories_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($categories_result as $category) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->execute([$category['id']]);
    $category['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categories[] = $category;
    $stmt->closeCursor();
}

// Détails produit
$detailed_product = null;
$editing_product = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $detailed_product = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($is_admin && isset($_GET['edit'])) {
        $editing_product = $detailed_product;
    }
}

// Pour le formulaire d'admin
$all_categories = [];
$stmt = $pdo->query("SELECT id, name FROM categories");
$all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();

$page_title = $detailed_product ? htmlspecialchars($detailed_product['name']) : "Nos Produits - Comfort Chairs";
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .category-section {
            margin: 40px 0;
            padding: 20px;
            background:rgb(154, 153, 153);
            border-radius: 8px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            transition: transform 0.3s;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .add-to-cart-form {
            margin-top: 15px;
        }
        .admin-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
        .admin-btn {
            background: #333;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .product-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group textarea, 
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn-admin {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-admin:hover {
            background-color: #45a049;
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
    <div class="container">
        <!-- Message flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message">
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
                <?php unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <!-- Mode édition -->
                    <?php if ($editing_product): ?>
                <!-- ... (formulaire d'édition) ... -->
            <?php elseif ($detailed_product): ?>
                <section class="product-detail">
                    <?php if ($is_admin): ?>
                        <div class="admin-actions">
                            <a href="product.php?id=<?= $detailed_product['id'] ?>&edit=true" 
                               class="admin-btn" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_product.php?id=<?= $detailed_product['id'] ?>" 
                               class="admin-btn" title="Supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea id="description" name="description" rows="5" 
                                  required minlength="10" maxlength="1000"><?= 
                                  htmlspecialchars($editing_product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="price">Prix (€)*</label>
                        <input type="number" id="price" name="price" step="0.01" 
                               value="<?= htmlspecialchars(number_format((float)($editing_product['price'] ?? 0), 2)) ?>" 
                               required min="0.01" max="9999.99">
                    </div>

                    <div class="form-group">
                        <label for="category_id">Catégorie*</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Sélectionnez une catégorie --</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['id']) ?>" 
                                    <?= ($cat['id'] == ($editing_product['category_id'] ?? 0)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">Image du produit</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if (!empty($editing_product['image_url'])): ?>
                            <div class="current-image">
                                <p>Image actuelle :</p>
                                <img src="images/<?= htmlspecialchars($editing_product['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($editing_product['name']) ?>" 
                                     style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <a href="<?= !empty($editing_product['id']) ? 'product.php?id=' . urlencode($editing_product['id']) : 'product.php' ?>" 
                           class="btn btn-secondary">Annuler</a>
                        <button type="submit" name="save_product" class="btn btn-primary">
                            <?= !empty($editing_product['id']) ? 'Mettre à jour' : 'Créer le produit' ?>
                        </button>
                    </div>
                </form>
            </section

        <-- Affichage détaillé d'un produit -->
        <?php elseif ($detailed_product): ?>
            <section class="product-detail">
                 <?php if($_SESSION['role'] === 'admin'): ?>
                    <div class="admin-actions">
                        <a href="product.php?id=<?= urlencode($detailed_product['id']) ?>&edit=true" class="admin-btn" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete_product.php?id=<?= urlencode($detailed_product['id']) ?>" class="admin-btn" title="Supprimer"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit définitivement ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                <?php endif; ?>

                <div class="product-images">
                    <img src="images/<?= htmlspecialchars($detailed_product['image_url']) ?>" 
                         alt="<?= htmlspecialchars($detailed_product['name']) ?>" class="product-main-image">
                </div>

                <div class="product-info">
                    <h1><?= htmlspecialchars($detailed_product['name']) ?></h1>
                    <div class="product-meta">
                        <span class="category"><?= htmlspecialchars($detailed_product['category_name']) ?></span>
                        <span class="price"><?= number_format((float)$detailed_product['price'], 2) ?> €</span>
                    </div>

                    <div class="description">
                        <?= nl2br(htmlspecialchars($detailed_product['description'])) ?>
                    </div>

                   <form method="post" action="product.php" class="add-to-cart-form">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($detailed_product['id']) ?>">
                        <div class="form-group">
                            <label for="quantity">Quantité:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" class="quantity-input">
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-add-to-cart">

                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                        </button>
                    </form>
                </div>
            </section>

        <!-- Liste de tous les produits -->
        <?php else: ?>
            <div class="products-header">
                <h1>Nos produits</h1>
                  <?php if($_SESSION['role'] === 'admin'): ?>
                    <div class="admin-product-actions">
                        <a href="add_product.php?edit=true" class="btn-admin">
                            <i class="fas fa-plus"></i> Ajouter un produit
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (empty($categories)): ?>
                <div class="alert alert-info">Aucun produit disponible pour le moment.</div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <section class="category-section">
                        <h2 class="category-title"><?= htmlspecialchars($category['name']) ?></h2>

                        <?php if (empty($category['products'])): ?>
                            <p class="no-products">Aucun produit dans cette catégorie.</p>
                        <?php else: ?>
                            <div class="products-grid">
                                <?php foreach ($category['products'] as $product): ?>
                                    <div class="product-card">
                                      <?php if($_SESSION['role'] === 'admin'): ?>
                                            <div class="admin-actions">
                                                <a href="edit_product.php?id=<?= $product['id'] ?>&edit=true" class="admin-btn" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_product.php?id=<?= $product['id'] ?>" class="admin-btn" title="Supprimer"
                                                   onclick="return confirm('Supprimer ce produit ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <a href="product.php?id=<?= $product['id'] ?>" class="product-link">
                                            <div class="product-image-container">
                                                <img src="images/<?= htmlspecialchars($product['image_url']) ?>" 
                                                     alt="<?= htmlspecialchars($product['name']) ?>" class="product-thumbnail">
                                            </div>
                                            <div class="product-info">
                                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                                <div class="price"><?= number_format((float)$product['price'], 2) ?> €</div>
                                            </div>
                                        </a>
  <form method="post" action="cart.php" class="add-to-cart-form">
    <!-- Critical hidden field -->
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    
    <div class="quantity">
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="1" min="1" 
               max="<?= isset($product['stock_quantity']) ? (int)$product['stock_quantity'] : 10 ?>" 
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
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> Comfort Chairs. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Confirmation avant suppression
        document.querySelectorAll('[onclick*="confirm"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm(e.target.getAttribute('data-confirm') || 'Êtes-vous sûr ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>