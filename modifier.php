<?php
require_once 'connexion.php';
$message = '';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id_capture = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['pecheur']) && !empty($_POST['espece']) && !empty($_POST['lieu']) && $_POST['taille'] > 0) {
        try {
            $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
            $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
            $id_lieu = $_POST['lieu'];

            // --- GESTION AUTOMATIQUE DU NOUVEAU LIEU TROUVÉ PAR LA CARTE ---
            if (strpos($id_lieu, 'NEW:') === 0) {
                $nom_nouveau_lieu = trim(substr($id_lieu, 4));
                
                $stmt_check = $pdo->prepare("SELECT id_lieu FROM LIEU WHERE LOWER(nom_lieu) = LOWER(?)");
                $stmt_check->execute(array($nom_nouveau_lieu));
                $existing = $stmt_check->fetch();
                
                if ($existing) {
                    $id_lieu = $existing['id_lieu'];
                } else {
                    $stmt_ins = $pdo->prepare("INSERT INTO LIEU (nom_lieu) VALUES (?)");
                    $stmt_ins->execute(array($nom_nouveau_lieu));
                    $id_lieu = $pdo->lastInsertId();
                }
            }

            $sql = "UPDATE CAPTURE SET date_capture = ?, poids = ?, taille = ?, id_pecheur = ?, id_lieu = ?, id_espece = ?, latitude = ?, longitude = ? 
                    WHERE id_capture = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                $_POST['date'], 
                $_POST['poids'], 
                $_POST['taille'], 
                $_POST['pecheur'], 
                $id_lieu, 
                $_POST['espece'],
                $latitude,
                $longitude,
                $id_capture
            ));
            
            header('Location: index.php');
            exit;
        } catch (\PDOException $e) {
            $message = "Erreur SQL : " . $e->getMessage();
        }
    }
}

$stmt_capture = $pdo->prepare("SELECT * FROM CAPTURE WHERE id_capture = ?");
$stmt_capture->execute(array($id_capture));
$capture = $stmt_capture->fetch();

if (!$capture) { die("Capture introuvable."); }

$pecheurs = $pdo->query("SELECT id_pecheur, prenom, nom FROM PECHEUR ORDER BY prenom ASC")->fetchAll();
$lieux = $pdo->query("SELECT id_lieu, nom_lieu FROM LIEU ORDER BY nom_lieu ASC")->fetchAll();
$especes = $pdo->query("SELECT id_espece, nom_espece FROM ESPECE ORDER BY nom_espece ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Capture</title>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        :root { --primary: #2c3e50; --warning: #f39c12; --bg: #f4f7f6; }
        body { font-family: sans-serif; background-color: var(--bg); margin: 0; }
        .header { background-color: var(--primary); color: white; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { margin: 0; font-size: 24px; }
        nav a { color: #b4c6d8; text-decoration: none; margin-left: 20px; font-weight: 500; }
        nav a:hover { color: white; }
        .form-container { background: white; padding: 30px; border-radius: 8px; max-width: 550px; margin: 40px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }
        input[type="submit"] { background-color: var(--warning); color: white; border: none; font-weight: bold; cursor: pointer; margin-top: 10px;}
        
        #map-picker { height: 250px; width: 100%; border-radius: 6px; margin-bottom: 20px; border: 1px solid #ccc; }
        .map-help { font-size: 12px; color: #7f8c8d; margin-top: -15px; margin-bottom: 20px; display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎣 Carnet de Pêche</h1>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="ajouter.php">Déclarer une prise</a>
            <a href="admin.php">Administration</a>
        </nav>
    </div>
    <div class="form-container">
        <h2>Modifier la capture n°<?php echo $id_capture; ?></h2>
        <?php if (!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
        
        <form action="modifier.php?id=<?php echo $id_capture; ?>" method="POST">
            <label>Pêcheur *</label>
            <select name="pecheur" required>
                <?php foreach($pecheurs as $p): ?>
                    <option value="<?php echo $p['id_pecheur']; ?>" <?php if($p['id_pecheur'] == $capture['id_pecheur']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($p['prenom'] . " " . $p['nom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Espèce *</label>
            <select name="espece" required>
                <?php foreach($especes as $e): ?>
                    <option value="<?php echo $e['id_espece']; ?>" <?php if($e['id_espece'] == $capture['id_espece']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($e['nom_espece']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Lieu global *</label>
            <select name="lieu" id="lieu-select" required>
                <?php foreach($lieux as $l): ?>
                    <option value="<?php echo $l['id_lieu']; ?>" <?php if($l['id_lieu'] == $capture['id_lieu']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($l['nom_lieu']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="form-row">
                <div class="form-group">
                    <label>Taille (cm) *</label>
                    <input type="number" name="taille" step="0.1" value="<?php echo htmlspecialchars($capture['taille']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Poids (g)</label>
                    <input type="number" name="poids" value="<?php echo htmlspecialchars($capture['poids']); ?>">
                </div>
            </div>

            <label>Emplacement précis sur la carte</label>
            <div id="map-picker"></div>
            <span class="map-help">💡 Cliquez à un autre endroit si vous souhaitez modifier l'emplacement et le lieu global associés.</span>

            <div class="form-row">
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" name="latitude" id="latitude" step="any" value="<?php echo htmlspecialchars($capture['latitude']); ?>" readonly style="background:#eee; cursor:not-allowed;">
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" name="longitude" id="longitude" step="any" value="<?php echo htmlspecialchars($capture['longitude']); ?>" readonly style="background:#eee; cursor:not-allowed;">
                </div>
            </div>

            <label>Date *</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($capture['date_capture']); ?>" required>
            
            <input type="submit" value="Enregistrer les modifications">
        </form>
    </div>

    <script>
        var initialLat = <?php echo !empty($capture['latitude']) ? $capture['latitude'] : 49.12; ?>;
        var initialLng = <?php echo !empty($capture['longitude']) ? $capture['longitude'] : -0.15; ?>;
        var hasCoords = <?php echo (!empty($capture['latitude']) && !empty($capture['longitude'])) ? 'true' : 'false'; ?>;

        var map = L.map('map-picker').setView([initialLat, initialLng], hasCoords ? 13 : 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker;
        if (hasCoords) { marker = L.marker([initialLat, initialLng]).addTo(map); }

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);

            if (marker) { marker.setLatLng(e.latlng); } else { marker = L.marker(e.latlng).addTo(map); }

            // --- RECHERCHE INTELLIGENTE DU NOM DE LIEU ---
            var selectLieu = document.getElementById('lieu-select');
            
            let oldDynamic = document.getElementById('dynamic-lieu');
            if (oldDynamic) oldDynamic.remove();
            
            let loadingOpt = document.createElement('option');
            loadingOpt.id = 'dynamic-lieu';
            loadingOpt.text = "🔍 Recherche du nom de l'endroit...";
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
                    if(dyn) dyn.text = "-- Sélectionner un lieu --";
                });
        });
    </script>
</body>
</html>