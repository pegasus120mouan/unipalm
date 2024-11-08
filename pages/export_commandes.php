<?php
require_once '../inc/functions/connexion.php';
require '../vendor/autoload.php';  // Assurez-vous que PhpSpreadsheet est correctement chargé

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// En-têtes de colonnes
$sheet->setCellValue('A1', 'Communes');
$sheet->setCellValue('B1', 'Coût Global');
$sheet->setCellValue('C1', 'Livraison');
$sheet->setCellValue('D1', 'Coût réel');
$sheet->setCellValue('E1', 'Boutique');
$sheet->setCellValue('F1', 'Livreur');
$sheet->setCellValue('G1', 'Statut');
$sheet->setCellValue('H1', 'Date de la commande');

// Récupération des données
$stmt = $conn->prepare("SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_commande AS commande_date_commande, 
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    concat(livreur.nom, ' ',livreur.prenoms) AS fullname,
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
ORDER BY commandes.date_commande DESC");
$stmt->execute();
$point_commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remplir les données
$row = 2;  // Commence à la ligne 2 pour éviter les en-têtes
foreach ($point_commandes as $point_commande) {
    $sheet->setCellValue('A' . $row, $point_commande['commande_communes']);
    $sheet->setCellValue('B' . $row, $point_commande['commande_cout_global']);
    $sheet->setCellValue('C' . $row, $point_commande['commande_cout_livraison']);
    $sheet->setCellValue('D' . $row, $point_commande['commande_cout_reel']);
    $sheet->setCellValue('E' . $row, $point_commande['nom_boutique']);
    $sheet->setCellValue('F' . $row, $point_commande['fullname']);
    $sheet->setCellValue('G' . $row, $point_commande['commande_statut']);

    // Convertir la date PHP en format Excel et formater en français (jj/mm/aaaa)
    $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($point_commande['commande_date_commande']);
    $sheet->setCellValue('H' . $row, $excelDate);
    $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('DD/MM/YYYY');
    
    $row++;
}

// Définir le nom du fichier et les en-têtes pour le téléchargement
$filename = 'commandes_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");

// Créer le fichier et le télécharger
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
