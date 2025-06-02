<?php
// Start session and include config
session_start();
require_once 'config.php';

// Verify PDO connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Erreur de connexion à la base de données");
}

// Gestion du formulaire
$message = '';
$message_type = ''; // success ou danger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate form data
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $material = $_POST['material'] ?? '';
        $dimensions = $_POST['dimensions'] ?? '';
        $weight_capacity = $_POST['weight_capacity'] ?? '';
        $colors = $_POST['colors'] ?? '';
        $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_FLOAT);
        $featured = isset($_POST['featured']) ? 1 : 0;
        $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);

        // Validate required fields
        if (empty($name) || empty($description) || $price === false || $category_id === false) {
            throw new Exception("Tous les champs obligatoires doivent être remplis correctement");
        }

        // Gestion de l'image
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Erreur lors de la création du dossier upload");
                }
            }
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Type de fichier non autorisé. Seuls JPG, PNG et GIF sont acceptés.");
            }

            // Generate unique filename and move file
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $target_path = $uploadDir . $filename;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                throw new Exception("Erreur lors de l'enregistrement de l'image");
            }
            
            $image_url = $target_path;
        } else {
            throw new Exception("Veuillez sélectionner une image valide");
        }

        // Insert product using PDO
        $stmt = $pdo->prepare("INSERT INTO products 
            (name, description, price, category_id, material, dimensions, weight_capacity, colors, image_url, rating, featured, stock_quantity) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $name, $description, $price, $category_id, $material, $dimensions, 
            $weight_capacity, $colors, $image_url, $rating, $featured, $stock_quantity
        ]);

        $_SESSION['product_added'] = true;
        $_SESSION['new_product_image'] = $image_url;
        header("Location: products.php");
        exit();

    } catch (PDOException $e) {
        $message = "Erreur de base de données: " . $e->getMessage();
        $message_type = "danger";
        error_log("Database error: " . $e->getMessage());
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = "danger";
    }
}

// Récupération des catégories
try {
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors du chargement des catégories";
    $message_type = "danger";
    error_log("Database error: " . $e->getMessage());
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
        }
        .btn-submit {
            background-color: #0d6efd;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-submit:hover {
            background-color: #0b5ed7;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="text-center">Ajouter un Produit</h1>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom du produit</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Prix (€)</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Catégorie</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Sélectionnez une catégorie</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label">Quantité en stock</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="10" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="material" class="form-label">Matériau</label>
                                <input type="text" class="form-control" id="material" name="material">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="dimensions" class="form-label">Dimensions</label>
                                <input type="text" class="form-control" id="dimensions" name="dimensions">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="weight_capacity" class="form-label">Capacité de poids</label>
                                <input type="text" class="form-control" id="weight_capacity" name="weight_capacity">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="colors" class="form-label">Couleurs disponibles</label>
                                <input type="text" class="form-control" id="colors" name="colors" placeholder="Séparées par des virgules">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="rating" class="form-label">Note (0-5)</label>
                                <input type="number" step="0.1" max="5" min="0" class="form-control" id="rating" name="rating">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image du produit</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF (max 2MB)</div>
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="featured" name="featured">
                            <label class="form-check-label" for="featured">Produit en vedette</label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary btn-submit me-md-2">
                                <i class="bi bi-plus-circle"></i> Ajouter le produit
                            </button>
                            <a href="products.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
</body>
</html>