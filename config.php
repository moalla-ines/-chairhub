<?php
// config.php
$host = 'localhost';
$dbname = 'e_commerce';
$username = 'root';
$password = '';

try {
    $db = new mysqli($host, $username, $password, $dbname);
    
    // Vérifier la connexion
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    
    // Définir le charset
    $db->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>