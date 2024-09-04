<?php
include('header.php');
require_once '../inc/functions/connexion.php';

// Fonction pour traduire les mois en français
function moisEnFrancais($moisAnglais) {
    $mois = array(
        'January' => 'Janvier',
        'February' => 'Février',
        'March' => 'Mars',
        'April' => 'Avril',
        'May' => 'Mai',
        'June' => 'Juin',
        'July' => 'Juillet',
        'August' => 'Août',
        'September' => 'Septembre',
        'October' => 'Octobre',
        'November' => 'Novembre',
        'December' => 'Décembre'
    );
    return isset($mois[$moisAnglais]) ? $mois[$moisAnglais] : $moisAnglais;
}

// Requête pour les statistiques des commandes
$sql = "SELECT 
    DATE_FORMAT(date_commande, '%Y') AS annee,
    DATE_FORMAT(date_commande, '%M') AS mois,
    MONTH(date_commande) AS mois_numero,
    SUM(recette) AS total_recette,
    SUM(depense) AS total_depense,
    SUM(recette) - SUM(depense) AS gain
FROM 
    points_livreurs
GROUP BY 
    annee, mois, mois_numero
ORDER BY 
    annee DESC, mois_numero DESC";

$requete = $conn->prepare($sql);
$requete->execute();

$dataPoints = array();

while ($row = $requete->fetch(PDO::FETCH_ASSOC)) {
    $dataPoints[] = array(
        'annee' => $row['annee'],
        'mois' => moisEnFrancais($row['mois']),
        'total_recette' => $row['total_recette'],
        'total_depense' => $row['total_depense'],
        'gain' => $row['gain']
    );
}

$jsonData = json_encode($dataPoints);

// Requête pour les boutiques avec le plus grand nombre de commandes
$sqlBoutiques = "SELECT 
    b.nom AS boutique_nom,
    YEAR(c.date_commande) AS annee,
    COUNT(c.id) AS nombre_commandes
FROM 
    commandes c
INNER JOIN 
    utilisateurs u ON c.utilisateur_id = u.id
INNER JOIN 
    boutiques b ON u.boutique_id = b.id
WHERE 
    YEAR(c.date_commande) = YEAR(CURDATE())
GROUP BY 
    b.nom, YEAR(c.date_commande)
ORDER BY 
    nombre_commandes DESC;
";

$requeteBoutiques = $conn->prepare($sqlBoutiques);
$requeteBoutiques->execute();

$boutiqueData = array();

while ($row = $requeteBoutiques->fetch(PDO::FETCH_ASSOC)) {
    $boutiqueData[] = array(
        'annee' => $row['annee'],
        'boutique_nom' => $row['boutique_nom'],
        'nombre_commandes' => $row['nombre_commandes']
    );
}

$jsonBoutiqueData = json_encode($boutiqueData);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique des Montants</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Inclure DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <style>
        #myTable {
            height: 400px !important;
            overflow-y: scroll;
        }
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination-link {
            padding: 8px;
            text-decoration: none;
            color: white;
            background-color: #007bff; 
            border: 1px solid #007bff;
            border-radius: 4px; 
            margin-right: 4px;
        }
        .items-per-page-form {
            margin-left: 20px;
        }
        label {
            margin-right: 5px;
        }
        .items-per-page-select {
            padding: 6px;
            border-radius: 4px; 
        }
        .submit-button {
            padding: 6px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px; 
            cursor: pointer;
        }
        .custom-icon {
            color: green;
            font-size: 24px;
            margin-right: 8px;
        }
        .spacing {
            margin-right: 10px; 
            margin-bottom: 20px;
        }
        @media only screen and (max-width: 767px) {
            th {
                display: none; 
            }
            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                padding: 10px;
            }
            tbody tr td::before {
                font-weight: bold;
                margin-right: 5px;
            }
        }
        .margin-right-15 {
            margin-right: 15px;
        }
        .block-container {
            background-color: #d7dbdd;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="row">
        <div class="block-container">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-commande">
                <i class="fa fa-edit"></i> Statistiques clients
            </button>
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#add-point">
                <i class="fa fa-print"></i> Imprimer un point
            </button>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search-commande">
                <i class="fa fa-search"></i> Recherche un point
            </button>
        </div>
        <div class="col-md-6" id="myTable">
            <?php
            if (!empty($dataPoints)) {
                echo "<table class='table table-striped table-valign-middle'>
                    <thead>
                        <tr>
                            <th>Année</th>
                            <th>Mois</th>
                            <th>Total Recette</th>
                            <th>Total Dépense</th>
                            <th>Gain du Mois</th>
                        </tr>
                    </thead>
                    <tbody>";

                foreach ($dataPoints as $row) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['annee']) . "</td>
                        <td>" . htmlspecialchars($row['mois']) . "</td>
                        <td>" . htmlspecialchars($row['total_recette']) . "</td>
                        <td>" . htmlspecialchars($row['total_depense']) . "</td>
                        <td>" . htmlspecialchars($row['gain']) . "</td>
                    </tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "Aucun résultat trouvé.";
            }
            ?>
        </div>
        <div class="col-md-6">
            <div class="position-relative mb-4">
                <canvas id="myChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Modal Add Commande -->
    <div class="modal fade" id="add-commande" tabindex="-1" role="dialog" aria-labelledby="addCommandeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="addCommandeLabel">Les clients les plus prolifiques</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Contenu du modal -->
                    <p><i>Voici les informations sur les clients les plus prolifiques.</i></p>
                    <canvas id="clientsChart" height="400"></canvas> <!-- Canvas pour le graphique en secteurs -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Données des boutiques pour le graphique en secteurs
        var clientData = <?php echo $jsonBoutiqueData; ?>;
        var clientNames = [];
        var clientValues = [];

        // Remplir les noms et valeurs des boutiques à partir des données JSON
        for (var i = 0; i < clientData.length; i++) {
            clientNames.push(clientData[i].boutique_nom); // Correction ici
            clientValues.push(clientData[i].nombre_commandes);
        }

        // Création du graphique des boutiques
        var ctxClients = document.getElementById('clientsChart').getContext('2d');
        var clientsChart = new Chart(ctxClients, {
            type: 'pie',
            data: {
                labels: clientNames,
                datasets: [{
                    label: 'Nombre de Commandes',
                    data: clientValues,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Données des dépenses et recettes pour le graphique
        var jsonData = <?php echo $jsonData; ?>;
        var labels = [];
        var depenses = [];
        var recettes = [];
        var gains = [];

        for (var i = 0; i < jsonData.length; i++) {
            labels.push(jsonData[i].mois + ' ' + jsonData[i].annee);
            depenses.push(jsonData[i].total_depense);
            recettes.push(jsonData[i].total_recette);
            gains.push(jsonData[i].gain);
        }

        // Création du graphique des dépenses, recettes et gains
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Dépenses',
                        data: depenses,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Recettes',
                        data: recettes,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Gain',
                        data: gains,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <!-- Inclure jQuery et Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <!-- Inclure DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
</body>
</html>
