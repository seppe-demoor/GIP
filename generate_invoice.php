<?php
// Laad het startbestand voor initiële instellingen en sessiebeheer
require("start.php");
// Laad het PDO-bestand voor de databaseverbinding
require("pdo.php");
// Laad de Composer autoload file om externe libraries te gebruiken
require __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Haal gegevens op uit het POST-verzoek
    $projectId = $_POST['project_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $totalPrice = $_POST['total_price'] ?? null;
    $VAT = $_POST['VAT'] ?? null;
    $totalPayment = $_POST['total_payment'] ?? null;
    $pricePerHour = $_POST['price_per_hour'] ?? null;
    $totalHours = $_POST['total_hours'] ?? null;

    // Controleer of een project is geselecteerd
    if (!$projectId) {
        die("Geen project geselecteerd.");
    }

    try {
        if ($action === 'create') {
            // Haal klant- en projectgegevens op
            try {
                $projectQuery = $conn->query("SELECT * FROM `projects` WHERE `id` = '$projectId'");
                if (!$projectQuery) {
                    throw new Exception("Query error: " . $conn->error);
                }
                $projectData = $projectQuery->fetch_assoc();
                if (!$projectData) {
                    throw new Exception("Geen project gevonden met ID: $projectId");
                }
                $customerId = $projectData['customer_id'];
            } catch (Exception $e) {
                die($e->getMessage());
            }

         
            // Haal belastingtarief op uit klantgegevens
                try {
                    $customerQuery = $conn->query("SELECT countries.tax_rate, countries.name AS country_name, customers.* FROM customers JOIN countries ON customers.country = countries.id WHERE customers.id = '$customerId'");
                    if (!$customerQuery) {
                        throw new Exception("Query error: " . $conn->error);
                    }
                    $customerData = $customerQuery->fetch_assoc();
                    if (!$customerData) {
                        throw new Exception("Geen klant gevonden met ID: $customerId");
                    }
                    $taxRate = $customerData['tax_rate'];
                } catch (Exception $e) {
                    die($e->getMessage());
                }


            // Bereken totale gewerkte uren
            try {
                $totalHours = 0;
                $work_time_query = $conn->query("SELECT * FROM `work_time` WHERE `project_id` = $projectId AND `invoiced` = 0");
                while ($work = $work_time_query->fetch_assoc()) {
                    $totalHours += calculateHours($work['start_time'], $work['end_time']);
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Haal prijs per uur op uit de database op basis van het geselecteerde project-ID
            try {
                $projectPriceQuery = $conn->query("SELECT price_per_hour FROM `projects` WHERE `id` = $projectId");
                if (!$projectPriceQuery) {
                    throw new Exception("Query error: " . $conn->error);
                }
                $projectPriceData = $projectPriceQuery->fetch_assoc();
                if (!$projectPriceData) {
                    throw new Exception("Geen prijsgegevens gevonden voor project met ID: $projectId");
                }
                $pricePerHour = $projectPriceData['price_per_hour'];
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Bereken totale prijs en BTW
            if (is_numeric($totalHours) && is_numeric($pricePerHour)) {
                $totalPrice = $totalHours * $pricePerHour;
            }
            $VAT = $totalPrice * ($taxRate / 100);
            $totalPayment = $totalPrice + $VAT;

            $totalPrice = number_format($totalPrice, 2, '.', '');
            $VAT = number_format($VAT, 2, '.', '');
            $totalPayment = number_format($totalPayment, 2, '.', '');
            $pricePerHour = number_format($pricePerHour, 2, '.', '');

            // Maak de factuur aan in de database
            try {
                $invoiceDate = date('Y-m-d');
                $dueDate = date('Y-m-d', strtotime('+1 month'));
                $insertInvoiceQuery = "INSERT INTO `Invoices` (`customer_id`, `invoice_date`, `due_date`, `net_amount`, `VAT_percent`, `total_amount`, `hourly_rate`)
                                       VALUES ('$customerId', '$invoiceDate', '$dueDate', '$totalPrice', '$taxRate', '$totalPayment', '$pricePerHour')";
                if (!$conn->query($insertInvoiceQuery)) {
                    throw new Exception("Insert error: " . $conn->error);
                }
                $invoiceId = $conn->insert_id;
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Genereer PDF met stylesheet
            try {
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
                        <div class='invoice-title'>
                            <h1>FACTUUR</h1>
                        </div>
                        <div class='invoice-info'>
                            <p><strong>FACTUUR 2023-0030</strong></p>
                            <p>Aangemaakt op: " . date('d-m-Y') . "</p>
                            <p>Vervaldatum: " . date('d-m-Y', strtotime('+1 month')) . "</p>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Beschrijving</th>
                                    <th>Prijs excl. btw</th>
                                    <th>Btw-tarief</th>
                                    <th>Aantal</th>
                                    <th>Totaal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{$projectData['description']}</td>
                                    <td>€" . number_format($pricePerHour, 2) . "</td>
                                    <td>{$taxRate}%</td>
                                    <td>{$totalHours}u</td>
                                    <td>€{$totalPrice}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class='totals'>
                            <div class='total-row'>
                                <div class='label'>Subtotaal exclusief btw</div>
                                <div class='amount'>€{$totalPrice}</div>
                            </div>
                            <div class='total-row'>
                                <div class='label'>Btw ({$taxRate}%)</div>
                                <div class='amount'>€{$VAT}</div>
                            </div>
                            <div class='total-row'>
                                <div class='label'><strong>Te betalen</strong></div>
                                <div class='amount'><strong>€{$totalPayment}</strong></div>
                            </div>
                        </div>
                    </div>
                    <div class='footer'>
                        <p>Betaal met je bank-app</p>
                        <p>IBAN: BE84 7330 7114 2759</p>
                        <p>BIC: KRED BE BB</p>
                        <p>MEDEDELING: +++232/6954/52184+++</p>
                        <p>wannesmatthys@gmail.com</p>
                        <p>+32468183549</p>
                    </div>
                ", \Mpdf\HTMLParserMode::HTML_BODY);
                $pdfContent = $mpdf->Output('', 'S'); // Output als een string
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Sla PDF op in de database
            try {
                $insertPdfQuery = "INSERT INTO `pdf_invoices` (`invoice_id`, `pdf_data`, `created_at`) VALUES (?, ?, NOW())";
                $stmt = $conn->prepare($insertPdfQuery);
                if (!$stmt) {
                    throw new Exception("Prepare statement error: " . $conn->error);
                }
                $null = NULL;
                $stmt->bind_param('ib', $invoiceId, $null);
                $stmt->send_long_data(1, $pdfContent);
                if (!$stmt->execute()) {
                    throw new Exception("PDF insert error: " . $stmt->error);
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Update de gefactureerde status van de werkuren
            try {
                $updateQuery = $conn->query("UPDATE `work_time` SET `invoiced` = 1 WHERE `project_id` = '$projectId' AND `invoiced` = 0");
                if (!$updateQuery) {
                    throw new Exception("Update error: " . $conn->error);
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Redirect naar de homepage
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

function calculateHours($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $total_minutes = round(($end_time - $start_time) / 60);
    return round($total_minutes / 60, 2);
}
?>
