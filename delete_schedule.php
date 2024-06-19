<?php 
// Laad het bestand voor de databaseverbinding
require_once('pdo.php');

try {
    // Controleer of het 'id' parameter is meegegeven in de URL
    if (!isset($_GET['id'])) {
        // Gooi een uitzondering als de 'id' parameter niet is meegegeven
        throw new Exception('Undefined Schedule ID.');
    }

    // Voer de SQL-query uit om een record te verwijderen uit de 'work_time' tabel met het opgegeven id
    $delete = $conn->query("DELETE FROM `work_time` WHERE id = '{$_GET['id']}'");

    // Controleer of de verwijdering succesvol was
    if ($delete) {
        // Als de verwijdering succesvol was, redirect naar de homepagina
        echo "<script> location.replace('./homePage.php') </script>";
    } else {
        // Gooi een uitzondering als er een fout optreedt bij het verwijderen
        throw new Exception("An Error occurred.\nError: " . $conn->error . "\nSQL: " . $conn->last_query);
    }
} catch (Exception $e) {
    // Vang de uitzondering op en toon een foutmelding, redirect naar de hoofdpagina
    echo "<script> alert('{$e->getMessage()}'); location.replace('./') </script>";
} finally {
    // Sluit de databaseverbinding
    $conn->close();
    // Stop de uitvoering van het script
    exit;
}
?>
