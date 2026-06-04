<?php
require_once 'connexion.php';
require_once 'auth_check.php';

// SÉCURITÉ ABSOLUE : Bloque instantanément si l'utilisateur connecté n'est pas admin (est_admin != 1)
if (!isset($_SESSION['est_admin']) || $_SESSION['est_admin'] != 1) {
    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h1 style='color:#e74c3c;'>🛑 Accès refusé</h1>";
    echo "<p>Vous devez être administrateur pour accéder à cette page.</p>";
    echo "<a href='index.php' style='color:#2980b9; font-weight:bold; text-decoration:none;'>Retourner à l'accueil</a>";
    echo "</div>";
    exit;
}

$message_success = '';
$message_error = '';

// --- ACTIONS : GESTION DES INSCRIPTIONS MEMBRES ---
if (isset($_GET['action']) && $_GET['action'] === 'valider' && isset($_GET['id'])) {
    $id_valider = (int)$_GET['id'];
    $stmt = $pdo->prepare("UPDATE PECHEUR SET est_valide = 1 WHERE id_pecheur = ?");
    $stmt->execute([$id_valider]);
    header('Location: admin.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'refuser' && isset($_GET['id'])) {
    $id_refuser = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM PECHEUR WHERE id_pecheur = ? AND est_valide = 0");
    $stmt->execute([$id_refuser]);
    header('Location: admin.php');
    exit;
}

// --- ACTIONS : GESTION DES LIEUX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lieu'])) {
    $nom_lieu = trim($_POST['nom_lieu']);
    if (!empty($nom_lieu)) {
        $stmt = $pdo->prepare("INSERT INTO LIEU (nom_lieu) VALUES (?)");
        $stmt->execute([$nom_lieu]);
        $message_success = "Le lieu a bien été ajouté !";
    }
}
if (isset($_GET['action']) && $_GET['action'] === 'suppr_lieu' && isset($_GET['id'])) {
    try {
        $id_lieu = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM LIEU WHERE id_lieu = ?");
        $stmt->execute([$id_lieu]);
        header('Location: admin.php');
        exit;
    } catch (\PDOException $e) {
        $message_error = "Impossible de supprimer ce lieu car des poissons y ont été capturés.";
    }
}

// --- ACTIONS : GESTION DES ESPÈCES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_espece'])) {
    $nom_espece = trim($_POST['nom_espece']);
    if (!empty($nom_espece)) {
        $stmt = $pdo->prepare("INSERT INTO ESPECE (nom_espece) VALUES (?)");
        $stmt->execute([$nom_espece]);
        $message_success = "L'espèce a bien été ajoutée !";
    }
}
if (isset($_GET['action']) && $_GET['action'] === 'suppr_espece' && isset($_GET['id'])) {
    try {
        $id_espece = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM ESPECE WHERE id_espece = ?");
        $stmt->execute([$id_espece]);
        header('Location: admin.php');
        exit;
    } catch (\PDOException $e) {
        $message_error = "Impossible de supprimer cette espèce car elle est liée à des captures existantes.";
    }
}

// --- RÉCUPÉRATION DES LISTES ---
$demandes = $pdo->query("SELECT * FROM PECHEUR WHERE est_valide = 0 ORDER BY id_pecheur DESC")->fetchAll();
$lieux = $pdo->query("SELECT * FROM LIEU ORDER BY nom_lieu ASC")->fetchAll();
$especes = $pdo->query("SELECT * FROM ESPECE ORDER BY nom_espece ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration Complète - Carnet de Pêche</title>
    <style>
        :root { 
            --primary: #2c3e50; 
            --success: #2ecc71; 
            --danger: #e74c3c; 
            --accent: #2980b9;
        }
        body { font-family: 'Segoe UI', sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; }
        .header { background-color: var(--primary); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 24px; }
        nav a { color: #b4c6d8; text-decoration: none; margin-left: 20px; font-weight: 500; }
        nav a:hover { color: white; }
        
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .section { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px; }
        
        h2 { color: var(--primary); margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; font-weight: bold; color: var(--primary); }
        
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px; display: inline-block; }
        .btn-valider { background-color: var(--success); color: white; }
        .btn-valider:hover { background-color: #27ae60; }
        .btn-refuser { background-color: var(--danger); color: white; }
        .btn-refuser:hover { background-color: #c0392b; }
        
        .add-form { display: flex; gap: 10px; margin-top: 15px; }
        .add-form input[type="text"] { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .add-form input[type="submit"] { background-color: var(--accent); color: white; border: none; padding: 10px 20px; font-weight: bold; border-radius: 4px; cursor: pointer; }
        .add-form input[type="submit"]:hover { background-color: #2171a3; }
        
        .empty { text-align: center; color: #7f8c8d; padding: 15px; font-style: italic; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 20px; font-weight: bold; text-align: center; }
        .alert-success { background-color: #eafaf1; color: #2ecc71; }
        .alert-danger { background-color: #fde8e8; color: #e74c3c; }
    </style>
</head>
<body>

    <div class="header">
        <h1>🛠️ Espace Administration</h1>
        <nav>
            <a href="index.php">Retour au site</a>
            <a href="deconnexion.php" style="color:#e74c3c;">Déconnexion</a>
        </nav>
    </div>

    <div class="container">
        
        <?php if (!empty($message_success)): ?>
            <div class="alert alert-success"><?= $message_success ?></div>
        <?php endif; ?>
        <?php if (!empty($message_error)): ?>
            <div class="alert alert-danger"><?= $message_error ?></div>
        <?php endif; ?>

        <div class="section">
            <h2>👥 Demandes d'inscription en attente</h2>
            <?php if (empty($demandes)): ?>
                <p class="empty">Aucune demande d'inscription pour le moment.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Pêcheur</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demandes as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['prenom'] . " " . $d['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($d['email']) ?></td>
                                <td>
                                    <a class="btn btn-valider" href="admin.php?action=valider&id=<?= $d['id_pecheur'] ?>" onclick="return confirm('Accepter ce membre ?');">Accepter</a>
                                    <a class="btn btn-refuser" href="admin.php?action=refuser&id=<?= $d['id_pecheur'] ?>" onclick="return confirm('Refuser cette demande ?');">Refuser</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>📍 Gestion des Lieux de Pêche</h2>
            <form action="admin.php" method="POST" class="add-form">
                <input type="text" name="nom_lieu" placeholder="Nom du nouveau lieu (Ex: Étang de la Forge)" required>
                <input type="submit" name="add_lieu" value="Ajouter le lieu">
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Nom du Lieu</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lieux as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars($l['nom_lieu']) ?></td>
                            <td>
                                <a class="btn btn-refuser" style="padding: 4px 8px; font-size: 12px;" href="admin.php?action=suppr_lieu&id=<?= $l['id_lieu'] ?>" onclick="return confirm('Supprimer ce lieu ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>🐟 Gestion des Espèces de Poissons</h2>
            <form action="admin.php" method="POST" class="add-form">
                <input type="text" name="nom_espece" placeholder="Nouvelle espèce (Ex: Sandre)" required>
                <input type="submit" name="add_espece" value="Ajouter l'espèce">
            </form>
            <table>
                <thead>
                    <tr>
                        <th>Nom de l'espèce</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($especes as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['nom_espece']) ?></td>
                            <td>
                                <a class="btn btn-refuser" style="padding: 4px 8px; font-size: 12px;" href="admin.php?action=suppr_espece&id=<?= $e['id_espece'] ?>" onclick="return confirm('Supprimer cette espèce ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>