<?php
require("start.php");
require("pdo.php");

// Functie om uren te berekenen
function calculateHours($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    return round(($end_time - $start_time) / 3600, 2); // Omzetten naar uren met 2 decimalen
}

// Functie om de klantgegevens op te halen
function getCustomerDetails($customerId) {
    global $conn;
    $customer_query = $conn->query("SELECT customers.*, countries.tax_rate, countries.name as country_name
                                FROM customers 
                                JOIN countries ON customers.country = countries.id 
                                WHERE customers.id = '$customerId'");

    if (!$customer_query) {
        die("Query error: " . $conn->error);
    }
    $customer = $customer_query->fetch_assoc();
    if (!$customer) {
        die("No customer found with ID: $customerId");
    }
    return $customer;
}

// Laden van geselecteerde projectinformatie uit de querystring
$selectedProjectId = $_GET["project_id"] ?? null;
if (!$selectedProjectId) {
    die("Geen project geselecteerd.");
}

// Check if there are any work times that have not been invoiced
$invoicedCheckQuery = $conn->query("SELECT COUNT(*) as count FROM `work_time` WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
if (!$invoicedCheckQuery) {
    die("Query error: " . $conn->error);
}
$invoicedCheck = $invoicedCheckQuery->fetch_assoc();
if ($invoicedCheck['count'] == 0) {
    die("Geen uren beschikbaar om te factureren.");
}

// Haal projectgegevens op
$selectedProjectQuery = $conn->query("SELECT * FROM `projects` WHERE `id` = $selectedProjectId");
if (!$selectedProjectQuery) {
    die("Query error: " . $conn->error);
}
$selectedProject = $selectedProjectQuery->fetch_assoc();
if (!$selectedProject) {
    die("Geen project gevonden met ID: $selectedProjectId");
}

// Haal klantgegevens op
$customerDetails = getCustomerDetails($selectedProject['customer_id']);

$totalPrice = 0;
$VAT = 0;

// Bereken totale gewerkte uren
$totalHours = 0;
$work_time_query = $conn->query("SELECT * FROM `work_time` WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
while ($work = $work_time_query->fetch_assoc()) {
    $totalHours += calculateHours($work['start_time'], $work['end_time']);
}

// Fetch price per hour from the database based on the selected project ID
$projectPriceQuery = $conn->query("SELECT price_per_hour FROM `projects` WHERE `id` = $selectedProjectId");
if (!$projectPriceQuery) {
    die("Query error: " . $conn->error);
}
$projectPriceData = $projectPriceQuery->fetch_assoc();
if (!$projectPriceData) {
    die("No price data found for project with ID: $selectedProjectId");
}

$priceperhour = $projectPriceData['price_per_hour']; // Assign price per hour from the database

// Calculate total price
if (is_numeric($totalHours) && is_numeric($priceperhour)) {
    $totalPrice = number_format($totalHours * $priceperhour, 2);
}
?>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            size: A4; /* instellingen voor A4-formaat */
        }

        @page {
            margin: 1cm; /* marges instellen voor A4-formaat */
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
            <?php
            // Laad klantgegevens als geselecteerd project bestaat
            if ($selectedProject) {
                echo "<p class='large'><strong>" . $customerDetails['name'] . "</strong></p>";
                echo "<p class='small'>" . $customerDetails['street'] . "</p>";
                echo "<p class='small'>" . $customerDetails['zip_code'] . " " . $customerDetails['place'] . "</p>";
                echo "<p class='small'>" . $customerDetails['country_name'] . "</p>";
                echo "<p class='small'>Btw: " . $customerDetails['VAT_number'] . "</p>";
            } else {
                echo "<p class='small'>Selecteer eerst een project om de klantgegevens weer te geven.</p>";
            }
            ?>
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
                <?php
                $vervalDatum = date('d-m-Y', strtotime('+1 month'));
                echo "<p class='small' style='color: grey;'>Vervaldatum: $vervalDatum</p>";
                ?>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div style="width: 40%;">
                <h3 style="font-size: 16px; color: grey;">Beschrijving</h3>
                <p class="small"><?= $selectedProject['description'] ?></p>
            </div>

            <div style="width: 15%;">
                <h3 style="font-size: 16px; color: grey;">Prijs excl. btw</h3>
                <p class="small">€<?= number_format($priceperhour, 2) ?></p>
            </div>

            <div style="width: 13%;">
                <h3 style="font-size: 16px; color: grey;">Btw-tarief</h3>
                <p class="small"><?= $customerDetails['tax_rate'] ?>%</p>
            </div>

            <div style="width: 15%;">
                <h3 style="font-size: 16px; color: grey;">Aantal</h3>
                <p class="small"><?= $totalHours ?>u</p>
            </div>

            <div style="width: 6%;">
                <h3 style="font-size: 16px; color: grey;">Totaal</h3>
                <?php
                    $totalPrice = number_format($totalHours * $priceperhour, 2);
                    echo "<p class='small'>€$totalPrice</p>";
                ?>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 155%; text-align: right;">
                    <h3 style="font-size: 16px; color: grey;">Subtotaal exclusief btw</h3>
                </div>
                <div style="width: 15%; text-align: right;">
                    <p class="small">€<?= $totalPrice ?></p>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 90%; text-align: right;">
                <h3 style="font-size: 16px; color: grey;">Btw (<?= $customerDetails['tax_rate'] ?>%)</h3>
            </div>
            <div style="width: 15%; text-align: right;">
                <?php
                $VAT = 0; // Initialisatie van de variabele VAT

                // Verwijder alle niet-numerieke tekens, inclusief komma's
                $totalPrice = preg_replace("/[^0-9.]/", "", $totalPrice);

                // Converteer $totalPrice naar een float
                $totalPrice = floatval($totalPrice);

                // Controleer of $totalPrice numeriek is voordat je de berekening uitvoert
                if (is_numeric($totalPrice) && is_numeric($customerDetails['tax_rate'])) {
                    // Bereken de BTW
                    $VAT = $totalPrice * ($customerDetails['tax_rate'] / 100);
                
                    // Formatteer het BTW-bedrag met twee cijfers achter de komma
                    $formattedVAT = number_format($VAT, 2);
                }
                 
                // Laat de BTW zien
                echo "<p class='small'>€$formattedVAT</p>";
                ?>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 90%; text-align: right;">
                <h3 style="font-size: 16px; color: grey;">Te betalen</h3>
            </div>
            <div style="width: 15%; text-align: right;">
                <?php
                $totalPayment = number_format($totalPrice + $VAT, 2);
                echo "<p class='small'><strong>€$totalPayment</strong></p>";
                ?>
            </div>
        </div>
    </div>

    <script>
        // Functie om afdrukken te activeren bij Ctrl + P
        window.addEventListener("keydown", function(event) {
            if (event.ctrlKey && event.key === "p") {
                event.preventDefault(); // Voorkom standaard Ctrl + P
                window.print(); // Activeer afdrukken
            }
        });
    </script>

    <?php
    // Update invoiced status from 0 to 1 after generating the invoice
    $updateQuery = $conn->query("UPDATE `work_time` SET `invoiced` = 1 WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
    if (!$updateQuery) {
        die("Update error: " . $conn->error);
    }
    ?>
</body>
