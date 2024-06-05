<?php
require("start.php");
require("pdo.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = $_POST['project_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $totalPrice = $_POST['total_price'] ?? null;
    $VAT = $_POST['VAT'] ?? null;
    $totalPayment = $_POST['total_payment'] ?? null;
    $pricePerHour = $_POST['price_per_hour'] ?? null;
    $totalHours = $_POST['total_hours'] ?? null;

    if (!$projectId) {
        die("Geen project geselecteerd.");
    }

    try {
        if ($action === 'create') {
            // Haal klant- en projectgegevens op
            $projectQuery = $conn->query("SELECT * FROM `projects` WHERE `id` = '$projectId'");
            if (!$projectQuery) {
                throw new Exception("Query error: " . $conn->error);
            }
            $projectData = $projectQuery->fetch_assoc();
            if (!$projectData) {
                throw new Exception("Geen project gevonden met ID: $projectId");
            }
            $customerId = $projectData['customer_id'];

            // Haal belastingtarief op uit klantgegevens
            $customerQuery = $conn->query("SELECT countries.tax_rate FROM customers JOIN countries ON customers.country = countries.id WHERE customers.id = '$customerId'");
            if (!$customerQuery) {
                throw new Exception("Query error: " . $conn->error);
            }
            $customerData = $customerQuery->fetch_assoc();
            if (!$customerData) {
                throw new Exception("Geen klant gevonden met ID: $customerId");
            }
            $taxRate = $customerData['tax_rate'];

            // Maak de factuur aan in de database
            $invoiceDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime('+1 month'));

            $insertInvoiceQuery = "INSERT INTO `Invoices` (`customer_id`, `invoice_date`, `due_date`, `net_amount`, `VAT_percent`, `total_amount`, `hourly_rate`)
                                    VALUES ('$customerId', '$invoiceDate', '$dueDate', '$totalPrice', '$taxRate', '$totalPayment', '$pricePerHour')";

            if (!$conn->query($insertInvoiceQuery)) {
                throw new Exception("Insert error: " . $conn->error);
            }

            // Update invoiced status van de werkuren
            $updateQuery = $conn->query("UPDATE `work_time` SET `invoiced` = 1 WHERE `project_id` = '$projectId' AND `invoiced` = 0");
            if (!$updateQuery) {
                throw new Exception("Update error: " . $conn->error);
            }

            header('Location: homePage.php');
            exit();

        } elseif ($action === 'cancel') {
            header('Location: homePage.php');
            exit();
        } else {
            throw new Exception("Ongeldige actie.");
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }
}
?>
