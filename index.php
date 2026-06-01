<?php
require_once 'connexion.php'; // <-- Connexion centralisée

// Requête avec jointures pour récupérer les détails de la capture
$stmt = $pdo->query('SELECT c.id_capture, c.date_capture, c.poids, c.taille, p.prenom, l.nom_lieu, e.nom_espece 
                     FROM CAPTURE c
                     JOIN PECHEUR p ON c.id_pecheur = p.id_pecheur
                     JOIN LIEU l ON c.id_lieu = l.id_lieu
                     JOIN ESPECE e ON c.id_espece = e.id_espece
                     ORDER BY c.date_capture DESC');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carnet de Pêche Communautaire</title>
</head>
<body>
    <h1>Dernières Prises de l'Association</h1>
    <nav>
        <a href="index.php">Accueil</a> | <a href="ajouter.php">Déclarer une prise</a>
    </nav>
    <hr>

    <table border="1">
        <thead>
            <tr>
                <th>Pêcheur</th>
                <th>Espèce</th>
                <th>Taille (cm)</th>
                <th>Poids (g)</th>
                <th>Lieu</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['prenom']) ?></td>
                    <td><?= htmlspecialchars($row['nom_espece']) ?></td>
                    <td><?= htmlspecialchars($row['taille']) ?></td>
                    <td><?= htmlspecialchars($row['poids']) ?></td>
                    <td><?= htmlspecialchars($row['nom_lieu']) ?></td>
                    <td><?= htmlspecialchars($row['date_capture']) ?></td>
                    <td>
                        <a href="supprimer.php?id=<?= $row['id_capture'] ?>" onclick="return confirm('Supprimer cette prise ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>