<?php
ob_start();
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

require_once 'config.php';

// Vérification admin plus robuste
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['flash_message'] = "Accès refusé : permissions insuffisantes";
    $_SESSION['flash_type'] = "error";
    header("Location: login.php");
    exit();
}

// Initialisation
$product = null;
$page_title = "Ajouter un produit";
$categories = [];
$errors = [];

// Récupération du produit à éditer
if (isset($_GET['id'])) {
    $product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($product_id) {
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $product = $stmt->get_result()->fetch_assoc();
            if ($product) {
                $page_title = "Modifier " . htmlspecialchars($product['name']);
            } else {
                $errors[] = "Produit introuvable";
            }
        } else {
            $errors[] = "Erreur lors de la récupération du produit";
        }
        $stmt->close();
    } else {
        $errors[] = "ID produit invalide";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT) ?? 0;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);

    // Validation
    if (empty($name)) $errors[] = "Le nom du produit est requis";
    if (empty($description)) $errors[] = "La description est requise";
    if ($price === false || $price <= 0) $errors[] = "Prix invalide";
    if (!$category_id) $errors[] = "Catégorie invalide";

    if (empty($errors)) {
        // Échappement pour sécurité
        $name = $db->real_escape_string($name);
        $description = $db->real_escape_string($description);
        
        try {
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
            
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = $message;
                $_SESSION['flash_type'] = "success";
                
                // Redirection vers la liste des produits après modification
                header("Location: products.php");
                exit();
            } else {
                throw new Exception("Erreur lors de l'enregistrement");
            }
        } catch (Exception $e) {
            $errors[] = "Erreur database: " . $e->getMessage();
        }
    }
}

// Récupération des catégories
$cats_result = $db->query("SELECT id, name FROM categories ORDER BY name");
if ($cats_result) {
    while ($cat = $cats_result->fetch_assoc()) {
        $categories[] = $cat;
    }
} else {
    $errors[] = "Erreur lors du chargement des catégories";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(74,144,226,0.2);
        }
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #4a90e2;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background-color: #3a7bc8;
        }
        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background-color: #e0e0e0;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <h3>Erreurs :</h3>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="product-form">
            <h1><?= htmlspecialchars($page_title) ?></h1>
            
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?? 0 ?>">
                
                <div class="form-group">
                    <label for="name">Nom du produit *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" class="form-control" required><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Prix (€) *</label>
                    <input type="number" id="price" name="price" class="form-control" 
                           step="0.01" min="0.01" value="<?= isset($product['price']) ? number_format($product['price'], 2) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Catégorie *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">-- Sélectionnez --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" 
                                <?= isset($product['category_id']) && $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Image du produit</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                    <?php if (!empty($product['image_url'])): ?>
                        <div class="current-image" style="margin-top: 1rem;">
                            <p>Image actuelle :</p>
                            <img src="images/<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 style="max-width: 200px; margin-top: 0.5rem;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <a href="products.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </section>
    </main>

</body>
</html>