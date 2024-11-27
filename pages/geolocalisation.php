<?php
include('header.php'); // Inclure l'en-tête, qui contient la connexion à la base de données

// Récupérer les positions de la base de données avec PDO
$sql = "SELECT 
    p.position_id,
    p.engin_id,
    p.latitude,
    p.longitude,
    p.vitesse,
    p.direction,
    p.etat_mouvement,
    p.precision,
    p.date_enregistrement,
    e.marque, 
    e.plaque_immatriculation,
    CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet
FROM position p
JOIN engins e ON p.engin_id = e.engin_id
JOIN utilisateurs u ON p.utilisateur_id = u.id
WHERE p.date_enregistrement = (
    SELECT MAX(date_enregistrement) 
    FROM position 
    WHERE engin_id = p.engin_id
)";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Vérifier s'il y a des résultats
$positions = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $positions[] = [
        'latitude' => $row['latitude'],
        'longitude' => $row['longitude'],
        'etat_mouvement' => $row['etat_mouvement'],
        'utilisateur_nom_complet' => $row['utilisateur_nom_complet'],
        'plaque_immatriculation' => $row['plaque_immatriculation']
    ];
}

if (empty($positions)) {
    echo "Aucune position trouvée";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carte Professionnelle - Positions</title>

  <!-- Inclure le CSS de Leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/leaflet.markercluster.min.css" />

  <style>
    /* Style pour le conteneur de la carte */
    #map {
      height: 100vh; /* La carte occupe toute la hauteur de la fenêtre */
      width: 100%;   /* La carte occupe toute la largeur de la fenêtre */
    }
  </style>
</head>
<body>

  <!-- Conteneur de la carte -->
  <div id="map"></div>

  <!-- Inclure le JavaScript de Leaflet et du clustering -->
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.1/leaflet.markercluster.min.js"></script>
  <script>
    // Initialiser la carte Leaflet avec les coordonnées d'Abidjan
    var map = L.map('map').setView([5.3453, -4.0244], 12); // Abidjan (coordonnées : [latitude, longitude])

    // Ajouter la couche de tuiles OpenStreetMap avec un fond plus sombre
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Créer un groupe pour le clustering des marqueurs
    var markers = L.markerClusterGroup();

    // Tableau PHP avec les positions
    var positions = <?php echo json_encode($positions); ?>;

    // Ajouter un marqueur pour chaque position récupérée depuis la base de données
    positions.forEach(function(position) {
      var lat = position.latitude;
      var lon = position.longitude;
      var etat = position.etat_mouvement;
      var utilisateur = position.utilisateur_nom_complet;
      var plaque = position.plaque_immatriculation;

      // Créer un icône par défaut si aucun icône personnalisé n'est trouvé
      var customIcon = L.icon({
        iconUrl: 'https://unpkg.com/leaflet/dist/images/marker-icon.png',  // Icône par défaut de Leaflet
        iconSize: [32, 32],  // Taille de l'icône
        iconAnchor: [16, 32], // Ancrage de l'icône
        popupAnchor: [0, -32] // Ancrage du popup
      });

      // Ajouter le marqueur au groupe avec un popup détaillé
      var marker = L.marker([lat, lon], { icon: customIcon })
        .bindPopup('<div style="font-size:16px;"><strong>' + utilisateur + '</strong><br>' +
                   'État: ' + etat + '<br>' +
                   'Plaque: ' + plaque + '<br>' +
                   '<a href="détails/' + position.engin_id + '">Voir plus</a></div>');

      markers.addLayer(marker);
    });

    // Ajouter les marqueurs au cluster
    map.addLayer(markers);

    // Ajouter le contrôle de l'échelle de la carte
    L.control.scale().addTo(map);
  </script>

</body>
</html>
