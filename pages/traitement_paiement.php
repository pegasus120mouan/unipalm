<?php
require_once '../inc/functions/connexion.php';
session_start();

if (isset($_POST['payer_ticket'])) {
    $id_ticket = $_POST['id_ticket'];
    $date_paiement = $_POST['date_paiement'];
    $id_utilisateur = $_SESSION['user_id'];

    try {
        // 1. Mettre à jour la date de paiement du ticket
        $sql = "UPDATE tickets SET date_paie = :date_paiement WHERE id_ticket = :id_ticket";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':date_paiement' => $date_paiement,
            ':id_ticket' => $id_ticket
        ]);

        // 2. Récupérer le montant du ticket
        $sql = "SELECT montant_paie FROM tickets WHERE id_ticket = :id_ticket";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id_ticket' => $id_ticket]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        $montant = $ticket['montant_paie'];

        // 3. Enregistrer la transaction
        $sql = "INSERT INTO transactions (type_transaction, montant, id_ticket, id_utilisateur, date_transaction) 
                VALUES ('paiement', :montant, :id_ticket, :id_utilisateur, :date_transaction)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':montant' => $montant,
            ':id_ticket' => $id_ticket,
            ':id_utilisateur' => $id_utilisateur,
            ':date_transaction' => $date_paiement
        ]);

        $_SESSION['popup'] = true;
        header('Location: paiements.php');
        exit();

    } catch(PDOException $e) {
        $_SESSION['delete_pop'] = true;
        header('Location: paiements.php');
        exit();
    }
} else {
    header('Location: paiements.php');
    exit();
}
