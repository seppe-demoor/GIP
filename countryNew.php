<?php
require "vendor/autoload.php";
use Ramsey\Uuid\Uuid;
require("header.php");
// Inclusief het startbestand voor de sessie
require("start.php");

// Controleren of de gebruiker is aangemeld
if (!isset($_SESSION['email'])) {
    // Gebruiker is nog niet aangemeld, doorsturen naar de inlogpagina
    header("Location: loginPage.php");
    exit;
}   

// Controleren of het verzoek een POST-verzoek is
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Inclusief het PDO-bestand voor databaseverbinding
    require("pdo.php");
    
    $id = Uuid::uuid4();
    //printf("ID example: %s", $id->toString());
    $name = trim($_POST["name"]);
    $tax_rate = trim($_POST["tax_rate"]);
    $currency = trim($_POST["currency"]);
    $code = trim($_POST["code"]);
    $iso_code = trim($_POST["iso_code"]);
    $eu = isset($_POST["is_eu"]) ? 1 : 0;

    // Query voor het invoegen van het nieuwe land in de database
    $query = "INSERT INTO `countries` (name, tax_rate, currency, code, iso_code, is_eu)
              VALUES (:name, :tax_rate, :currency, :code, :iso_code, :is_eu)";
    
    // Array met de te binden waarden voor de query
    $values = [':name' => $name, ':tax_rate'=> $tax_rate,':currency'=> $currency, ':code'=>$code, ':iso_code'=>$iso_code, ':is_eu'=> $eu];
    
    try {
        // Voorbereiden van de query en uitvoeren met de ontvangen gegevens
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        // Foutafhandeling bij een fout in de query
        echo "Query error:" . $e;
        die();
    }
    
    // Doorsturen naar het overzicht van landen na succesvol toevoegen
    header("Location: countryOverzicht.php");
}

// Inclusief het headerbestand voor de opmaak van de pagina
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
                <!-- Formulier voor het invoeren van de gegevens voor een nieuw land -->
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
<?php
    require("footer.php");
?>