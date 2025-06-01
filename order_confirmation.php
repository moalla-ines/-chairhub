<?php
session_start();
require_once 'config.php';

// Debug initial
error_log("Accès order_confirmation.php - ID: ".($_GET['id'] ?? 'null'));

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['flash_message'] = "Numéro de commande invalide";
    header("Location: orders_history.php");
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['iduser'] ?? null;

if (!$user_id) {
    $_SESSION['flash_message'] = "Session expirée, veuillez vous reconnecter";
    header("Location: login.php");
    exit();
}

try {
    // REQUÊTE CORRIGÉE AVEC JOINTURE SÉCURISÉE
    $query = "
        SELECT o.*, u.name, u.email
        FROM orders o
        INNER JOIN users u ON o.user_id = u.iduser
        WHERE o.id = ? AND o.user_id = ?
    ";
    
    error_log("Exécution requête: $query"); // Debug
    
    $stmt = $db->prepare($query);
    if (!$stmt) {
        throw new Exception("Erreur préparation: ".$db->error);
    }
    
    $stmt->bind_param("ii", $order_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erreur exécution: ".$stmt->error);
    }
    
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // DEBUG: Log le résultat
    error_log("Résultat requête: ".json_encode($order));
    
    if (!$order) {
        // Vérifiez si la commande existe sans vérification utilisateur (debug)
        $test = $db->query("SELECT id FROM orders WHERE id = $order_id");
        if ($test->num_rows === 0) {
            throw new Exception("La commande #$order_id n'existe pas");
        } else {
            throw new Exception("La commande #$order_id ne vous appartient pas");
        }
    }

    // ... (le reste du code pour les articles)

} catch (Exception $e) {
    error_log("ERREUR: ".$e->getMessage());
    $_SESSION['flash_message'] = $e->getMessage();
    header("Location: orders_history.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-header { background-color: #f8f9fa; border-radius: 5px; padding: 20px; }
        .product-img { max-width: 60px; height: auto; }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Message d'erreur -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- En-tête de commande -->
        <div class="order-header mb-4">
            <h1>Commande #<?= $order['id'] ?></h1>
            <p class="text-muted">Passée le <?= date('d/m/Y à H:i', strtotime($order['order_date'])) ?></p>
            <p>Statut : <span class="badge bg-<?= 
                match($order['status']) {
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    default => 'warning'
                }
            ?>"><?= ucfirst($order['status']) ?></span></p>
        </div>

        <!-- Détails de livraison -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Informations de livraison</h2>
            </div>
            <div class="card-body">
                <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
            </div>
        </div>

        <!-- Articles -->
        <h2 class="mb-3">Articles</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix unitaire</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <img src="images/<?= htmlspecialchars($item['image_url']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="product-img me-2">
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td><?= number_format($item['unit_price'], 2) ?> €</td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['unit_price'] * $item['quantity'], 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="3">Total</th>
                        <th><?= number_format($order['total_amount'], 2) ?> €</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</body>
</html>