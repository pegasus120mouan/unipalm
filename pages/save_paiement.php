<?php
require_once '../inc/functions/connexion.php';
//session_start(); // N'oubliez pas de démarrer la session si elle est nécessaire pour $_SESSION

if (isset($_POST['save_paiement'])) {
    $type_transaction = $_POST['type_transaction'];
    $montant = $_POST['montant'];
    $date_transaction = date("Y-m-d H:i:s");
    $id_ticket = $_POST['id_ticket'];
    $numero_ticket = $_POST['numero_ticket'];
    $id_utilisateur = $_SESSION['user_id'];
    $motifs = ($type_transaction == 'paiement') ? "Paiement du ticket " . $numero_ticket : "";

    try {
        // Démarrer une transaction
        $conn->beginTransaction();

        // 1. Insertion dans la table transactions
        $stmt = $conn->prepare("INSERT INTO transactions (type_transaction, montant, date_transaction, id_utilisateur, motifs) 
                                VALUES (:type_transaction, :montant, :date_transaction, :id_utilisateur, :motifs)");
        $stmt->bindParam(':type_transaction', $type_transaction);
        $stmt->bindParam(':montant', $montant);
        $stmt->bindParam(':date_transaction', $date_transaction);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur);
        $stmt->bindParam(':motifs', $motifs);

        if ($stmt->execute()) {
            // 2. Mise à jour de la table tickets
            
            // D'abord, récupérer le total des paiements déjà effectués pour ce ticket
            $stmtTotal = $conn->prepare("SELECT COALESCE(SUM(montant_payer), 0) as total_paye, montant_paie 
                                       FROM tickets 
                                       WHERE id_ticket = :id_ticket");
            $stmtTotal->bindParam(':id_ticket', $id_ticket);
            $stmtTotal->execute();
            $result = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            
            // Calculer le nouveau total payé et le reste
            $nouveau_total_paye = $result['total_paye'] + $montant;
            $montant_reste = $result['montant_paie'] - $nouveau_total_paye;
            
            // Mise à jour du ticket avec le nouveau paiement et le reste
            $stmtUpdate = $conn->prepare("UPDATE tickets 
                                        SET montant_payer = :nouveau_total_paye,
                                            montant_reste = :montant_reste,
                                            date_paie = :date_transaction 
                                        WHERE id_ticket = :id_ticket");
            
            $stmtUpdate->bindParam(':nouveau_total_paye', $nouveau_total_paye);
            $stmtUpdate->bindParam(':montant_reste', $montant_reste);
            $stmtUpdate->bindParam(':date_transaction', $date_transaction);
            $stmtUpdate->bindParam(':id_ticket', $id_ticket);

            if ($stmtUpdate->execute()) {
                // Validation de la transaction
                $conn->commit();
                $_SESSION['popup'] = true;
                $filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
                header('Location: paiements.php' . $filter);
                exit();
            } else {
                throw new PDOException("Erreur lors de la mise à jour du ticket");
            }
        } else {
            throw new PDOException("Erreur lors de l'enregistrement de la transaction");
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        $filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
        header('Location: paiements.php' . $filter);
        exit();
    }
}

// Redirection par défaut si aucune action n'est effectuée
$filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
header('Location: paiements.php' . $filter);
exit();
?>
