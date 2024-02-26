<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuw land maken</title>
</head>

<body>

<?php
require("start.php");
if (!isset($_SESSION['email'])) {
    //user is reeds aangemeld
    header("Location: loginPage.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require("pdo.php");
    $name = trim($_POST["name"]);
    $tax_rate = trim($_POST["tax_rate"]);
    $currency = trim($_POST["currency"]);
    $code = trim($_POST["code"]);
    $iso_code = trim($_POST["iso_code"]);
    $eu = isset($_POST["is_eu"]) ? 1 : 0;

    $query = "INSERT INTO `countries` (name, tax_rate, currency, code, iso_code, is_eu)
              VALUES (:name, :tax_rate, :currency, :code, :iso_code, :is_eu)";
    $values = [':name' => $name, ':tax_rate'=> $tax_rate,':currency'=> $currency, ':code'=>$code, ':iso_code'=>$iso_code, ':is_eu'=> $eu];
    try {
    $res = $pdo->prepare($query);
    $res->execute($values);
    } catch (PDOException $e) {
        //error in query
        echo "Query error:" . $e;
        die();
    }
    header("Location: countryOverzicht.php");
}
require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                    <div class="mb-3">
                        <label for="name" class="form-label">land</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="tax_rate" class="form-label">tax_rate</label>
                        <input type="text" class="form-control" id="tax_rate" name="tax_rate" pattern="[0-50]+" required>
                    </div>
                    <div class="mb-3">
                        <label for="currency" class="form-label">currency</label>
                        <input type="text" class="form-control" id="currency" name="currency" maxlength="3" required>
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">code</label>
                        <input type="text" class="form-control" id="code" name="code" maxlength="2" required>
                    </div>
                    <div class="mb-3">
                        <label for="iso_code" class="form-label">iso_code</label>
                        <input type="text" class="form-control" id="iso_code" name="iso_code" maxlength="3" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="eu" name="eu">
                        <label class="form-check-label" for="eu">EU</label>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-success">land aanmaken</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>
