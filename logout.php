<?php
session_start();

// Effacer les données de session spécifiques
unset($_SESSION['user_id']);
unset($_SESSION['role']);

// Supprimer toutes les données de session
$_SESSION = array();

// Détruire le cookie de session, pour être sûr que les informations ne restent pas dans le navigateur
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger l'utilisateur vers la page d'accueil après la déconnexion
header("Location: index.php");
exit;
?>
