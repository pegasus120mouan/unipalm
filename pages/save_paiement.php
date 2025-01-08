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
                $_SESSION['success'] = true;
                ?>
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Mise à jour des données</title>
                    <style>
                        .table-loader {
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(255, 255, 255, 0.8);
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            flex-direction: column;
                        }
                        .spinner {
                            border: 4px solid #f3f3f3;
                            border-radius: 50%;
                            border-top: 4px solid #3498db;
                            width: 40px;
                            height: 40px;
                            animation: spin 1s linear infinite;
                            margin-bottom: 10px;
                        }
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                        .update-text {
                            font-size: 18px;
                            color: #333;
                        }
                    </style>
                </head>
                <body>
                    <div class="table-loader">
                        <div class="spinner"></div>
                        <div class="update-text">Mise à jour des données...</div>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.href = 'paiements.php';
                        }, 3000);
                    </script>
                </body>
                </html>
                <?php
                exit();
            } else {
                $conn->rollBack();
                $_SESSION['error'] = "Erreur : la mise à jour du ticket a échoué.";
                header('Location: paiements.php');
                exit();
            }
        } else {
            $conn->rollBack();
            $_SESSION['error'] = "Erreur : l'insertion dans les transactions a échoué.";
            header('Location: paiements.php');
            exit();
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header('Location: paiements.php');
        exit();
    }
}
?>
