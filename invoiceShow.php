<?php
require("start.php");
require("pdo.php");

function calculateHours($start, $end) {
    $start_time = strtotime($start);
    $end_time = strtotime($end);
    $total_minutes = round(($end_time - $start_time) / 60);
    return round($total_minutes / 60, 2);
}

function getCustomerDetails($customerId) {
    global $conn;
    try {
        $customer_query = $conn->query("SELECT customers.*, countries.tax_rate, countries.name as country_name
                                        FROM customers 
                                        JOIN countries ON customers.country = countries.id 
                                        WHERE customers.id = '$customerId'");
        if (!$customer_query) {
            throw new Exception("Query error: " . $conn->error);
        }

        $customer = $customer_query->fetch_assoc();
        if (!$customer) {
            throw new Exception("No customer found with ID: $customerId");
        }
        return $customer;
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

$selectedProjectId = $_POST["project_id"] ?? null;
if (!$selectedProjectId) {
    die("Geen project geselecteerd.");
}

try {
    $invoicedCheckQuery = $conn->query("SELECT COUNT(*) as count FROM `work_time` WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
    if (!$invoicedCheckQuery) {
        throw new Exception("Query error: " . $conn->error);
    }
    $invoicedCheck = $invoicedCheckQuery->fetch_assoc();
    if ($invoicedCheck['count'] == 0) {
        throw new Exception("Geen uren beschikbaar om te factureren.");
    }

    $selectedProjectQuery = $conn->query("SELECT * FROM `projects` WHERE `id` = $selectedProjectId");
    if (!$selectedProjectQuery) {
        throw new Exception("Query error: " . $conn->error);
    }
    $selectedProject = $selectedProjectQuery->fetch_assoc();
    if (!$selectedProject) {
        throw new Exception("Geen project gevonden met ID: $selectedProjectId");
    }

    $customerDetails = getCustomerDetails($selectedProject['customer_id']);

    $totalHours = 0;
    $work_time_query = $conn->query("SELECT * FROM `work_time` WHERE `project_id` = $selectedProjectId AND `invoiced` = 0");
    while ($work = $work_time_query->fetch_assoc()) {
        $totalHours += calculateHours($work['start_time'], $work['end_time']);
    }

    $projectPriceQuery = $conn->query("SELECT price_per_hour FROM `projects` WHERE `id` = $selectedProjectId");
    if (!$projectPriceQuery) {
        throw new Exception("Query error: " . $conn->error);
    }
    $projectPriceData = $projectPriceQuery->fetch_assoc();
    if (!$projectPriceData) {
        throw new Exception("No price data found for project with ID: $selectedProjectId");
    }

    $priceperhour = $projectPriceData['price_per_hour'];
    $totalPrice = number_format($totalHours * $priceperhour, 2);
} catch (Exception $e) {
    echo "<script>
        alert('{$e->getMessage()}');
        window.location.href = 'Invoice.php';
    </script>";
    exit();
}
?>

<head>
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
            <?php if ($selectedProject): ?>
                <p class="large"><strong><?php echo $customerDetails['name']; ?></strong></p>
                <p class="small"><?php echo $customerDetails['street']; ?></p>
                <p class="small"><?php echo $customerDetails['zip_code'] . " " . $customerDetails['place']; ?></p>
                <p class="small"><?php echo $customerDetails['country_name']; ?></p>
                <p class="small">Btw: <?php echo $customerDetails['VAT_number']; ?></p>
            <?php else: ?>
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
                <?php
                $totalPrice = preg_replace("/[^0-9.]/", "", $totalPrice);
                $totalPrice = floatval($totalPrice);
                if (is_numeric($totalPrice) && is_numeric($customerDetails['tax_rate'])) {
                    $VAT = number_format($totalPrice * ($customerDetails['tax_rate'] / 100), 2);
                }
                echo "<p class='small'>€$VAT</p>";
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
