<?php

function getTickets($conn) {
    $stmt = $conn->prepare(
        "SELECT 
    t.id_ticket,
    t.date_ticket,
    t.numero_ticket,
    t.poids,
    t.prix_unitaire,
    t.date_validation_boss,
    t.montant_paie,
    t.date_paie,
    t.montant_payer,
    t.montant_reste,
    t.created_at,
    CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
    u.contact AS utilisateur_contact,
    u.role AS utilisateur_role,
    v.matricule_vehicule,
    CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
    us.nom_usine
FROM 
    tickets t
INNER JOIN 
    utilisateurs u ON t.id_utilisateur = u.id
INNER JOIN 
    vehicules v ON t.vehicule_id = v.vehicules_id
INNER JOIN 
    agents a ON t.id_agent = a.id_agent
INNER JOIN 
    usines us ON t.id_usine = us.id_usine ORDER BY created_at DESC"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getTicketsJour($conn) {
    $stmt = $conn->prepare(
                    "SELECT 
                t.id_ticket,
                t.date_ticket,
                t.numero_ticket,
                t.poids,
                t.prix_unitaire,
                t.date_validation_boss,
                t.montant_paie,
                t.date_paie,
                t.created_at,
                CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
                u.contact AS utilisateur_contact,
                u.role AS utilisateur_role,
                v.matricule_vehicule,
                CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
                us.nom_usine
            FROM 
                tickets t
            INNER JOIN 
                utilisateurs u ON t.id_utilisateur = u.id
            INNER JOIN 
                vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN 
                agents a ON t.id_agent = a.id_agent
            INNER JOIN 
                usines us ON t.id_usine = us.id_usine
            WHERE 
                DATE(t.created_at) = CURRENT_DATE()
            ORDER BY 
                t.created_at DESC"
    );
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsAttente($conn, $agent_id = null, $usine_id = null) {
    $sql = "SELECT t.*, 
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine,
            us.id_usine
            FROM tickets t
            INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
            INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            INNER JOIN agents a ON t.id_agent = a.id_agent
            INNER JOIN usines us ON t.id_usine = us.id_usine
            WHERE t.date_validation_boss IS NULL";

    if ($agent_id) {
        $sql .= " AND t.id_agent = :agent_id";
    }
    
    if ($usine_id) {
        $sql .= " AND t.id_usine = :usine_id";
    }

    $sql .= " ORDER BY t.created_at DESC";

    try {
        $stmt = $conn->prepare($sql);
        if ($agent_id) {
            $stmt->bindValue(':agent_id', $agent_id, PDO::PARAM_INT);
        }
        if ($usine_id) {
            $stmt->bindValue(':usine_id', $usine_id, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTicketsAttente: " . $e->getMessage());
        return array();
    }
}


function getTicketsValides($conn) {
    $stmt = $conn->prepare(
            "SELECT 
    t.id_ticket,
    t.date_ticket,
    t.numero_ticket,
    t.poids,
    t.prix_unitaire,
    t.date_validation_boss,
    t.montant_paie,
    t.date_paie,
    CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
    u.contact AS utilisateur_contact,
    u.role AS utilisateur_role,
    v.matricule_vehicule,
    CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
    us.nom_usine
FROM 
    tickets t
INNER JOIN 
    utilisateurs u ON t.id_utilisateur = u.id
INNER JOIN 
    vehicules v ON t.vehicule_id = v.vehicules_id
INNER JOIN 
    agents a ON t.id_agent = a.id_agent
INNER JOIN 
    usines us ON t.id_usine = us.id_usine
WHERE 
    t.prix_unitaire != 0.00  AND t.date_paie IS NOT NULL ORDER BY t.date_paie DESC"
    );
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsPayes($conn) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.date_validation_boss,
            t.montant_paie,
            t.date_paie,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
            WHERE t.date_paie IS NOT NULL AND DATE(t.date_ticket) IS NOT NULL"
    );
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsNonSoldes($conn) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.date_validation_boss,
            t.montant_paie,
            t.montant_payer,
            t.montant_reste,
            t.date_paie,
            t.created_at,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            t.montant_reste > 0
            AND t.prix_unitaire IS NOT NULL
            AND t.prix_unitaire > 0
        ORDER BY t.created_at DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTicketsSoldes($conn) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.date_validation_boss,
            t.montant_paie,
            t.montant_payer,
            t.montant_reste,
            t.date_paie,
            t.created_at,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            t.montant_reste = 0
            AND t.prix_unitaire IS NOT NULL
            AND t.prix_unitaire > 0
        ORDER BY t.created_at DESC"
    );
    $stmt->execute();
    return $stmt->fetchAll();
}

function insertTicket($conn, $id_usine, $date_ticket, $id_agent, $numero_ticket, $vehicule_id, $poids, $id_utilisateur, $prix_unitaire = null) {
    try {
        $sql = "INSERT INTO tickets (id_usine, date_ticket, id_agent, numero_ticket, vehicule_id, poids, id_utilisateur, created_at, prix_unitaire) 
                VALUES (:id_usine, :date_ticket, :id_agent, :numero_ticket, :vehicule_id, :poids, :id_utilisateur, NOW(), :prix_unitaire)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_usine', $id_usine);
        $stmt->bindParam(':date_ticket', $date_ticket);
        $stmt->bindParam(':id_agent', $id_agent);
        $stmt->bindParam(':numero_ticket', $numero_ticket);
        $stmt->bindParam(':vehicule_id', $vehicule_id);
        $stmt->bindParam(':poids', $poids);
        $stmt->bindParam(':id_utilisateur', $id_utilisateur);
        $stmt->bindParam(':prix_unitaire', $prix_unitaire);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erreur lors de l'insertion du ticket: " . $e->getMessage());
        throw $e;
    }
}

function updateTicketPrixUnitaire($conn, $id_ticket, $prix_unitaire, $date) {
    // Requête SQL d'update
    $sql = "UPDATE tickets
            SET prix_unitaire = :prix_unitaire, date_validation_boss = :date_validation_boss 
            WHERE id_ticket = :id_ticket";

    try {
        // Préparation de la requête
        $requete = $conn->prepare($sql);

        // Exécution de la requête avec les nouvelles valeurs
        $query_execute = $requete->execute([
            ':id_ticket' => $id_ticket,
            ':prix_unitaire' => $prix_unitaire,
            ':date_validation_boss' => $date
        ]);

        // Vérification de l'exécution
        return $query_execute;
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour du ticket : " . $e->getMessage());
        return false;
    }
}

function updateTicket($conn, $id_ticket, $date_ticket, $numero_ticket) {
    try {
        $stmt = $conn->prepare("
            UPDATE tickets 
            SET date_ticket = :date_ticket,
                numero_ticket = :numero_ticket
            WHERE id_ticket = :id_ticket
        ");
        
        $stmt->bindParam(':date_ticket', $date_ticket);
        $stmt->bindParam(':numero_ticket', $numero_ticket);
        $stmt->bindParam(':id_ticket', $id_ticket);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Erreur lors de la mise à jour du ticket: " . $e->getMessage());
        return false;
    }
}

function searchTickets($conn, $usine = null, $date = null, $chauffeur = null, $agent = null) {
    $sql = "SELECT 
        t.id_ticket,
        t.date_ticket,
        t.numero_ticket,
        t.poids,
        t.prix_unitaire,
        t.date_validation_boss,
        t.montant_paie,
        t.date_paie,
        t.montant_payer,
        t.montant_reste,
        t.created_at,
        CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
        u.contact AS utilisateur_contact,
        u.role AS utilisateur_role,
        v.matricule_vehicule,
        CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
        us.nom_usine
    FROM tickets t
    INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
    INNER JOIN vehicules v ON t.vehicule_id = v.vehicules_id
    INNER JOIN agents a ON t.id_agent = a.id_agent
    INNER JOIN usines us ON t.id_usine = us.id_usine
    WHERE 1=1";

    $params = array();

    if ($usine) {
        $sql .= " AND t.id_usine = :usine";
        $params[':usine'] = $usine;
    }

    if ($date) {
        $sql .= " AND DATE(t.date_ticket) = :date";
        $params[':date'] = $date;
    }

    if ($chauffeur) {
        $sql .= " AND t.vehicule_id = :chauffeur";
        $params[':chauffeur'] = $chauffeur;
    }

    if ($agent) {
        $sql .= " AND t.id_agent = :agent";
        $params[':agent'] = $agent;
    }

    $sql .= " ORDER BY t.created_at DESC";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans searchTickets: " . $e->getMessage());
        return array();
    }
}

function searchTicketsByDateRange($conn, $date_debut, $date_fin) {
    $stmt = $conn->prepare(
        "SELECT 
            t.*,
            CONCAT(u.nom, ' ', u.prenoms) AS utilisateur_nom_complet,
            u.contact AS utilisateur_contact,
            u.role AS utilisateur_role,
            v.matricule_vehicule,
            CONCAT(a.nom, ' ', a.prenom) AS agent_nom_complet,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            utilisateurs u ON t.id_utilisateur = u.id
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            agents a ON t.id_agent = a.id_agent
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            DATE(t.date_ticket) BETWEEN :date_debut AND :date_fin
        ORDER BY 
            t.date_ticket DESC"
    );
    
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->bindParam(':date_fin', $date_fin);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsForBordereau($conn, $agent_id, $date_debut, $date_fin) {
    $stmt = $conn->prepare(
        "SELECT 
            t.id_ticket,
            t.date_ticket,
            t.numero_ticket,
            t.poids,
            t.prix_unitaire,
            t.numero_bordereau,
            v.matricule_vehicule,
            us.nom_usine
        FROM 
            tickets t
        INNER JOIN 
            vehicules v ON t.vehicule_id = v.vehicules_id
        INNER JOIN 
            usines us ON t.id_usine = us.id_usine
        WHERE 
            t.id_agent = :agent_id 
            AND t.date_ticket BETWEEN :date_debut AND :date_fin
        ORDER BY 
            t.date_ticket DESC"
    );

    $stmt->execute([
        ':agent_id' => $agent_id,
        ':date_debut' => $date_debut,
        ':date_fin' => $date_fin
    ]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateTicketsBordereau($conn, $ticket_ids, $numero_bordereau) {
    $logFile = dirname(dirname(dirname(__FILE__))) . '/pages/ajax/debug.log';
    
    try {
        file_put_contents($logFile, "\nDébut updateTicketsBordereau\n", FILE_APPEND);
        file_put_contents($logFile, "Tickets IDs: " . print_r($ticket_ids, true) . "\n", FILE_APPEND);
        file_put_contents($logFile, "Numéro bordereau: " . $numero_bordereau . "\n", FILE_APPEND);
        
        // Commencer une transaction
        $conn->beginTransaction();
        
        // Préparer les paramètres pour la requête
        $placeholders = str_repeat('?,', count($ticket_ids) - 1) . '?';
        
        // Construire la requête SQL
        $sql = "UPDATE tickets 
                SET 
                    numero_bordereau = ?,
                    updated_at = NOW()
                WHERE id_ticket IN ($placeholders)";
        
        file_put_contents($logFile, "Requête SQL: " . $sql . "\n", FILE_APPEND);
        
        // Préparer la requête
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            throw new Exception("Erreur de préparation de la requête: " . implode(", ", $conn->errorInfo()));
        }
        
        // Ajouter le numéro de bordereau comme premier paramètre, suivi des IDs des tickets
        $params = array_merge([$numero_bordereau], $ticket_ids);
        file_put_contents($logFile, "Paramètres: " . print_r($params, true) . "\n", FILE_APPEND);
        
        // Exécuter la requête
        $result = $stmt->execute($params);
        
        if ($result === false) {
            $error = $stmt->errorInfo();
            file_put_contents($logFile, "Erreur SQL: " . print_r($error, true) . "\n", FILE_APPEND);
            throw new Exception("Erreur lors de la mise à jour des tickets: " . implode(", ", $error));
        }
        
        $rowCount = $stmt->rowCount();
        file_put_contents($logFile, "Nombre de lignes affectées: " . $rowCount . "\n", FILE_APPEND);
        
        // Valider la transaction seulement si des lignes ont été affectées
        if ($rowCount > 0) {
            $conn->commit();
            file_put_contents($logFile, "Transaction validée avec succès\n", FILE_APPEND);
            return true;
        } else {
            $conn->rollBack();
            file_put_contents($logFile, "Aucune ligne affectée, rollback effectué\n", FILE_APPEND);
            throw new Exception("Aucun ticket n'a été mis à jour");
        }
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        file_put_contents($logFile, "Erreur et rollback: " . $e->getMessage() . "\n", FILE_APPEND);
        throw $e;
    }
}

?>
