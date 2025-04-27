<?php
// Début absolu - aucun espace avant !
ob_start();

session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

require_once 'config.php';

if (!isset($db) || !($db instanceof mysqli)) {
    die("Database connection not established");
}

// Vérifier si l'utilisateur est admin
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Gestion des actions admin
if ($is_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suppression de produit
    if (isset($_POST['delete_product'])) {
        $product_id = intval($_POST['product_id']);
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $_SESSION['flash_message'] = "Produit supprimé avec succès";
        header("Location: product.php");
        exit();
    }
    
    // Ajout/Modification de produit
    if (isset($_POST['save_product'])) {
        $product_id = intval($_POST['product_id'] ?? 0);
        $name = $db->real_escape_string($_POST['name']);
        $description = $db->real_escape_string($_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        
        if ($product_id > 0) {
            // Mise à jour
            $stmt = $db->prepare("UPDATE products SET name=?, description=?, price=?, category_id=? WHERE id=?");
            $stmt->bind_param("ssdii", $name, $description, $price, $category_id, $product_id);
            $message = "Produit mis à jour avec succès";
        } else {
            // Insertion
            $stmt = $db->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssdi", $name, $description, $price, $category_id);
            $message = "Produit ajouté avec succès";
        }
        
        $stmt->execute();
        $_SESSION['flash_message'] = $message;
        header("Location: product.php");
        exit();
    }
}

// Gestion de l'ajout au panier (pour tous les utilisateurs)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $product_id = intval($_POST['product_id']);
    $quantity = max(1, intval($_POST['quantity'] ?? 1));
    
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
    $_SESSION['flash_message'] = "Produit ajouté au panier !";
    
    header("Location: product.php?id=".$product_id);
    exit();
}

// Récupérer toutes les catégories avec leurs produits
$categories = [];
$categories_query = "SELECT c.id, c.name, c.image_url FROM categories c";
$categories_result = $db->query($categories_query);

if ($categories_result->num_rows > 0) {
    while ($category = $categories_result->fetch_assoc()) {
        $products_query = "SELECT p.* FROM products p WHERE p.category_id = ?";
        $stmt = $db->prepare($products_query);
        $stmt->bind_param("i", $category['id']);
        $stmt->execute();
        $products_result = $stmt->get_result();
        
        $category['products'] = [];
        while ($product = $products_result->fetch_assoc()) {
            $category['products'][] = $product;
        }
        
        $categories[] = $category;
        $stmt->close();
    }
}

// Si un ID produit est spécifié, afficher ce produit en détail
$detailed_product = null;
$editing_product = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $product_query = "SELECT p.*, c.name as category_name FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     WHERE p.id = ?";
    $stmt = $db->prepare($product_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $detailed_product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Mode édition pour admin
    if ($is_admin && isset($_GET['edit'])) {
        $editing_product = $detailed_product;
    }
}

