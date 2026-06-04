<?php
require_once 'connexion.php';
$message = '';
$type_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($password)) {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id_pecheur FROM PECHEUR WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $message = "Cette adresse email est déjà utilisée.";
            $type_message = "error";
        } else {
            // Hachage sécurisé du mot de passe
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            try {
                $stmt_ins = $pdo->prepare("INSERT INTO PECHEUR (nom, prenom, email, mot_de_passe, est_valide, est_admin) VALUES (?, ?, ?, ?, 0, 0)");
                $stmt_ins->execute([$nom, $prenom, $email, $password_hash]);

                // --- ENVOI DE L'EMAIL À L'ADMINISTRATEUR ---
                $to = "votre-email-admin@exemple.com"; // ⚠️ METTEZ VOTRE VRAI EMAIL ICI
                $subject = "[Carnet de Pêche] Nouvelle demande d'inscription en attente";
                $email_message = "Bonjour,\n\nUne nouvelle demande d'inscription a été effectuée sur le Carnet de Pêche :\n\n";
                $email_message .= "Nom : $nom\nPrénom : $prenom\nEmail : $email\n\n";
                $email_message .= "Connectez-vous à l'espace Administration de votre site pour accepter ou refuser ce membre.";
                
                $headers = "From: no-reply@alwaysdata.net\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";

                @mail($to, $subject, $email_message, $headers);

                $message = "Votre demande d'inscription a bien été envoyée ! Elle est en attente de validation par l'administrateur.";
                $type_message = "success";
            } catch (\PDOException $e) {
                $message = "Erreur lors de l'inscription : " . $e->getMessage();
                $type_message = "error";
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $type_message = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Carnet de Pêche</title>
    <style>
        body { font-family: sans-serif; background-color: #f5f7fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #2c3e50; margin-top: 0; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        input[type="submit"] { background-color: #2980b9; color: white; border: none; font-weight: bold; cursor: pointer; font-size: 16px; margin-top: 10px; }
        input[type="submit"]:hover { background-color: #2171a3; }
        .msg { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: 500; }
        .msg.error { background-color: #fde8e8; color: #e74c3c; }
        .msg.success { background-color: #eafaf1; color: #2ecc71; }
        .link { text-align: center; display: block; margin-top: 15px; color: #7f8c8d; text-decoration: none; }
        .link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Créer un compte</h2>
        
        <?php if (!empty($message)): ?>
            <div class="msg <?= $type_message ?>"><?= $message ?></div>
        <?php endif; ?>

        <form action="inscription.php" method="POST">
            <label>Prénom</label>
            <input type="text" name="prenom" required>

            <label>Nom</label>
            <input type="text" name="nom" required>

            <label>Adresse Email</label>
            <input type="email" name="email" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <input type="submit" value="Faire la demande d'accès">
        </form>
        <a class="link" href="connexion_user.php">Déjà inscrit ? Se connecter</a>
    </div>
</body>
</html>