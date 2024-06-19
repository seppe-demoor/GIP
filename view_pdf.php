<?php
require("pdo.php"); // Laad het bestand dat de verbinding met de database instelt via PDO

try {
    // Controleer of er een 'invoice_id' parameter is meegegeven in de URL
    if (!isset($_GET['invoice_id'])) {
        // Gooi een error als 'invoice_id' ontbreekt
        throw new Exception("Invoice ID is required.");
    }

    // Haal de 'invoice_id' op uit de URL-parameters
    $invoice_id = $_GET['invoice_id'];
    // Bereid een SQL-query voor om de PDF-gegevens op te halen voor de opgegeven 'invoice_id'
    $query = "SELECT pdf_data FROM pdf_invoices WHERE invoice_id = :invoice_id";
    $stmt = $pdo->prepare($query); // Bereid de query voor en gebruik pdo als inlog gegevens voor een conectie met de database
    // Bind de 'invoice_id' parameter aan de query, waarbij PDO::PARAM_INT aangeeft dat het een integer is
    $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
    // Voer de query uit
    $stmt->execute();

    // Controleer of er resultaten zijn gevonden
    if ($stmt->rowCount() == 0) {
        // Gooi een error als er geen resultaten zijn
        throw new Exception("PDF not found.");
    }

    // Haal de resultaten op als een associatieve array
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // Haal de 'pdf_data' op uit de resultaten
    $pdf_data = $row['pdf_data'];

    // Stel de Content-Type header in om aan te geven dat de inhoud een PDF is
    header('Content-Type: application/pdf');
    // Geef de PDF-gegevens weer
    echo $pdf_data;
} catch (Exception $e) {
    // Als er een algemene error optreedt, toon de foutboodschap
    echo $e->getMessage();
    exit; // Stop de verdere uitvoering van het script
} catch (PDOException $e) {
    // Als er een PDO-specifieke error optreedt, toon de databasefoutboodschap
    echo 'Database error: ' . $e->getMessage();
    exit; // Stop de verdere uitvoering van het script
}
?>
