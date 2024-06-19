<?php
// Laadt het startbestand voor initiële instellingen
require("start.php");

// Controleert of de gebruiker is ingelogd door te controleren op de sessievariabele 'email'
if (!isset($_SESSION["email"])) {
    // Als de gebruiker niet is ingelogd, doorsturen naar de inlogpagina
    header("Location: loginPage.php");
    exit;
}

// Controleert of het verzoek een POST-verzoek is en of de 'id' is verzonden via het formulier
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    // Haalt de 'id' op uit het POST-verzoek; dit komt van het formulier
    $id = $_POST['id'];

    require("pdo.php");

    
    $query = "UPDATE `countries` SET `active`=0 WHERE `id` = :ID"; //Update active "1" naar '0' zodat land bij verwijderd staat

    // Waarde aan de query binden
    $values = [":ID" => $id];

    try {
        // Bereidt de query voor en voert deze uit met de gebonden waarden
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        // Foutafhandeling bij het uitvoeren van de query
        echo 'Query error.' . $e;
        die();
    }
}
?>