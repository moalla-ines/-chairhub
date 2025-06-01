<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

$user_id = intval($_SESSION['iduser']);
$result = mysqli_query($db, "SELECT role FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($result);

if ($user['role'] !== 'admin') {
    die("Accès non autorisé.");
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash_message'] = "Produit supprimé avec succès.";
    header("Location: product.php");
    exit();
}
?>
