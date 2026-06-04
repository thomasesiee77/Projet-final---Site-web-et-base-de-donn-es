<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si l'utilisateur n'est pas connecté, redirection immédiate vers la page de connexion
if (!isset($_SESSION['id_pecheur'])) {
    header('Location: connexion_user.php');
    exit;
}
?>