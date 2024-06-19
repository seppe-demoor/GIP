<?php
// Inclusie van vereiste bestanden voor databaseverbinding en configuratie
require("start.php");
require("pdo.php");

// Functie: bereken aantal uren tussen twee tijdstippen
function calculateHours($start, $end) {
    $start_time = strtotime($start); // Zet starttijd om naar timestamp
    $end_time = strtotime($end); // Zet eindtijd om naar timestamp
    $total_minutes = round(($end_time - $start_time) / 60); // Bereken het totaal aantal minuten
    return round($total_minutes / 60, 2); // Converteer naar uren en rond af op twee decimalen
}

// Functie: haal klantgegevens op basis van klant-ID
function getCustomerDetails($customerId) {
    global $conn; // Gebruik de globale databaseverbinding
    try {
        // Query om klantgegevens op te halen inclusief belastingtarief van het land
        $customer_query = $conn->query("SELECT customers.*, countries.tax_rate, countries.name as country_name
                                        FROM customers 
                                        JOIN countries ON customers.country = countries.id 
                                        WHERE customers.id = '$customerId'");
        // Gooi een fout als de query mislukt
        if (!$customer_query) {
            throw new Exception("Query error: " . $conn->error);
        }

        // Haal klantgegevens op
        $customer = $customer_query->fetch_assoc();
        // Gooi een fout als er geen klant gevonden wordt met het opgegeven ID
        if (!$customer) {
            throw new Exception("No customer found with ID: $customerId");
        }
        return $customer; // Geef de klantgegevens terug
    } catch (Exception $e) {
        die($e->getMessage()); // Stop het script en geef de foutmelding weer
    }
}

// Haal het geselecteerde project-ID op uit POST-gegevens of geef een foutmelding als er geen is
$selectedProjectId = $_POST["project_id"] ?? null;
if (!$selectedProjectId) {
    die("Geen project geselecteerd.");
}

try {
    // Controleer of er niet-gefactureerde uren beschikbaar zijn voor het geselecteerde project
    $invoicedCheckQuery = $conn->query("SELECT COUNT(*) as count FROM `work_time` WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
    if (!$invoicedCheckQuery) {
        throw new Exception("Query error: " . $conn->error);
    }
    $invoicedCheck = $invoicedCheckQuery->fetch_assoc();
    // Gooi een fout als er geen niet-gefactureerde uren zijn voor het project
    if ($invoicedCheck['count'] == 0) {
        throw new Exception("Geen uren beschikbaar om te factureren.");
    }

    // Haal projectdetails op voor het geselecteerde project
    $selectedProjectQuery = $conn->query("SELECT * FROM `projects` WHERE `id` = $selectedProjectId");
    if (!$selectedProjectQuery) {
        throw new Exception("Query error: " . $conn->error);
    }
    $selectedProject = $selectedProjectQuery->fetch_assoc();
    // Gooi een fout als er geen project gevonden wordt met het opgegeven project-ID
    if (!$selectedProject) {
        throw new Exception("Geen project gevonden met ID: $selectedProjectId");
    }

    // Haal klantgegevens op voor het geselecteerde project
    $customerDetails = getCustomerDetails($selectedProject['customer_id']);

    // Bereken totaal aantal uren voor niet-gefactureerde werktijd voor het project
    $totalHours = 0;
    $work_time_query = $conn->query("SELECT * FROM `work_time` WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
    while ($work = $work_time_query->fetch_assoc()) {
        $totalHours += calculateHours($work['start_time'], $work['end_time']);
    }

    // Haal prijs per uur op voor het geselecteerde project
    $projectPriceQuery = $conn->query("SELECT price_per_hour FROM `projects` WHERE `id` = $selectedProjectId");
    if (!$projectPriceQuery) {
        throw new Exception("Query error: " . $conn->error);
    }
    $projectPriceData = $projectPriceQuery->fetch_assoc();
    // Gooi een fout als er geen prijsgegevens gevonden worden voor het project met het opgegeven ID
    if (!$projectPriceData) {
        throw new Exception("No price data found for project with ID: $selectedProjectId");
    }

    // Haal prijs per uur op en bereken totaalprijs voor de factuur
    $priceperhour = $projectPriceData['price_per_hour'];
    $totalPrice = number_format($totalHours * $priceperhour, 2);
} catch (Exception $e) {
    // Vang uitzonderingen op en geef een JavaScript-waarschuwing weer met de foutmelding, stuur de gebruiker door naar een foutpagina
    echo "<script>
        alert('{$e->getMessage()}');
        window.location.href = 'Invoice.php';
    </script>";
    exit(); // Stop verdere uitvoering van het script na het weergeven van de foutmelding
}
?>

<head>
    <!-- Stijlinstellingen voor de factuurpagina -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            size: A4;
        }
        @page {
            margin: 1cm;
        }
        .header {
            background-color: #008BBA;
            color: white;
            padding: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .content {
            padding: 20px;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: center;
        }
        .xsmall {
            font-size: 13px;
        }
        .small {
            font-size: 15px;
        }
        .large {
            font-size: 24px;
        }
        .left {
            text-align: left;
        }
        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <!-- Factuurinhoud dynamisch gegenereerd op basis van opgehaalde gegevens -->
    <div class="header">
        <div class="left">
            <p class="xsmall">Van</p>
            <p class="large"><strong>Matthys</strong></p>
            <p class="small">Heurnestraat 59A</p>
            <p class="small">9700 Oudenaarde</p>
            <p class="small">Belgie</p>
            <p class="small">Btw: BE0800716390</p>
        </div>
        <div class="right">
            <p class="xsmall">Aan</p>
            <!-- Dynamische weergave van klantgegevens -->
            <?php if ($selectedProject): ?>
                <p class="large"><strong><?php echo $customerDetails['name']; ?></strong></p>
                <p class="small"><?php echo $customerDetails['street']; ?></p>
                <p class="small"><?php echo $customerDetails['zip_code'] . " " . $customerDetails['place']; ?></p>
                <p class="small"><?php echo $customerDetails['country_name']; ?></p>
                <p class="small">Btw: <?php echo $customerDetails['VAT_number']; ?></p>
            <?php else: ?>
                <!-- Geef een melding weer als er geen project is geselecteerd -->
                <p class="small">Selecteer eerst een project om de klantgegevens weer te geven.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="left">
                <h1 style="font-size: 80px; color: grey;">FACTUUR</h1>
            </div>
            <div class="right">
                <p style="font-size: 18px;"><strong>FACTUUR <?php echo date("Y") ?>-0030</strong></p>
                <p class="small" style="color: grey;">Aangemaakt op: <?php echo date("d-m-Y") ?></p>
                <?php $vervalDatum = date('d-m-Y', strtotime('+1 month')); ?>
                <p class="small" style="color: grey;">Vervaldatum: <?php echo $vervalDatum; ?></p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="width: 40%;">
                <h3 style="font-size: 16px; color: grey;">Beschrijving</h3>
                <p class="small"><?php echo $selectedProject['description']; ?></p>
            </div>
            <div style="width: 15%;">
                <h3 style="font-size: 16px; color: grey;">Prijs excl. btw</h3>
                <p class="small">€<?php echo number_format($priceperhour, 2); ?></p>
            </div>
            <div style="width: 13%;">
                <h3 style="font-size: 16px; color: grey;">Btw-tarief</h3>
                <p class="small"><?php echo $customerDetails['tax_rate']; ?>%</p>
            </div>
            <div style="width: 15%;">
                <h3 style="font-size: 16px; color: grey;">Aantal</h3>
                <p class="small"><?php echo $totalHours; ?>u</p>
            </div>
            <div style="width: 6%;">
                <h3 style="font-size: 16px; color: grey;">Totaal</h3>
                <p class="small">€<?php echo $totalPrice; ?></p>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 155%; text-align: right;">
                    <h3 style="font-size: 16px; color: grey;">Subtotaal exclusief btw</h3>
                </div>
                <div style="width: 15%; text-align: right;">
                    <p class="small">€<?php echo $totalPrice; ?></p>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 90%; text-align: right;">
                <h3 style="font-size: 16px; color: grey;">Btw (<?php echo $customerDetails['tax_rate']; ?>%)</h3>
            </div>
            <div style="width: 15%; text-align: right;">
                <!-- Bereken BTW-bedrag en geef het weer -->
                <?php
                $totalPrice = preg_replace("/[^0-9.]/", "", $totalPrice); // Verwijder niet-numerieke tekens uit totaalprijs
                $totalPrice = floatval($totalPrice); // Converteer totaalprijs naar een float
                if (is_numeric($totalPrice) && is_numeric($customerDetails['tax_rate'])) {
                    $VAT = number_format($totalPrice * ($customerDetails['tax_rate'] / 100), 2); // Bereken BTW
                }
                echo "<p class='small'>€$VAT</p>"; // Geef BTW-bedrag weer
                ?>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 90%; text-align: right;">
                <h3 style="font-size: 16px; color: grey;">Te betalen</h3>
            </div>
            <div style="width: 15%; text-align: right;">
                <!-- Bereken totaal te betalen bedrag en geef het weer -->
                <?php
                $totalPayment = number_format($totalPrice + $VAT, 2); // Bereken totaal te betalen bedrag
                echo "<p class='small'><strong>€$totalPayment</strong></p>"; // Geef totaal te betalen bedrag weer
                ?>
            </div>
        </div>

        <!-- Formulier voor factuuracties -->
        <form method="post" action="generate_invoice.php">
            <input type="hidden" name="project_id" value="<?php echo $selectedProjectId; ?>">
            <input type="hidden" name="total_price" value="<?php echo $totalPrice; ?>">
            <input type="hidden" name="VAT" value="<?php echo $VAT; ?>">
            <input type="hidden" name="total_payment" value="<?php echo $totalPayment; ?>">
            <input type="hidden" name="price_per_hour" value="<?php echo $priceperhour; ?>">
            <input type="hidden" name="total_hours" value="<?php echo $totalHours; ?>">
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <button type="submit" name="action" value="create">Maak Factuur</button>
                <button type="submit" name="action" value="cancel">Annuleren</button>
            </div>
        </form>
    </div>
</body>
