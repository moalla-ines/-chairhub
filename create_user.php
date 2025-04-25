<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'] ?? null;
    $country = $_POST['country'] ?? null;
    $role = $_POST['role'] ?? 'user';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, password, email, phone, country, role) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $password, $email, $phone, $country, $role]);
        
        header("Location: list_users.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Erreur lors de la création : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
</head>
<body>
    <h1>Ajouter un utilisateur</h1>
    
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Nom complet:</label>
            <input type="text" name="name" required>
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Mot de passe:</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Téléphone:</label>
            <input type="text" name="phone">
        </div>
        <div>
            <label>Pays:</label>
            <input type="text" name="country">
        </div>
        <div>
            <label>Rôle:</label>
            <select name="role">
                <option value="user">Utilisateur</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>
        <button type="submit">Créer</button>
    </form>
    <a href="list_users.php">Retour à la liste</a>
</body>
</html>