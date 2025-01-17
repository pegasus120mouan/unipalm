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

function getTicketsAttente($conn) {
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
        WHERE t.prix_unitaire = 0.00 AND t.date_validation_boss IS NULL"
    );
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

function insertTicket($conn, $id_usine, $date_ticket, $id_agent, $numero_ticket, $vehicule_id, $poids, $id_utilisateur)
{
    try {
        $query = "INSERT INTO tickets (id_usine, date_ticket, id_agent, numero_ticket, vehicule_id, poids, id_utilisateur, created_at) 
                  VALUES (:id_usine, :date_ticket, :id_agent, :numero_ticket, :vehicule_id, :poids, :id_utilisateur, :created_at)";
        $query_run = $conn->prepare($query);

        $data = [
            ':id_usine' => $id_usine,
            ':date_ticket' => $date_ticket,
            ':id_agent' => $id_agent,
            ':numero_ticket' => $numero_ticket,
            ':vehicule_id' => $vehicule_id,
            ':poids' => $poids,
            ':id_utilisateur' => $id_utilisateur,
            ':created_at' => date("Y-m-d H:i"),
        ];

        // Exécuter la requête
        if ($query_run->execute($data)) {
            $_SESSION['popup'] = true; // Message de succès
            header('Location: tickets.php');
            exit;
        } else {
             $_SESSION['false'] = true; // Message de succès
            header('Location: tickets.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Erreur lors de l'insertion du ticket : " . $e->getMessage());
        return false;
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
    $sql = "SELECT t.*, u.nom_usine, 
            CONCAT(a.nom, ' ', a.prenom) as agent_nom_complet,
            v.matricule_vehicule,
            CONCAT(us.nom, ' ', us.prenoms) as utilisateur_nom_complet,
            t.created_at,
            t.prix_unitaire,
            t.date_validation_boss,
            t.montant_paie,
            t.date_paie
            FROM tickets t
            LEFT JOIN usines u ON t.id_usine = u.id_usine
            LEFT JOIN agents a ON t.id_agent = a.id_agent
            LEFT JOIN vehicules v ON t.vehicule_id = v.vehicules_id
            LEFT JOIN utilisateurs us ON t.id_utilisateur = us.id
            WHERE 1=1";
    
    $params = array();
    
    if ($usine) {
        $sql .= " AND t.id_usine = ?";
        $params[] = $usine;
    }
    
    if ($date) {
        $sql .= " AND DATE(t.created_at) = ?";
        $params[] = $date;
    }
    
    if ($chauffeur) {
        $sql .= " AND t.vehicule_id = ?";
        $params[] = $chauffeur;
    }
    
    if ($agent) {
        $sql .= " AND t.id_agent = ?";
        $params[] = $agent;
    }
    
    $sql .= " ORDER BY t.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
