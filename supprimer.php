<?php
require_once 'connexion.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM CAPTURE WHERE id_capture = ?');
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;
?>