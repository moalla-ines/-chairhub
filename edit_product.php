<?php
ob_start();
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

require_once 'config.php';

// Vérifier si admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer le produit à éditer
$product = null;
$page_title = "Ajouter un produit";

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        $page_title = "Modifier " . htmlspecialchars($product['name']);
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

// Récupérer toutes les catégories
$categories = [];
$cats_result = $db->query("SELECT id, name FROM categories");
while ($cat = $cats_result->fetch_assoc()) {
    $categories[] = $cat;
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
    <?php include 'header.php'; ?>

    <main>
        <div class="container">
            <section class="product-form">
                <h2><?= isset($product['id']) ? 'Modifier' : 'Ajouter' ?> un produit</h2>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                    
                    <div class="form-group">
                        <label for="name">Nom du produit</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Prix</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?= $product['price'] ?? '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Catégorie</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= isset($product['category_id']) && $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <a href="product.php" class="btn">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </section>
        </div>
    </main>

</body>
</html>