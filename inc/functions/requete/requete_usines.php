<?php

function getUsines($conn) {
    $stmt = $conn->prepare(
        "SELECT id_usine, nom_usine from usines"
    );

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>