// Récupérer toutes les catégories pour le formulaire admin
$all_categories = [];
$cats_result = $db->query("SELECT id, name FROM categories");
while ($cat = $cats_result->fetch_assoc()) {
    $all_categories[] = $cat;
}

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
            background: #f9f9f9;
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
                    <li><a href="products.php" class="active">Our Chairs</a></li>
                    <li><a href="about.php">About us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li>
                        <a href="cart.php"><i class="fas fa-shopping-cart"></i> cart
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="cart-count"><?= array_sum($_SESSION['cart']) ?></span>
                        <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <?php if($is_admin): ?>
                                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin</a>
                            <?php endif; ?>
                            <a href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
                        <?php else: ?>
                            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
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
            <section class="product-form">
                <h2><?= $editing_product['id'] ? 'Modifier le produit' : 'Ajouter un nouveau produit' ?></h2>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $editing_product['id'] ?? 0 ?>">
                    
                    <div class="form-group">
                        <label for="name">Nom du produit*</label>
                        <input type="text" id="name" name="name" 
                               value="<?= htmlspecialchars($editing_product['name'] ?? '') ?>" 
                               required minlength="3" maxlength="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description*</label>
                        <textarea id="description" name="description" rows="5" 
                                  required minlength="10" maxlength="1000"><?= 
                                  htmlspecialchars($editing_product['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Prix (€)*</label>
                        <input type="number" id="price" name="price" step="0.01" 
                               value="<?= number_format($editing_product['price'] ?? 0, 2) ?>" 
                               required min="0.01" max="9999.99">
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Catégorie*</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Sélectionnez une catégorie --</option>
                            <?php foreach ($all_categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
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
                                <img src="images/<?= $editing_product['image_url'] ?>" 
                                     alt="<?= htmlspecialchars($editing_product['name']) ?>" 
                                     style="max-width: 200px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="<?= !empty($editing_product['id']) ? 'product.php?id='.$editing_product['id'] : 'product.php' ?>" 
                           class="btn btn-secondary">
                            Annuler
                        </a>
                        <button type="submit" name="save_product" class="btn btn-primary">
                            <?= $editing_product['id'] ? 'Mettre à jour' : 'Créer le produit' ?>
                        </button>
                    </div>
                </form>
            </section>
        
        <!-- Affichage détaillé d'un produit -->
        <?php elseif ($detailed_product): ?>
            <section class="product-detail">
                <?php if ($is_admin): ?>
                    <div class="admin-actions">
                        <a href="edit_product.php?id=<?= $detailed_product['id'] ?>" 
                           class="admin-btn" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete_product.php?id=<?= $detailed_product['id'] ?>" 
                           class="admin-btn" title="Supprimer" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit définitivement ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="product-images">
                    <img src="images/<?= $detailed_product['image_url'] ?>" 
                         alt="<?= htmlspecialchars($detailed_product['name']) ?>"
                         class="product-main-image">
                </div>
                
                <div class="product-info">
                    <h1><?= htmlspecialchars($detailed_product['name']) ?></h1>
                    <div class="product-meta">
                        <span class="category"><?= htmlspecialchars($detailed_product['category_name']) ?></span>
                        <span class="price"><?= number_format($detailed_product['price'], 2) ?> €</span>
                    </div>
                    
                    <div class="description">
                        <?= nl2br(htmlspecialchars($detailed_product['description'])) ?>
                    </div>
                    
                    <form method="post" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?= $detailed_product['id'] ?>">
                        <div class="form-group">
                            <label for="quantity">Quantité:</label>
                            <input type="number" id="quantity" name="quantity" 
                                   value="1" min="1" max="10" class="quantity-input">
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
                
                <?php if ($is_admin): ?>
                    <div class="admin-product-actions">
                        <a href="create_product.php" class="btn btn-admin">
                            <i class="fas fa-plus"></i> Ajouter un produit
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (empty($categories)): ?>
                <div class="alert alert-info">
                    Aucun produit disponible pour le moment.
                </div>
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
                                        <?php if ($is_admin): ?>
                                            <div class="admin-actions">
                                                <a href="edit_product.php?id=<?= $product['id'] ?>" 
                                                   class="admin-btn" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_product.php?id=<?= $product['id'] ?>" 
                                                   class="admin-btn" title="Supprimer" 
                                                   onclick="return confirm('Supprimer ce produit ?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <a href="product.php?id=<?= $product['id'] ?>" class="product-link">
                                            <div class="product-image-container">
                                                <img src="images/<?= $product['image_url'] ?>" 
                                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                                     class="product-thumbnail">
                                            </div>
                                            <div class="product-info">
                                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                                <div class="price"><?= number_format($product['price'], 2) ?> €</div>
                                            </div>
                                        </a>
                                        
                                        <form method="post" class="add-to-cart-form">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <button type="submit" name="add_to_cart" class="btn btn-sm btn-add-to-cart">
                                                <i class="fas fa-cart-plus"></i> Ajouter
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
        <!-- Votre pied de page existant -->
    </footer>

    <script>
        // Confirmation avant suppression
        document.querySelectorAll('[name="delete_product"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Supprimer ce produit définitivement ?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>