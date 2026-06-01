<?php
require_once 'connexion.php'; // <-- Connexion centralisée

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pecheur = $_POST['pecheur'];
    $id_espece = $_POST['espece'];
    $id_lieu = $_POST['lieu'];
    $taille = $_POST['taille'];
    $poids = $_POST['poids'];
    $date = $_POST['date'];

    if (!empty($id_pecheur) && !empty($id_espece) && !empty($id_lieu) && $taille > 0) {
        $sql = "INSERT INTO CAPTURE (date_capture, poids, taille, id_pecheur, id_lieu, id_espece) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$date, $poids, $taille, $id_pecheur, $id_lieu, $id_espece]);

        header('Location: index.php');
        exit;
    }
}

// Récupération des données pour remplir les listes déroulantes
$pecheurs = $pdo->query("SELECT id_pecheur, prenom, nom FROM PECHEUR")->fetchAll();
$lieux = $pdo->query("SELECT id_lieu, nom_lieu FROM LIEU")->fetchAll();
$especes = $pdo->query("SELECT id_espece, nom_espece FROM ESPECE")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer une Capture</title>
</head>
<body>
    <h1>Déclarer un nouveau poisson</h1>
    <form action="ajouter.php" method="POST">
        <label>Pêcheur :</label>
        <select name="pecheur" required>
            <?php foreach($pecheurs as $p): ?>
                <option value="<?= $p['id_pecheur'] ?>"><?= htmlspecialchars($p['prenom']." ".$p['nom']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Espèce :</label>
        <select name="espece" required>
            <?php foreach($especes as $e): ?>
                <option value="<?= $e['id_espece'] ?>"><?= htmlspecialchars($e['nom_espece']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Lieu de capture :</label>
        <select name="lieu" required>
            <?php foreach($lieux as $l): ?>
                <option value="<?= $l['id_lieu'] ?>"><?= htmlspecialchars($l['nom_lieu']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Taille (en cm) :</label>
        <input type="number" name="taille" required><br><br>

        <label>Poids (en grammes) :</label>
        <input type="number" name="poids" required><br><br>

        <label>Date :</label>
        <input type="date" name="date" required><br><br>

        <input type="submit" value="Enregistrer la prise">
    </form>
    <br>
    <a href="index.php">Retour à l'accueil</a>
</body>
</html>