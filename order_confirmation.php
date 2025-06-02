<?php
session_start();
require_once 'config.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier qu'un ID de commande est présent
if (!isset($_GET['id'])) {
    $_SESSION['flash_message'] = "Aucune commande à afficher";
    $_SESSION['flash_type'] = 'error';
    header("Location: index.php");
    exit();
}

$orderId = intval($_GET['id']);

// Récupérer les détails de la commande
$orderQuery = $pdo->prepare("
    SELECT o.*, u.name, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.iduser
    WHERE o.id = ? AND o.user_id = ?
");
$orderQuery->execute([$orderId, $_SESSION['user_id']]);
$order = $orderQuery->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['flash_message'] = "Commande introuvable";
    $_SESSION['flash_type'] = 'error';
    header("Location: orders.php");
    exit();
}

// Récupérer les articles de la commande
$itemsQuery = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsQuery->execute([$orderId]);
$items = $itemsQuery->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande #<?= $orderId ?> - Comfort Chairs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .confirmation-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .order-header {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .product-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 4px;
        }
        .total-amount {
            font-size: 1.3em;
            font-weight: bold;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <div class="confirmation-container">
            <div class="order-header text-center">
                <h1 class="text-success">✅ Commande confirmée</h1>
                <h3 class="mt-3">Merci pour votre achat !</h3>
                <p class="text-muted">Votre commande #<?= $orderId ?> a bien été enregistrée.</p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h5>Informations de livraison</h5>
                    <div class="card mb-4">
                        <div class="card-body">
                            <p><strong>Nom :</strong> <?= htmlspecialchars($order['username']) ?></p>
                            <p><strong>Email :</strong> <?= htmlspecialchars($order['email']) ?></p>
                            <?php if (!empty($order['delivery_address'])): ?>
                                <p><strong>Adresse :</strong><br><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h5>Détails de la commande</h5>
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Numéro de commande :</strong> #<?= $orderId ?></p>
                            <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
                            <p><strong>Statut :</strong> <span class="badge bg-success">Confirmée</span></p>
                            <p class="total-amount"><strong>Total :</strong> <?= number_format($order['total_amount'], 2) ?> €</p>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mt-4">Articles commandés</h5>
            <div class="list-group mb-4">
                <?php foreach ($items as $item): ?>
                <div class="product-item list-group-item">
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                    <div class="flex-grow-1">
                        <h6><?= htmlspecialchars($item['name']) ?></h6>
                        <div class="d-flex justify-content-between">
                            <span><?= number_format($item['price'], 2) ?> € x <?= $item['quantity'] ?></span>
                            <strong><?= number_format($item['price'] * $item['quantity'], 2) ?> €</strong>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="products.php" class="btn btn-outline-primary">Continuer vos achats</a>
                <a href="orders.php" class="btn btn-primary">Voir toutes vos commandes</a>
            </div>
        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>