<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: list_users.php");
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE iduser = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("Utilisateur non trouvé");
    }
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'utilisateur</title>
</head>
<body>
    <h1>Détails de l'utilisateur</h1>
    
    <p><strong>ID:</strong> <?= htmlspecialchars($user['iduser']) ?></p>
    <p><strong>Nom:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Téléphone:</strong> <?= htmlspecialchars($user['phone'] ?? 'N/A') ?></p>
    <p><strong>Pays:</strong> <?= htmlspecialchars($user['country'] ?? 'N/A') ?></p>
    <p><strong>Rôle:</strong> <?= htmlspecialchars($user['role']) ?></p>
    
    <a href="edit_user.php?id=<?= $user['iduser'] ?>">Modifier</a> |
    <a href="list_users.php">Retour à la liste</a>
</body>
</html>
