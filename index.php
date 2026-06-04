<?php
require_once 'connexion.php';
require_once 'auth_check.php';

// Sélection de toutes les captures avec leurs coordonnées GPS
$stmt = $pdo->query('SELECT c.id_capture, c.date_capture, c.poids, c.taille, c.latitude, c.longitude, p.prenom, l.nom_lieu, e.nom_espece 
                     FROM CAPTURE c
                     JOIN PECHEUR p ON c.id_pecheur = p.id_pecheur
                     JOIN LIEU l ON c.id_lieu = l.id_lieu
                     JOIN ESPECE e ON c.id_espece = e.id_espece
                     ORDER BY c.date_capture DESC');
$captures = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carnet de Pêche Communautaire</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        :root {
            --primary: #2c3e50;
            --accent: #2980b9;
            --danger: #e74c3c;
            --bg: #f5f7fa;
            --text: #34495e;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: var(--bg); color: var(--text); margin: 0; padding: 0; }
        .header { background-color: var(--primary); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header h1 { margin: 0; font-size: 24px; }
        nav a { color: #b4c6d8; text-decoration: none; margin-left: 20px; font-weight: 500; transition: color 0.2s; }
        nav a:hover, nav a.active { color: white; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        /* Style de la grande carte */
        #map-global { height: 400px; width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px; border: 1px solid #ddd; }
        
        /* Style du tableau modernisé */
        .table-container { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background-color: var(--primary); color: white; padding: 15px 20px; font-weight: 600; }
        td { padding: 15px 20px; border-bottom: 1px solid #eee; }
        tr:hover { background-color: #fcfdfd; }
        
        .btn-delete { color: var(--danger); text-decoration: none; font-weight: bold; }
        .btn-delete:hover { text-decoration: underline; }
        .btn-edit { color: var(--accent); text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-edit:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="header">
        <h1>🎣 Carnet de Pêche Communautaire</h1>
        <nav>
            <a href="index.php" class="active">Accueil</a>
            <a href="ajouter.php">Déclarer une prise</a>
            <?php if (isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1): ?>
                <a href="admin.php">Administration</a>
            <?php endif; ?>
            <a href="deconnexion.php" style="color: #e74c3c;">Déconnexion</a>
        </nav>
    </div>

    <div class="container">
        <h2>🗺️ Carte des prises de l'association</h2>
        <div id="map-global"></div>

        <h2>📋 Dernières Prises de l'Association</h2>
        <div class="table-container">
            <table>
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
                    <?php foreach ($captures as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['prenom']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nom_espece']) ?></td>
                            <td><?= htmlspecialchars($row['taille']) ?> cm</td>
                            <td><?= $row['poids'] ? htmlspecialchars($row['poids'])." g" : '-' ?></td>
                            <td>📍 <?= htmlspecialchars($row['nom_lieu']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['date_capture'])) ?></td>
                            <td>
                                <a class="btn-edit" href="modifier.php?id=<?= $row['id_capture'] ?>">Modifier</a>
                                <a class="btn-delete" href="supprimer.php?id=<?= $row['id_capture'] ?>" onclick="return confirm('Supprimer cette prise ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        var map = L.map('map-global').setView([49.12, -0.15], 8);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var captures = <?= json_encode($captures); ?>;
        var markerBounds = [];

        captures.forEach(function(c) {
            if (c.latitude && c.longitude) {
                var lat = parseFloat(c.latitude);
                var lng = parseFloat(c.longitude);
                markerBounds.push([lat, lng]);

                var popupContent = `
                    <div style="font-family: sans-serif; font-size: 13px;">
                        <strong style="color: #2c3e50; font-size: 14px;">${c.prenom}</strong> a pêché :<br>
                        🐟 <strong>${c.nom_espece}</strong> de <strong>${c.taille} cm</strong><br>
                        ${c.poids ? '⚖️ Poids : ' + c.poids + ' g<br>' : ''}
                        📍 Lieu : ${c.nom_lieu}<br>
                        📅 Date : ${c.date_capture}
                    </div>
                `;

                L.marker([lat, lng]).addTo(map).bindPopup(popupContent);
            }
        });

        if (markerBounds.length > 0) {
            map.fitBounds(markerBounds, { padding: [40, 40] });
        }
    </script>
</body>
</html>