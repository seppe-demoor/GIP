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
                $projectData = $projectQuery->fetch_assoc(); // projectQuery ophalen
                if (!$projectData) {
                    throw new Exception("Geen project gevonden met ID: $projectId");
                }
                $customerId = $projectData['customer_id'];
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Haal belastingtarief op uit klantgegevens
            try {
                $customerQuery = $conn->query("SELECT countries.tax_rate, customers.* FROM customers JOIN countries ON customers.country = countries.id WHERE customers.id = '$customerId'");
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
                // Initialiseer de variabele om het totaal aantal uren bij te houden
                $totalHours = 0;
            
                // Voer een SQL-query uit om alle werkuren op te halen voor het opgegeven project die nog niet gefactureerd zijn
                $work_time_query = $conn->query("SELECT * FROM `work_time` WHERE `project_id` = $projectId AND `invoiced` = 0");
            
                // Loop door elke rij in de resultaten van de query
                while ($work = $work_time_query->fetch_assoc()) {
                    // Bereken de gewerkte uren voor elke rij en tel deze op bij het totaal
                    $totalHours += calculateHours($work['start_time'], $work['end_time']);
                }
            } catch (Exception $e) {
                // Als er een uitzondering wordt gegooid, toon de foutmelding en stop de uitvoering
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
                    // Controleer of zowel $totalHours als $pricePerHour numerieke waarden zijn
                    $totalPrice = $totalHours * $pricePerHour;
                    // Bereken de totale prijs door het totaal aantal uren te vermenigvuldigen met de prijs per uur
                }
                $VAT = $totalPrice * ($taxRate / 100);
                // Bereken de BTW door de totale prijs te vermenigvuldigen met het belastingtarief gedeeld door 100
                $totalPayment = $totalPrice + $VAT;
                // Bereken het totale te betalen bedrag door de BTW bij de totale prijs op te tellen

                // Zorg ervoor dat de waarden correct zijn geformatteerd voor SQL
                $totalPrice = number_format($totalPrice, 2, '.', ''); // Formatteer de totale prijs met 2 decimalen en gebruik een punt als decimaalteken
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
                // Haal de laatst ingevoegde factuur-ID op
                $invoiceId = $conn->insert_id;
            } catch (Exception $e) {
                die($e->getMessage());
            }

            // Genereer PDF met stylesheet
            try {
                // Maak een nieuwe instantie van de Mpdf-klasse
                    $mpdf = new \Mpdf\Mpdf();

                    // Lees de inhoud van het CSS-stylesheet bestand
                    $stylesheet = file_get_contents('style_inschrijving.css');

                    // Schrijf de CSS-stylesheet naar het PDF-document, waarbij het wordt geïnterpreteerd als CSS voor de header
                    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);

                    // Begin met het schrijven van HTML-inhoud naar het PDF-document
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
                // Bind de parameters
                $null = NULL;
                $stmt->bind_param('ib', $invoiceId, $null);
                // Stuur de daadwerkelijke BLOB-gegevens
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
            // Redirect naar de homepage bij annuleren
            header('Location: homePage.php');
            exit();
        } else {
            // Gooi een uitzondering bij een ongeldige actie
            throw new Exception("Ongeldige actie.");
        }
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

// Functie om uren te berekenen
function calculateHours($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end); // Converteer de eindtijd naar een UNIX-timestamp
    $total_minutes = round(($end_time - $start_time) / 60); // Omzetten naar minuten en afronden
    return round($total_minutes / 60, 2); // Omzetten naar uren met 2 decimalen
}
?>
