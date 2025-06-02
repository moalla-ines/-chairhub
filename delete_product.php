<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['iduser'])) {
    header('Location: login.php');
    exit();
}

// Verify PDO connection exists
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Erreur de connexion à la base de données");
}

$user_id = intval($_SESSION['iduser']);

try {
    // Check user role
    $stmt = $pdo->prepare("SELECT role FROM users WHERE iduser = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'admin') {
        die("Accès non autorisé.");
    }

    // Process product deletion
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $product_id = intval($_GET['id']);
        
        // Delete product
        $delete_stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->execute([$product_id]);
        
        $_SESSION['flash_message'] = "Produit supprimé avec succès.";
        header("Location: products.php");  // Changed from product.php to products.php for consistency
        exit();
    }
    
} catch (PDOException $e) {
    // Log error and show message
    error_log("Database error: " . $e->getMessage());
    $_SESSION['flash_message'] = "Erreur lors de la suppression du produit.";
    header("Location: products.php");
    exit();
}