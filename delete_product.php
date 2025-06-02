<?php
// delete_product.php - Version ultra-simplifiée

// 1. Démarrer la session
session_start();

// 2. Inclure la connexion à la DB
require 'config.php';

// 3. Vérifier si admin (version basique)
if ($_SESSION['role'] !== 'admin') {
    die("Accès refusé. Vous devez être administrateur.");
}

// 4. Récupérer l'ID du produit
$product_id = (int)$_GET['id'];

// 5. Exécuter la suppression
$pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$product_id]);

// 6. Rediriger avec message
$_SESSION['message'] = "Produit #$product_id supprimé";
header("Location: products.php");
exit();