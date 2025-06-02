<?php
require_once 'config.php';

// Vérifier que la connexion PDO existe
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Erreur de connexion à la base de données");
}

// Vérifier que l'ID est présent et numérique
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: liste_users.php?error=invalid_id");
    exit();
}

$user_id = (int)$_GET['id'];

try {
    // Vérifier que l'utilisateur existe avant suppression
    $check_stmt = $pdo->prepare("SELECT iduser FROM users WHERE iduser = ?");
    $check_stmt->execute([$user_id]);
    
    if ($check_stmt->rowCount() === 0) {
        header("Location: liste_users.php?error=user_not_found");
        exit();
    }

    // Suppression de l'utilisateur
    $delete_stmt = $pdo->prepare("DELETE FROM users WHERE iduser = ?");
    $delete_stmt->execute([$user_id]);
    
    if ($delete_stmt->rowCount() > 0) {
        header("Location: liste_users.php?success=user_deleted");
    } else {
        header("Location: liste_users.php?error=delete_failed");
    }
    exit();
    
} catch (PDOException $e) {
    error_log("Delete user error: " . $e->getMessage());
    header("Location: liste_users.php?error=database_error");
    exit();
}
?>