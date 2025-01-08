<?php

require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifiez si l'action concerne l'insertion ou autre chose
    if (isset($_POST["usine"]) && isset($_POST["date_ticket"])) {
        // Traitement de l'insertion du ticket
        $id_usine = $_POST["usine"] ?? null;
        $date_ticket = $_POST["date_ticket"] ?? null;
        $id_agent = $_POST["id_agent"] ?? null;
        $numero_ticket = $_POST["numero_ticket"] ?? null;
        $vehicule_id = $_POST["vehicule"] ?? null;
        $poids = $_POST["poids"] ?? null;
        $id_utilisateur = $_SESSION['user_id'] ?? null;

        // Validation des données
        if (!$id_usine || !$date_ticket || !$id_agent || !$numero_ticket || !$vehicule_id || !$poids || !$id_utilisateur) {
            $_SESSION['delete_pop'] = true; // Message d'erreur
            header('Location: tickets.php');
            exit;
        }

        // Appel de la fonction pour insérer le ticket
        try {
            if (insertTicket($conn, $id_usine, $date_ticket, $id_agent, $numero_ticket, $vehicule_id, $poids, $id_utilisateur)) {
                $_SESSION['popup'] = true; // Message de succès
            } else {
                $_SESSION['delete_pop'] = true; // Message d'erreur
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du ticket : " . $e->getMessage());
            $_SESSION['delete_pop'] = true; // Message d'erreur
        }
        header('Location: tickets.php');
        exit;
    } elseif (isset($_POST["id_ticket"]) && isset($_POST["prix_unitaire"])) {
        // Traitement des données supplémentaires
        $id_ticket = $_POST["id_ticket"] ?? null;
        $prix_unitaire = $_POST["prix_unitaire"] ?? null;
        $date = date("Y-m-d");

        // Validation des données
        if (!$id_ticket || !$prix_unitaire) {
            $_SESSION['delete_pop'] = true; // Message d'erreur
            header('Location: tickets.php');
            exit;
        }

        // Requête SQL d'update
        $sql = "UPDATE tickets
                SET prix_unitaire = :prix_unitaire, date_validation_boss = :date_validation_boss 
                WHERE id_ticket = :id_ticket";

        // Préparation de la requête
         // Appel de la fonction
        $result = updateTicketPrixUnitaire($conn, $id_ticket, $prix_unitaire, $date);

        if ($result) {
            $_SESSION['popup'] = true; // Message de succès
        } else {
            $_SESSION['delete_pop'] = true; // Message d'erreur
        }

        // Redirection
        header('Location: tickets.php');
        exit;
    }

    // Vérifier si le ticket existe et n'est pas payé
    if (isset($_POST['id_ticket'])) {
        $check_stmt = $conn->prepare("SELECT date_paie FROM tickets WHERE id_ticket = ?");
        $check_stmt->execute([$_POST['id_ticket']]);
        $ticket = $check_stmt->fetch();

        if ($ticket['date_paie'] !== null) {
            $_SESSION['delete_pop'] = true;
            header('Location: tickets_modifications.php');
            exit;
        }
    }

    // Modification de l'usine
    if (isset($_POST["usine"]) && isset($_POST["id_ticket"]) && !isset($_POST["date_ticket"])) {
        $id_usine = $_POST["usine"];
        $id_ticket = $_POST["id_ticket"];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET id_usine = ? WHERE id_ticket = ?");
            $stmt->execute([$id_usine, $id_ticket]);
            $_SESSION['popup'] = true;
        } catch (PDOException $e) {
            $_SESSION['delete_pop'] = true;
        }
        header('Location: tickets_modifications.php');
        exit;
    }

    // Modification de l'agent (chef de mission)
    if (isset($_POST["chef_equipe"]) && isset($_POST["id_ticket"])) {
        $id_agent = $_POST["chef_equipe"];
        $id_ticket = $_POST["id_ticket"];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET id_agent = ? WHERE id_ticket = ?");
            $stmt->execute([$id_agent, $id_ticket]);
            $_SESSION['popup'] = true;
        } catch (PDOException $e) {
            $_SESSION['delete_pop'] = true;
        }
        header('Location: tickets_modifications.php');
        exit;
    }

    // Modification du véhicule
    if (isset($_POST["vehicule"]) && isset($_POST["id_ticket"])) {
        $id_vehicule = $_POST["vehicule"];
        $id_ticket = $_POST["id_ticket"];

        try {
            $stmt = $conn->prepare("UPDATE tickets SET vehicule_id = ? WHERE id_ticket = ?");
            $stmt->execute([$id_vehicule, $id_ticket]);
            $_SESSION['popup'] = true;
            header('Location: tickets_modifications.php');
        } catch (PDOException $e) {
            $_SESSION['delete_pop'] = true;
            error_log("Erreur lors de la mise à jour du véhicule : " . $e->getMessage());
            header('Location: tickets_modifications.php');
        }
        exit;
    }
}

header('Location: tickets_modifications.php');
exit;
?>
