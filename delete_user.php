<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    header("Location: list_users.php");
    exit();
}

$id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE iduser = ?");
    $stmt->execute([$id]);
    
    header("Location: list_users.php?success=1");
    exit();
} catch (PDOException $e) {
    die("Erreur lors de la suppression : " . $e->getMessage());
}
?>