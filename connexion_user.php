<?php
require_once 'connexion.php';
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM PECHEUR WHERE email = ?");
        $stmt->execute([$email]);
        $pecheur = $stmt->fetch();

        if ($pecheur && password_verify($password, $pecheur['mot_de_passe'])) {
            if ($pecheur['est_valide'] == 1) {
                $_SESSION['id_pecheur'] = $pecheur['id_pecheur'];
                $_SESSION['prenom'] = $pecheur['prenom'];
                $_SESSION['nom'] = $pecheur['nom'];
                $_SESSION['est_admin'] = $pecheur['est_admin'];

                header('Location: index.php');
                exit;
            } else {
                $message = "Votre compte est en attente de validation par l'administrateur.";
            }
        } else {
            $message = "Identifiants incorrects.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Carnet de Pêche</title>
    <style>
        body { font-family: sans-serif; background-color: #f5f7fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 350px; }
        h2 { color: #2c3e50; margin-top: 0; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { background-color: #2ecc71; color: white; border: none; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px; }
        input[type="submit"]:hover { background-color: #27ae60; }
        .error { background-color: #fde8e8; color: #e74c3c; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
        .link { text-align: center; display: block; margin-top: 15px; color: #7f8c8d; text-decoration: none; }
        .link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Connexion</h2>
        <?php if (!empty($message)) echo "<div class='error'>$message</div>"; ?>
        <form action="connexion_user.php" method="POST">
            <label>Adresse Email</label>
            <input type="email" name="email" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <input type="submit" value="Se connecter">
        </form>
        <a class="link" href="inscription.php">Pas encore de compte ? Faire une demande</a>
    </div>
</body>
</html>