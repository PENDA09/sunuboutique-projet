<?php
// Configuration Sunuboutique - Version corrigée
$host = 'sql206.infinityfree.com'; 
$dbname = 'if0_41075398_sunuboutiquedatabase';
$username = 'if0_41075398';
$password = 'jmwbTyVWx7';

try {
    // Connexion avec l'hôte nettoyé
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optionnel : décommentez la ligne suivante pour tester si ça marche
    // echo "Bravo ! La connexion est établie.";

} catch (PDOException $e) {
    // Affiche l'erreur précise si ça échoue encore
    die("Erreur de connexion : " . $e->getMessage());
}
?>