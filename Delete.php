<?php
// Laad het startbestand voor initiële instellingen en sessiebeheer
require("start.php");

// Controleer of het verzoek een POST-verzoek is en of een ID is meegegeven in de URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    // Haal het ID op uit de POST-gegevens
    $id = $_POST['id'];

    // Laad het PDO-bestand voor de databaseverbinding
    require('pdo.php');

    // Definieer de SQL-query om de gebruiker te deactiveren
    $query = "UPDATE users SET active = 0 WHERE id = :id";
    // Maak een array met de waarden die aan de query moeten worden gebonden
    $values = [':ID' => $id];

    try {
        // Bereid de query voor met behulp van PDO
        $stmt = $pdo->prepare($query);
        // Bind de waarde van $id aan de parameter :id in de query
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        // Voer de query uit met de gebonden waarden
        $stmt->execute($values);

        // Redirect naar de overzichtspagina na succesvolle uitvoering
        header("Location: useroverzicht.php");
        exit();
    } catch (PDOException $e) {
        // Log de foutmelding in de error log
        error_log('Query error: ' . $e->getMessage());

        // Redirect naar de overzichtspagina bij een fout
        header("Location: useroverzicht.php");
        exit();
    }
}
?>
