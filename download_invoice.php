<?php
require("start.php");
require("pdo.php");

if (isset($_GET['invoice_number'])) {
    $invoiceId = intval($_GET['invoice_number']);

    // Prepare and execute the statement
    $stmt = $conn->prepare("SELECT Pdf FROM Invoices WHERE invoice_number = ?");
    $stmt->bind_param("i", $invoiceId);
    $stmt->execute();
    $stmt->store_result();

    // Check if we have a result
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($pdfData);
        $stmt->fetch();

        // Set headers to force download as PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="invoice_' . $invoiceId . '.pdf"');
        header('Content-Length: ' . strlen($pdfData));
        echo $pdfData;
    } else {
        echo "No PDF found for this invoice.";
    }

    $stmt->close();
} else {
    echo "No invoice ID provided.";
}

$conn->close();
?>
