<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY iduser DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h1>Liste des utilisateurs</h1>
    
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;">Opération effectuée avec succès!</p>
    <?php endif; ?>
    
    <a href="create_user.php">Ajouter un utilisateur</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Pays</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['iduser']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($user['country'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                    <a href="view_user.php?id=<?= $user['iduser'] ?>">Voir</a> |
                    <a href="edit_user.php?id=<?= $user['iduser'] ?>">Modifier</a> |
                    <a href="delete_user.php?id=<?= $user['iduser'] ?>" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>