<?php
require "vendor/autoload.php";
use Ramsey\Uuid\Uuid;
require("start.php");

// Controleer of de gebruiker is aangemeld
if (!isset($_SESSION['username'])) {
    // Als de gebruiker niet is aangemeld, stuur ze naar de inlogpagina
    header("Location: loginPage.php");
    exit;
}

// Verwerk het formulier als het is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verbindingsbestand met de database
    require("pdo.php");

    // Unieke factuur-ID genereren
    $factuur_id = Uuid::uuid4();

    // Factuurinformatie van het formulier ophalen
    $naam_klant = trim($_POST["naam_klant"]);
    $datum = date("Y-m-d"); // Huidige datum als factuurdatum
    $product = trim($_POST["product"]);
    $hoeveelheid = intval($_POST["hoeveelheid"]);
    $eenheidsprijs = floatval($_POST["eenheidsprijs"]);
    $btw_percentage = 21; // BTW-percentage (bijvoorbeeld 21%)
    
    // Berekeningen voor het factuurbedrag
    $subtotaal = $hoeveelheid * $eenheidsprijs;
    $btw_bedrag = ($subtotaal * $btw_percentage) / 100;
    $totaal_bedrag = $subtotaal + $btw_bedrag;

    // Factuurinformatie invoegen in de database
    $query = "INSERT INTO `facturen` (factuur_id, naam_klant, datum, product, hoeveelheid, eenheidsprijs, btw_percentage, subtotaal, btw_bedrag, totaal_bedrag)
            VALUES (:factuur_id, :naam_klant, :datum, :product, :hoeveelheid, :eenheidsprijs, :btw_percentage, :subtotaal, :btw_bedrag, :totaal_bedrag)";
    $values = [
        ':factuur_id' => $factuur_id,
        ':naam_klant' => $naam_klant,
        ':datum' => $datum,
        ':product' => $product,
        ':hoeveelheid' => $hoeveelheid,
        ':eenheidsprijs' => $eenheidsprijs,
        ':btw_percentage' => $btw_percentage,
        ':subtotaal' => $subtotaal,
        ':btw_bedrag' => $btw_bedrag,
        ':totaal_bedrag' => $totaal_bedrag
    ];

    try {
        // Query voorbereiden en uitvoeren
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        // Fout bij het uitvoeren van de query
        echo "Query error:" . $e;
        die();
    }

    // Terug naar overzichtspagina
    header("Location: facturenOverzicht.php");
}

require("header.php");
?>

<!-- Factuurformulier -->
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                <div class="mb-3">
                    <label for="naam_klant" class="form-label">Naam Klant</label>
                    <input type="text" class="form-control" id="naam_klant" name="naam_klant" required>
                </div>
                <div class="mb-3">
                    <label for="product" class="form-label">Product</label>
                    <input type="text" class="form-control" id="product" name="product" required>
                </div>
                <div class="mb-3">
                    <label for="hoeveelheid" class="form-label">Hoeveelheid</label>
                    <input type="number" class="form-control" id="hoeveelheid" name="hoeveelheid" required>
                </div>
                <div class="mb-3">
                    <label for="eenheidsprijs" class="form-label">Eenheidsprijs</label>
                    <input type="number" step="0.01" class="form-control" id="eenheidsprijs" name="eenheidsprijs" required>
                </div>
                <button type="submit" class="btn btn-success">Factuur aanmaken</button>
            </form>
        </div>
        <div class="col-sm-6">
        </div>
    </div>
</div>
<?php
require("footer.php");
?>
