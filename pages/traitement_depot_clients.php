<?php
require('../fpdf/fpdf.php'); 
require_once '../inc/functions/connexion.php';   

if (isset($_POST['client']) && isset($_POST['date_debut']) && isset($_POST['date_fin']) ) {
    $client = $_POST['client'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];

    // Étape 1 : Etablir la connexion à la base de données

    // Étape 2 : Exécuter la requête SQL pour récupérer les données du pays et de la date sélectionnée
    $sql = "SELECT
        b.id AS id_boutique,
        b.nom AS nom_boutique,
        c.date_commande as date_commande,
        SUM(c.cout_reel) AS cout_reel_journalier
    FROM
        boutiques b
    JOIN
        utilisateurs u ON b.id = u.boutique_id
    JOIN
        commandes c ON u.id = c.utilisateur_id
    WHERE
        c.date_commande BETWEEN :dateDebut AND :dateFin  AND b.nom= :client 
    GROUP BY
        b.id, b.nom, c.date_commande
    ORDER BY
        b.id, c.date_commande";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':client', $client);
    $requete->bindParam(':dateDebut', $date_debut);
    $requete->bindParam(':dateFin', $date_fin);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Étape 3 : Créez un fichier PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Définissez la police et la taille de la police
    $pdf->SetFont('Arial', 'I', 14);

    // Ajoutez un titre
    $pdf->SetY(55);
    $pdf->SetX(10);
    $pdf->SetFont('Helvetica','B',12);
    $pdf->Cell(50,10,"Point des depot effectué ",0,1);
    $pdf->SetFont('Helvetica','',12);
    $pdf->Cell(50,7,"$client",0,1);
    $pdf->SetFont('Helvetica','',12);
    $pdf->Cell(50,7,"$date_debut",0,1);
    $pdf->Cell(50,7,"$date_fin",0,1);
    $pdf->Ln(7);

    // Ajoutez les données dans le PDF
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Cell(50, 10, "Partenaires", 1, 0, 'C', 1);
    $pdf->Cell(50, 10, "Date", 1, 0, 'C', 1);
    $pdf->Cell(50, 10, "Montant", 1, 1, 'C', 1);

    $total = 0;
    foreach ($resultat as $row) {
        $total = $total + $row['cout_reel_journalier'];
        $pdf->Cell(50, 10, utf8_decode($row['nom_boutique']), 1);
        $pdf->Cell(50, 10, $row['date_commande'], 1);
        $pdf->Cell(50, 10, $row['cout_reel_journalier'], 1);
        $pdf->Ln();
    }
    
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(50, 10, "Total", 1, 0, 'C', 1);
    $pdf->SetFillColor(173, 216, 230);
    $pdf->SetFont('Helvetica', '', 25);
    $pdf->Cell(100, 10, $total, 1,0,'C',1);

    // Étape 4 : Générez le fichier PDF
    $pdf->Output();

    // Étape 5 : Fermer la connexion à la base de données
    $conn = null;
} else {
    echo "Veuillez sélectionner un client et une date.";
}
?>
