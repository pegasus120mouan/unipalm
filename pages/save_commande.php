<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utilisateur_id = $_POST["client_id"];
    $communes = $_POST['communes'];
    $cout_global = $_POST['cout_global'];
    $livraison = $_POST['livraison'];
    $cout_reel = $cout_global - $livraison;
    $date= date("Y-m-d");
    
    $query = "INSERT INTO commandes (utilisateur_id,communes, cout_global,cout_livraison,cout_reel,date_commande) 
    VALUES (:utilisateur_id,:communes, :cout_global,:cout_livraison,:cout_reel,:date_commande)";
    $query_run = $conn->prepare($query);
    
    $data = [
        ':utilisateur_id'=>$utilisateur_id,
        ':communes' => $communes,
        ':cout_global' => $cout_global,
        ':cout_livraison' => $livraison,
        ':cout_reel' => $cout_reel,
        ':date_commande' => $date,

    ];
    $query_execute = $query_run->execute($data);
   
    if($query_execute)
    {
       // $_SESSION['message'] = "Insertion reussie";
        $_SESSION['popup'] = true;
       header('Location: commandes.php');
       exit(0);
    }
    else
    {
        $_SESSION['delete_pop'] = true;
        header('Location: commandes.php');
        exit(0);
    }
}
?>