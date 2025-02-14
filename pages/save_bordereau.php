<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_agent=$_POST['id_agent'];
    $date_debut=$_POST['date_debut'];
    $date_fin=$_POST['date_fin'];

    echo $id_agent;
    echo "<br>";
    echo $date_debut;
    echo "<br>";
    echo $date_fin;
    echo "<br>";

    try {
        $sql = "INSERT INTO bordereau (numero_bordereau, id_agent, date_debut, date_fin, poids_total, montant_total)
                SELECT 
                    CONCAT('BORD-', DATE_FORMAT(NOW(), '%Y%m%d-%H%i%s'), '-', FLOOR(RAND() * 9000) + 1000), 
                    :id_agent, 
                    :date_debut, 
                    :date_fin, 
                    COALESCE(SUM(t.poids), 0), 
                    COALESCE(SUM(t.prix_unitaire * t.poids), 0)
                FROM tickets t 
                WHERE t.id_agent = :id_agent 
                AND t.created_at BETWEEN :date_debut AND :date_fin";
    
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id_agent' => $id_agent,
            ':date_debut' => $date_debut,
            ':date_fin' => $date_fin
        ]);
        
        $id_bordereau = $conn->lastInsertId();
        
        echo "Bordereau créé avec succès ! ID: " . $id_bordereau . "\n";
        
        // Afficher les détails du bordereau créé
        if ($id_bordereau) {
            $sql = "SELECT * FROM bordereau WHERE id_bordereau = :id_bordereau";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id_bordereau', $id_bordereau);
            $stmt->execute();
            $bordereau = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($bordereau) {
                header("Location: bordereaux.php");
                exit();
            }
            else
            {
                header("Location: bordereaux.php");
                exit();
            }
        }
    
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage() . "\n";
    }


    
}
?>
