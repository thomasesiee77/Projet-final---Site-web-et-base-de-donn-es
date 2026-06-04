<?php
require_once 'connexion.php';
require_once 'auth_check.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pecheur = $_POST['pecheur'];
    $id_espece = $_POST['espece'];
    $id_lieu = $_POST['lieu'];
    $taille = $_POST['taille'];
    $poids = !empty($_POST['poids']) ? $_POST['poids'] : null;
    $date = $_POST['date'];
    $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;

    if (!empty($id_pecheur) && !empty($id_espece) && !empty($id_lieu) && $taille > 0) {
        try {
            // Création automatique si l'API a détecté un nouveau lieu global
            if (strpos($id_lieu, 'NEW:') === 0) {
                $nom_nouveau_lieu = trim(substr($id_lieu, 4));
                
                $stmt_check = $pdo->prepare("SELECT id_lieu FROM LIEU WHERE LOWER(nom_lieu) = LOWER(?)");
                $stmt_check->execute([$nom_nouveau_lieu]);
                $existing = $stmt_check->fetch();
                
                if ($existing) {
                    $id_lieu = $existing['id_lieu'];
                } else {
                    $stmt_ins = $pdo->prepare("INSERT INTO LIEU (nom_lieu) VALUES (?)");
                    $stmt_ins->execute([$nom_nouveau_lieu]);
                    $id_lieu = $pdo->lastInsertId();
                }
            }

            $sql = "INSERT INTO CAPTURE (date_capture, poids, taille, id_pecheur, id_lieu, id_espece, latitude, longitude) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date, $poids, $taille, $id_pecheur, $id_lieu, $id_espece, $latitude, $longitude]);

            header('Location: index.php');
            exit;
        } catch (\PDOException $e) {
            $message = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}

$pecheurs = $pdo->query("SELECT id_pecheur, prenom, nom FROM PECHEUR ORDER BY prenom ASC")->fetchAll();
$lieux = $pdo->query("SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu ASC")->fetchAll();
$especes = $pdo->query("SELECT id_espece, nom_espece FROM ESPECE ORDER BY nom_espece ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer une Capture</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        :root {
            --primary: #2c3e50;
            --success: #2ecc71;
            --bg: #f4f7f6;
        }
        body { font-family: sans-serif; background-color: var(--bg); margin: 0; padding: 0; }
        .header { background-color: var(--primary); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 24px; }
        nav a { color: #b4c6d8; text-decoration: none; margin-left: 20px; font-weight: 500; }
        nav a:hover, nav a.active { color: white; }
        
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 550px; margin: 40px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-container h2 { margin-top: 0; color: var(--primary); }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        
        input[type="submit"] { background-color: var(--success); color: white; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px; padding: 12px; }
        input[type="submit"]:hover { background-color: #27ae60; }
        
        #map-picker { height: 250px; width: 100%; border-radius: 6px; margin-bottom: 5px; border: 1px solid #ccc; }
        .map-help { font-size: 12px; color: #7f8c8d; display: block; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>🎣 Carnet de Pêche</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter.php" class="active">Déclarer une prise</a>
            <?php if (isset($_SESSION['est_admin']) && $_SESSION['est_admin'] == 1): ?>
                <a href="admin.php">Administration</a>
            <?php endif; ?>
            <a href="deconnexion.php" style="color: #e74c3c;">Déconnexion</a>
        </nav>
    </div>

    <div class="form-container">
        <h2>Déclarer un nouveau poisson</h2>
        <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>

        <form action="ajouter.php" method="POST">
            <label>Pêcheur *</label>
            <select name="pecheur" required>
                <option value="">-- Sélectionner un pêcheur --</option>
                <?php foreach($pecheurs as $p): ?>
                    <option value="<?= $p['id_pecheur'] ?>"><?= htmlspecialchars($p['prenom']." " . $p['nom']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Espèce *</label>
            <select name="espece" required>
                <option value="">-- Sélectionner une espèce --</option>
                <?php foreach($especes as $e): ?>
                    <option value="<?= $e['id_espece'] ?>"><?= htmlspecialchars($e['nom_espece']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Lieu de capture (Lieu Global) *</label>
            <select name="lieu" id="lieu-select" required>
                <option value="">-- Choisir ou cliquer sur la carte --</option>
                <?php foreach($lieux as $l): ?>
                    <option value="<?= $l['id_lieu'] ?>"><?= htmlspecialchars($l['nom_lieu']) ?></option>
                <?php endforeach; ?>
            </select>

            <div class="form-row">
                <div class="form-group">
                    <label>Taille (cm) *</label>
                    <input type="number" name="taille" step="0.1" required>
                </div>
                <div class="form-group">
                    <label>Poids (g)</label>
                    <input type="number" name="poids">
                </div>
            </div>

            <label>Emplacement précis (Cliquez sur la carte)</label>
            <div id="map-picker"></div>
            <span class="map-help">💡 Le clic remplit automatiquement la Latitude, la Longitude et trouve le nom du lieu !</span>

            <div class="form-row">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" name="latitude" id="latitude" step="any" readonly style="background:#eee; cursor:not-allowed;">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" name="longitude" id="longitude" step="any" readonly style="background:#eee; cursor:not-allowed;">
                </div>
            </div>

            <label>Date *</label>
            <input type="date" name="date" value="<?= date('Y-m-d'); ?>" required>

            <input type="submit" value="Enregistrer la prise">
        </form>
    </div>

    <script>
        var map = L.map('map-picker').setView([49.12, -0.15], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker;

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);

            if (marker) { marker.setLatLng(e.latlng); } else { marker = L.marker(e.latlng).addTo(map); }

            var selectLieu = document.getElementById('lieu-select');
            
            let oldDynamic = document.getElementById('dynamic-lieu');
            if (oldDynamic) oldDynamic.remove();
            
            let loadingOpt = document.createElement('option');
            loadingOpt.id = 'dynamic-lieu';
            loadingOpt.text = "🔍 Recherche de l'emplacement...";
            loadingOpt.value = "";
            loadingOpt.selected = true;
            selectLieu.add(loadingOpt);

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.address) {
                        var a = data.address;
                        var nomTrouve = a.waterway || a.river || a.lake || a.body_of_water || a.natural || a.village || a.town || a.city || "Lieu inconnu";
                        
                        document.getElementById('dynamic-lieu').remove();

                        var existeDeja = false;
                        for (var i = 0; i < selectLieu.options.length; i++) {
                            if (selectLieu.options[i].text.toLowerCase() === nomTrouve.toLowerCase()) {
                                selectLieu.selectedIndex = i;
                                existeDeja = true;
                                break;
                            }
                        }

                        if (!existeDeja) {
                            let newOpt = document.createElement('option');
                            newOpt.id = 'dynamic-lieu';
                            newOpt.value = "NEW:" + nomTrouve;
                            newOpt.text = "📍 " + nomTrouve + " (Nouveau lieu détecté)";
                            newOpt.selected = true;
                            selectLieu.add(newOpt);
                        }
                    }
                })
                .catch(err => {
                    let dyn = document.getElementById('dynamic-lieu');
                    if(dyn) dyn.text = "-- Choisir ou cliquer sur la carte --";
                });
        });
    </script>
</body>
</html>