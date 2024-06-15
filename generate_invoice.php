<?php
require("start.php");
require("pdo.php");
require __DIR__ . '/vendor/autoload.php'; // Make sure to include the Composer autoload file

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
            $customerQuery = $conn->query("SELECT countries.tax_rate, customers.* FROM customers JOIN countries ON customers.country = countries.id WHERE customers.id = '$customerId'");
            if (!$customerQuery) {
                throw new Exception("Query error: " . $conn->error);
            }
            $customerData = $customerQuery->fetch_assoc();
            if (!$customerData) {
                throw new Exception("Geen klant gevonden met ID: $customerId");
            }
            $taxRate = $customerData['tax_rate'];

            // Bereken totale gewerkte uren
            $totalHours = 0;
            $work_time_query = $conn->query("SELECT * FROM `work_time` WHERE `project_id` = $projectId AND `invoiced` = 0");
            while ($work = $work_time_query->fetch_assoc()) {
                $totalHours += calculateHours($work['start_time'], $work['end_time']);
            }

            // Fetch price per hour from the database based on the selected project ID
            $projectPriceQuery = $conn->query("SELECT price_per_hour FROM `projects` WHERE `id` = $projectId");
            if (!$projectPriceQuery) {
                throw new Exception("Query error: " . $conn->error);
            }
            $projectPriceData = $projectPriceQuery->fetch_assoc();
            if (!$projectPriceData) {
                throw new Exception("No price data found for project with ID: $projectId");
            }

            $pricePerHour = $projectPriceData['price_per_hour'];

            // Calculate total price and VAT
            if (is_numeric($totalHours) && is_numeric($pricePerHour)) {
                $totalPrice = $totalHours * $pricePerHour;
            }
            $VAT = $totalPrice * ($taxRate / 100);
            $totalPayment = $totalPrice + $VAT;

            // Ensure the values are properly formatted for SQL
            $totalPrice = number_format($totalPrice, 2, '.', '');
            $VAT = number_format($VAT, 2, '.', '');
            $totalPayment = number_format($totalPayment, 2, '.', '');
            $pricePerHour = number_format($pricePerHour, 2, '.', '');

            // Maak de factuur aan in de database
            $invoiceDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime('+1 month'));

            $insertInvoiceQuery = "INSERT INTO `Invoices` (`customer_id`, `invoice_date`, `due_date`, `net_amount`, `VAT_percent`, `total_amount`, `hourly_rate`)
                                    VALUES ('$customerId', '$invoiceDate', '$dueDate', '$totalPrice', '$taxRate', '$totalPayment', '$pricePerHour')";

            if (!$conn->query($insertInvoiceQuery)) {
                throw new Exception("Insert error: " . $conn->error);
            }

            // Fetch the last inserted invoice ID
            $invoiceId = $conn->insert_id;

            // Generate PDF with stylesheet
            $mpdf = new \Mpdf\Mpdf();
            $stylesheet = file_get_contents('style_inschrijving.css');
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML("
                <div class='header clearfix'>
                    <div class='left'>
                        <p class='xsmall'>Van</p>
                        <p class='large'><strong>Matthys</strong></p>
                        <p class='small'>Heurnestraat 59A</p>
                        <p class='small'>9700 Oudenaarde</p>
                        <p class='small'>Belgie</p>
                        <p class='small'>Btw: BE0800716390</p>
                    </div>
                    <div class='right'>
                        <p class='xsmall'>Aan</p>
                        <p class='large'><strong>{$customerData['name']}</strong></p>
                        <p class='small'>{$customerData['street']}</p>
                        <p class='small'>{$customerData['zip_code']} {$customerData['place']}</p>
                        <p class='small'>{$customerData['country_name']}</p>
                        <p class='small'>Btw: {$customerData['VAT_number']}</p>
                    </div>
                </div>

                <div class='content'>
                    <div style='display: flex; justify-content: space-between; align-items: flex-start;'>
                        <div class='left'>
                            <h1 style='font-size: 80px; color: grey;'>FACTUUR</h1>
                        </div>
                        <div class='right'>
                            <p style='font-size: 18px;'><strong>FACTUUR 2023-0030</strong></p>
                            <p class='small' style='color: grey;'>Aangemaakt op: " . date('d-m-Y') . "</p>
                            <p class='small' style='color: grey;'>Vervaldatum: " . date('d-m-Y', strtotime('+1 month')) . "</p>
                        </div>
                    </div>

                    <div style='display: flex; justify-content: space-between; align-items: flex-start;'>
                        <div style='width: 40%;'>
                            <h3 style='font-size: 16px; color: grey;'>Beschrijving</h3>
                            <p class='small'>{$projectData['description']}</p>
                        </div>
                        <div style='width: 15%;'>
                            <h3 style='font-size: 16px; color: grey;'>Prijs excl. btw</h3>
                            <p class='small'>€" . number_format($pricePerHour, 2) . "</p>
                        </div>
                        <div style='width: 13%;'>
                            <h3 style='font-size: 16px; color: grey;'>Btw-tarief</h3>
                            <p class='small'>{$taxRate}%</p>
                        </div>
                        <div style='width: 15%;'>
                            <h3 style='font-size: 16px; color: grey;'>Aantal</h3>
                            <p class='small'>{$totalHours}u</p>
                        </div>
                        <div style='width: 6%;'>
                            <h3 style='font-size: 16px; color: grey;'>Totaal</h3>
                            <p class='small'>€{$totalPrice}</p>
                        </div>
                    </div>

                    <div style='margin-top: 20px;'>
                        <div style='display: flex; justify-content: space-between;'>
                            <div style='width: 55%; text-align: right;'>
                                <h3 style='font-size: 16px; color: grey;'>Subtotaal exclusief btw</h3>
                            </div>
                            <div style='width: 15%; text-align: right;'>
                                <p class='small'>€{$totalPrice}</p>
                            </div>
                        </div>
                    </div>

                    <div style='display: flex; justify-content: flex-end;'>
                        <div style='width: 90%; text-align: right;'>
                            <h3 style='font-size: 16px; color: grey;'>Btw ({$taxRate}%)</h3>
                        </div>
                        <div style='width: 15%; text-align: right;'>
                            <p class='small'>€{$VAT}</p>
                        </div>
                    </div>

                    <div style='display: flex; justify-content: flex-end;'>
                        <div style='width: 90%; text-align: right;'>
                            <h3 style='font-size: 16px; color: grey;'>Te betalen</h3>
                        </div>
                        <div style='width: 15%; text-align: right;'>
                            <p class='small'><strong>€{$totalPayment}</strong></p>
                        </div>
                    </div>
                </div>

                <div class='footer'>
                    <p class='small'>Betaal met je bank-app</p>
                    <p class='small'>IBAN: BE84 7330 7114 2759</p>
                    <p class='small'>BIC: KRED BE BB</p>
                    <p class='small'>MEDEDELING: +++232/6954/52184+++</p>
                    <p class='small'>wannesmatthys@gmail.com</p>
                    <p class='small'>+32468183549</p>
                </div>
            ", \Mpdf\HTMLParserMode::HTML_BODY);
            $pdfContent = $mpdf->Output('', 'S'); // Output as a string

            // Save PDF to database
            $insertPdfQuery = "INSERT INTO `pdf_invoices` (`invoice_id`, `pdf_data`, `created_at`) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($insertPdfQuery);
            if (!$stmt) {
                throw new Exception("Prepare statement error: " . $conn->error);
            }

            // Bind the parameters
            $null = NULL;
            $stmt->bind_param('ib', $invoiceId, $null);

            // Send the actual BLOB data
            $stmt->send_long_data(1, $pdfContent);

            if (!$stmt->execute()) {
                throw new Exception("PDF insert error: " . $stmt->error);
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

// Functie om uren te berekenen
function calculateHours($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $total_minutes = round(($end_time - $start_time) / 60); // Omzetten naar minuten en afronden
    return round($total_minutes / 60, 2); // Omzetten naar uren met 2 decimalen
}
?>
