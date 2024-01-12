<?php
// Assurez-vous d'avoir la connexion à la base de données et d'autres fichiers inclus ici
require_once '../inc/functions/connexion.php';

// Vérifiez si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Récupérez les données du formulaire de recherche
    $searchQuery = isset($_GET['recherche']) ? $_GET['recherche'] : '';


    // Traitez les données comme vous le souhaitez, par exemple, recherchez dans la base de données
    // Exemple simple : recherchez dans une table "commandes" la colonne "commande_communes"
   $sql = "SELECT * FROM commandes WHERE communes LIKE :searchQuery";
   $stmt = $conn->prepare($sql);
   $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vous pouvez maintenant utiliser les résultats pour afficher ou faire d'autres opérations

    // Par exemple, rediriger vers la page d'affichage des résultats
    header("Location: resultats-de-recherche.php");
exit();
} else {
    // Le formulaire n'a pas été soumis, vous pouvez rediriger l'utilisateur ou faire autre chose
   header("Location: /index.php");
    exit();
}
?>
