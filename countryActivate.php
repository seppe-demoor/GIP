<?php
// Laadt het startbestand voor initiële instellingen
require("start.php");

// Controleert of de gebruiker is ingelogd
if (!isset($_SESSION["email"])) {
    // Als de gebruiker niet is ingelogd, doorsturen naar de inlogpagina
    header("Location: loginPage.php");
    exit;
}

// Controleert of het een POST-verzoek is en of 'id' is verzonden
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    // Haalt het verzonden 'id' op uit het POST-verzoek
    $id = $_POST['id'];

    // Laadt het PDO-bestand voor databaseverbinding
    require("pdo.php");

    // Voorbereiden van de query om het land te activeren
    $query = "UPDATE `countries` SET `active`=1 WHERE `id` = :ID";

    // Waarden die aan de query worden gebonden
    $values = [":ID" => $id];

    try {
        // Voorbereiden van de query en uitvoeren met de gebonden waarden
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        // Afhandeling van fouten bij het uitvoeren van de query
        echo 'Query error.' . $e;
        die();
    }
}
?>
