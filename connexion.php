<?php
$host = 'mysql-dump-peche-ybert-delafollye-dougdad-perrot.alwaysdata.net';
$db   = 'dump-peche-ybert-delafollye-dougdad-perrot_1';
$user = '478856'; 
$pass = 'pecheesiee478856';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
// Utilisation de array() pour la compatibilité avec toutes les versions PHP
$options = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
);

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>