<?php
require("pdo.php");

try {
    if (!isset($_GET['invoice_id'])) {
        throw new Exception("Invoice ID is required.");
    }

    $invoice_id = $_GET['invoice_id'];
    $query = "SELECT pdf_data FROM pdf_invoices WHERE invoice_id = :invoice_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        throw new Exception("PDF not found.");
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdf_data = $row['pdf_data'];

    header('Content-Type: application/pdf');
    echo $pdf_data;
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
    exit;
}
?>
