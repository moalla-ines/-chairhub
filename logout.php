<?php
session_start();

// Effacer les données de session spécifiques
unset($_SESSION['user_id']);
unset($_SESSION['role']);
// Ou pour tout effacer :
// session_destroy();

// Rediriger sans rafraîchir la page actuelle
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>