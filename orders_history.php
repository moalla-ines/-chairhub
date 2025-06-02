<?php
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);
require_once 'config.php';

// Vérification de la connexion PDO
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Database connection not established");
}

// Récupération du rôle
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

// Requête différente selon le rôle
if ($is_admin) {
    // Admin - voir toutes les commandes
    $query = "
        SELECT 
            o.id, 
            o.order_date, 
            o.total_amount, 
            o.status,
            u.iduser,
            u.name as customer_name
        FROM orders o
        JOIN users u ON o.user_id = u.iduser
        ORDER BY o.order_date DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
} else {
    // Utilisateur normal - seulement ses commandes
    $query = "
        SELECT 
            id, 
            order_date, 
            total_amount, 
            status
        FROM orders
        WHERE user_id = ?
        ORDER BY order_date DESC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
}

// Récupération des résultats
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $is_admin ? 'Toutes les commandes' : 'Mes commandes' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?= $is_admin ? 'Toutes les commandes' : 'Historique de mes commandes' ?></h1>
            <a href="<?= $is_admin ? 'dashboard.php' : 'profile.php' ?>" class="btn btn-secondary">
                Retour
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if ($is_admin): ?>
                        <th>Client</th>
                        <?php endif; ?>
                        <th>N° Commande</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="<?= $is_admin ? 6 : 5 ?>" class="text-center">
                                Aucune commande trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <?php if ($is_admin): ?>
                            <td>
                                <?= htmlspecialchars($order['customer_name']) ?>
                                <small class="text-muted d-block">ID: <?= $order['iduser'] ?></small>
                            </td>
                            <?php endif; ?>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= number_format($order['total_amount'], 2) ?> €</td>
                            <td>
                                <span class="order-status status-<?= strtolower($order['status']) ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_details.php?id=<?= $order['id'] ?>" 
                                   class="btn btn-sm btn-primary">
                                   Détails
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>