<!DOCTYPE html>
<?php
require("start.php");
require("pdo.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $name = trim($_POST["name"]);
    $tax_rate = trim($_POST["tax_rate"]);
    $currency =trim($_POST["currency"]);
    $code = trim($_POST["code"]);
    $iso_code = trim($_POST["iso_code"]);

    $query = "UPDATE `countries` 
              SET name = :name, tax_rate = :tax_rate, currency = :currency, code = :code, `iso_code` = :iso 
              WHERE `id` = :id";
    $values = [":id" => $id, ":name" => $name, "tax_rate" => $tax_rate, "currency" => $currency, "code" => $code, "iso" => $iso_code];
    // execute query

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
        header("Location: countryOverzicht.php");
        exit;
    } catch (PDOException $e) {
        // error in de query
        echo 'Query error<br>' . $e; 
        die();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $query = "SELECT * FROM countries WHERE id = :id";
    $values = ['id' => $id];
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);
        $country = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query error: ' . $e->getMessage();
        die();
    }
} else {
    header("Location: countryOverzicht.php");
    exit;
}


require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
            <input type="hidden" name="shop_id" value="<?php echo $country['id']; ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Gebruikersnaam</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo $country['name'];?>">
                    </div>
                    <div class="mb-3">
                        <label for="tax_rate" class="form-label">tax_rate</label>
                        <input type="text" class="form-control" id="tax_rate" name="tax_rate" required value="<?php echo $country['tax_rate']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="currency" class="form-label">currency</label>
                        <input type="text" class="form-control" id="currency" name="currency" required value="<?php echo $country['currency']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">code</label>
                        <input type="text" class="form-control" id="code" name="code" required value="<?php echo $country['code']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="iso_code" class="form-label">iso_code</label>
                        <input type="text" class="form-control" id="iso_code" name="iso_code" required value="<?php echo $country['iso_code']; ?>">
                    </div>
                    <br>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-success">Land updaten</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>
<?php
require("footer.php");
?>