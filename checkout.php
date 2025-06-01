<?php

session_start();
error_log('Session ID: ' . session_id());
error_log('Session data: ' . print_r($_SESSION, true));
require_once 'config.php';

// Debug: Afficher le contenu du panier
error_log('Contenu du panier: ' . print_r($_SESSION['cart'] ?? [], true));

// 1. Vérification du panier
if (empty($_SESSION['cart'])) {
    $_SESSION['flash_message'] = "Votre panier est vide.";
    $_SESSION['flash_type'] = 'error';
    error_log('Redirection vers cart.php - Panier vide');
    header("Location: cart.php");
    exit();
}

// 2. Vérification de la connexion
$isLoggedIn = !empty($_SESSION['iduser']);
$userId = $isLoggedIn ? $_SESSION['iduser'] : null;

if (!$isLoggedIn) {
    error_log('User not logged in. Session data: ' . print_r($_SESSION, true));
    $loginRequired = true;
    $errorMessage = "Veuillez vous connecter pour finaliser votre achat.";
} else {
    error_log('User logged in with ID: ' . $_SESSION['iduser']);
}   
// 3. Fonction de création de commande
function createOrder($userId, $products, $db, $address) {
    $db->begin_transaction();
    try {
        // Calcul du total
        $total = array_reduce($products, fn($sum, $p) => $sum + ($p['price'] * $p['quantity']), 0);

        // Insertion commande
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, status) VALUES (?, ?, ?, 'processing')");
        $stmt->bind_param("ids", $userId, $total, $address);
        $stmt->execute();
        $orderId = $db->insert_id;
        $stmt->close();

        // Insertion des articles
        foreach ($products as $p) {
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $orderId, $p['product_id'], $p['quantity'], $p['price']);
            $stmt->execute();
            $stmt->close();

            // Mise à jour stock
            $update = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $update->bind_param("ii", $p['quantity'], $p['product_id']);
            $update->execute();
            if ($db->affected_rows === 0) {
                throw new Exception("Échec mise à jour stock pour produit #{$p['product_id']}");
            }
            $update->close();
        }

        $db->commit();
        return $orderId;

    } catch (Exception $e) {
        $db->rollback();
        error_log("Erreur création commande: " . $e->getMessage());
        throw $e;
    }
}

// 4. Préparation des produits du panier
$products = [];
foreach ($_SESSION['cart'] as $id => $qty) {
    $stmt = $db->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        $_SESSION['flash_message'] = "Produit #$id n'existe plus";
        $_SESSION['flash_type'] = 'error';
        header("Location: cart.php");
        exit();
    }

    if ($product['stock_quantity'] < $qty) {
        $_SESSION['flash_message'] = "Stock insuffisant pour {$product['name']}";
        $_SESSION['flash_type'] = 'error';
        header("Location: cart.php");
        exit();
    }

    $products[] = [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $qty
    ];
}

// 5. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    try {
        // Vérifier d'abord si l'utilisateur est connecté
        if (!isset($_SESSION['iduser'])) {
            throw new Exception("Vous devez être connecté pour passer commande");
        }

        $address = trim($_POST['address'] ?? '');
        if (strlen($address) < 4) {
            throw new Exception("Adresse doit contenir au moins 4 caractères");
        }

        $orderId = createOrder($_SESSION['iduser'], $products, $db, $address);
        
        // SUCCÈS - Vidage panier et redirection
        unset($_SESSION['cart']);
        $_SESSION['flash_message'] = "Commande #$orderId validée!";
        $_SESSION['flash_type'] = 'success';
        
        error_log("Commande #$orderId créée - Redirection vers orders_history.php");
        header("Location: orders_history.php");
        exit();

    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
        error_log("Erreur checkout: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h4 mb-0">Finalisation de commande</h2>
                    </div>
                    
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <h3 class="h5 mb-3">Votre panier</h3>
                        <ul class="list-group mb-4">
                            <?php foreach ($products as $p): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <div>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                    <div class="text-muted small">Qté: <?= $p['quantity'] ?></div>
                                </div>
                                <div class="text-end">
                                    <?= number_format($p['price'], 2) ?> €
                                    <div class="fw-bold"><?= number_format($p['price'] * $p['quantity'], 2) ?> €</div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>

                        <form method="post">
                            <div class="mb-4">
                                <label for="address" class="form-label fw-bold">Adresse de livraison</label>
                                <textarea class="form-control" name="address" id="address" rows="4" required
                                          minlength="10" placeholder="10 rue Exemple, 75000 Paris"></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="cart.php" class="btn btn-outline-secondary">Retour</a>
                                <button type="submit" name="confirm_order" class="btn btn-primary px-4">
                                    <i class="bi bi-check-circle"></i> Confirmer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>  
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>