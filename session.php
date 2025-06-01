<?php
// session.php

// Démarrer la session
session_start();

// Fonction pour connecter un utilisateur
function loginUser($user_id, $username, $role = 'user') {
    $_SESSION['iduser'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['last_activity'] = time();
}

// Fonction pour déconnecter un utilisateur
function logoutUser() {
    // Détruire toutes les données de session
    $_SESSION = array();
    
    // Si vous voulez détruire complètement la session, effacez également
    // le cookie de session.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Finalement, détruire la session
    session_destroy();
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Fonction pour vérifier le rôle de l'utilisateur
function checkUserRole($required_role) {
    if (!isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        return false;
    }
    return true;
}

// Fonction pour vérifier l'inactivité et déconnecter si nécessaire (timeout)
function checkSessionTimeout($timeout_minutes = 30) {
    if (isLoggedIn() && isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > $timeout_minutes * 60) {
            logoutUser();
            return false;
        }
        $_SESSION['last_activity'] = time(); // Mettre à jour le temps d'activité
    }
    return isLoggedIn();
}

// Vérifier le timeout de session à chaque chargement
checkSessionTimeout();

// Protection contre la fixation de session
function preventSessionFixation() {
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = true;
    }
}

// Appeler la protection contre la fixation
preventSessionFixation();
?>