<?php
require_once 'connexion.php';

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);

    if (!empty($nom) && !empty($prenom)) {
        try {
            // Insertion sécurisée
            $sql = "INSERT INTO PECHEUR (nom, prenom) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            // Utilisation d'un array() classique pour éviter l'erreur de syntaxe
            $stmt->execute(array($nom, $prenom));
            
            $message = "Le pêcheur <strong>" . htmlspecialchars($prenom) . " " . htmlspecialchars($nom) . "</strong> a été ajouté avec succès !";
            $status = "success";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout : " . $e->getMessage();
            $status = "error";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Pêcheur</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f7f6; padding: 40px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        input { width: 100%; padding: 8px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Nouveau Pêcheur</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $status; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="ajouter_pecheur.php" method="POST">
        <label>Prénom :</label>
        <input type="text" name="prenom" required>
        
        <label>Nom :</label>
        <input type="text" name="nom" required>
        
        <input type="submit" value="Enregistrer">
    </form>

    <p><a href="index.php">Retour à l'accueil</a></p>
</div>

</body>
</html>