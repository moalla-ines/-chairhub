<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: list_users.php");
    exit();
}

$id = $_GET['id'];

// Récupération de l'utilisateur existant
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null;
    $country = $_POST['country'] ?? null;
    $role = $_POST['role'];
    
    // Gestion du mot de passe (seulement si modifié)
    $password = $user['password'];
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, 
                              phone = ?, country = ?, role = ? WHERE iduser = ?");
        $stmt->execute([$name, $email, $password, $phone, $country, $role, $id]);
        
        header("Location: list_users.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'utilisateur</title>
</head>
<body>
    <h1>Modifier l'utilisateur</h1>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Nom complet:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div>
            <label>Nouveau mot de passe (laisser vide pour ne pas changer):</label>
            <input type="password" name="password">
        </div>
        <div>
            <label>Téléphone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        </div>
        <div>
            <label>Pays:</label>
            <input type="text" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
        </div>
        <div>
            <label>Rôle:</label>
            <select name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
            </select>
        </div>
        <button type="submit">Mettre à jour</button>
    </form>
    <a href="list_users.php">Retour à la liste</a>
</body>
</html>