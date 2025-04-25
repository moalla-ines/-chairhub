<?php
require_once 'config.php';

// Vérifier que la connexion existe
if (!isset($db) || !($db instanceof mysqli)) {
    die("Erreur de connexion à la base de données");
}

// Vérifier que l'ID est présent et numérique
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: liste_users.php?error=invalid_id");
    exit();
}

$user_id = (int)$_GET['id'];

// Vérifier que l'utilisateur existe avant suppression
$check_stmt = $db->prepare("SELECT iduser FROM users WHERE iduser = ?");
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header("Location: liste_users.php?error=user_not_found");
    exit();
}

// Suppression de l'utilisateur
try {
    $delete_stmt = $db->prepare("DELETE FROM users WHERE iduser = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();
    
    if ($delete_stmt->affected_rows > 0) {
        header("Location: liste_users.php?success=user_deleted");
    } else {
        header("Location: liste_users.php?error=delete_failed");
    }
    exit();
    
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    header("Location: liste_users.php?error=database_error");
    exit();
}
?>