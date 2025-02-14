<?php
require_once '../inc/functions/connexion.php';

if (isset($_POST['save_paiement'])) {
    $type_transaction = $_POST['type_transaction'];
    $montant = $_POST['montant'];
    $date_transaction = date("Y-m-d H:i:s");
    $id_utilisateur = $_SESSION['user_id'];
    
    // Déterminer si c'est un paiement de ticket ou de bordereau
    $is_bordereau = isset($_POST['id_bordereau']);
    
    if ($is_bordereau) {
        $id_bordereau = $_POST['id_bordereau'];
        $numero_bordereau = $_POST['numero_bordereau'];
        $motifs = "Paiement du bordereau " . $numero_bordereau;
    } else {
        $id_ticket = $_POST['id_ticket'];
        $numero_ticket = $_POST['numero_ticket'];
        $motifs = "Paiement du ticket " . $numero_ticket;
    }

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
            if ($is_bordereau) {
                // Mise à jour directe du bordereau
                $stmtBordereau = $conn->prepare("
                    SELECT montant_total, COALESCE(montant_payer, 0) as montant_payer 
                    FROM bordereau 
                    WHERE id_bordereau = :id_bordereau");
                $stmtBordereau->bindParam(':id_bordereau', $id_bordereau);
                $stmtBordereau->execute();
                $bordereau = $stmtBordereau->fetch(PDO::FETCH_ASSOC);

                // Calculer les nouveaux montants
                $nouveau_montant_payer = $bordereau['montant_payer'] + $montant;
                $nouveau_montant_reste = $bordereau['montant_total'] - $nouveau_montant_payer;
                $statut_bordereau = $nouveau_montant_reste <= 0 ? 'soldé' : 'non soldé';

                // Mise à jour du bordereau
                $stmtUpdate = $conn->prepare("
                    UPDATE bordereau 
                    SET montant_payer = :montant_payer,
                        montant_reste = :montant_reste,
                        date_paie = :date_transaction,
                        statut_bordereau = :statut_bordereau
                    WHERE id_bordereau = :id_bordereau");
                
                $stmtUpdate->bindParam(':montant_payer', $nouveau_montant_payer);
                $stmtUpdate->bindParam(':montant_reste', $nouveau_montant_reste);
                $stmtUpdate->bindParam(':date_transaction', $date_transaction);
                $stmtUpdate->bindParam(':statut_bordereau', $statut_bordereau);
                $stmtUpdate->bindParam(':id_bordereau', $id_bordereau);
                
                if (!$stmtUpdate->execute()) {
                    throw new PDOException("Erreur lors de la mise à jour du bordereau");
                }

                // Mise à jour des tickets du bordereau
                $stmtUpdateTickets = $conn->prepare("
                    UPDATE tickets 
                    SET montant_payer = CASE
                            WHEN montant_reste >= :montant THEN COALESCE(montant_payer, 0) + :montant
                            ELSE montant_paie
                        END,
                        montant_reste = CASE
                            WHEN montant_reste >= :montant THEN montant_reste - :montant
                            ELSE 0
                        END,
                        date_paie = :date_transaction
                    WHERE numero_bordereau = :numero_bordereau
                    AND montant_reste > 0
                    ORDER BY id_ticket ASC
                    LIMIT 1");
                
                $stmtUpdateTickets->bindParam(':montant', $montant);
                $stmtUpdateTickets->bindParam(':date_transaction', $date_transaction);
                $stmtUpdateTickets->bindParam(':numero_bordereau', $numero_bordereau);
                
                if (!$stmtUpdateTickets->execute()) {
                    throw new PDOException("Erreur lors de la mise à jour des tickets");
                }
            } else {
                // Mise à jour du ticket
                $stmtTotal = $conn->prepare("
                    SELECT COALESCE(montant_payer, 0) as total_paye, montant_paie 
                    FROM tickets 
                    WHERE id_ticket = :id_ticket");
                $stmtTotal->bindParam(':id_ticket', $id_ticket);
                $stmtTotal->execute();
                $result = $stmtTotal->fetch(PDO::FETCH_ASSOC);

                // Calculer le nouveau montant payé et le reste
                $nouveau_montant_paye = $result['total_paye'] + $montant;
                $nouveau_montant_reste = $result['montant_paie'] - $nouveau_montant_paye;

                // Mise à jour du ticket
                $stmtUpdate = $conn->prepare("
                    UPDATE tickets 
                    SET montant_payer = :nouveau_montant_paye,
                        montant_reste = :nouveau_montant_reste,
                        date_paie = :date_transaction
                    WHERE id_ticket = :id_ticket");

                $stmtUpdate->bindParam(':nouveau_montant_paye', $nouveau_montant_paye);
                $stmtUpdate->bindParam(':nouveau_montant_reste', $nouveau_montant_reste);
                $stmtUpdate->bindParam(':date_transaction', $date_transaction);
                $stmtUpdate->bindParam(':id_ticket', $id_ticket);

                if (!$stmtUpdate->execute()) {
                    throw new PDOException("Erreur lors de la mise à jour du ticket");
                }
            }

            // Si tout s'est bien passé, on valide la transaction
            $conn->commit();
            $_SESSION['success'] = "Paiement enregistré avec succès";
        } else {
            throw new PDOException("Erreur lors de l'enregistrement du paiement");
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }
    
    // Redirection
    header('Location: paiements.php');
    exit();
}

// Redirection par défaut si aucune action n'est effectuée
$filter = isset($_POST['filter']) ? '?filter=' . $_POST['filter'] : '';
header('Location: paiements.php' . $filter);
exit();
?>
