<?php
// Deze functie `sendMail()` verstuurt een e-mail naar de gebruiker met informatie over het geresette wachtwoord en de geheime code.
function sendMail($to, $secret, $voornaam, $ww) { 
    $from = "yorben.vandermeiren@leerling.go-ao.be"; // Het afzender e-mailadres
    $subject = "Reset van je wachtwoord"; // Het onderwerp van de e-mail
    // Het bericht dat wordt opgesteld voor de e-mail, inclusief variabelen voor de voornaam, nieuw wachtwoord en geheime code.
    $message = "Beste $voornaam,\nWe hebben je wachtwoord gereset.\nJe nieuwe wachtwoord is $ww.\nJe moet ook nog deze code ingeven: $secret.\nKlik op onderstaande link:\nhttp://seppe.go-ao.be/GIP/resetUser.php?secret=$secret\n\nMet vriendelijke groeten,\nAdmin van de website.";

    // De `mail()` functie van PHP wordt gebruikt om de e-mail te versturen.
    if (mail($to, $subject, $message, $from)) {
        echo "Mail is verstuurd"; // Bericht als de e-mail succesvol is verstuurd
    } else {
        echo "Mail is niet verzonden"; // Bericht als er een probleem was met het versturen van de e-mail
    }
}

require("start.php"); // Het starten van de sessie en andere benodigde initialisaties

// Controleert of de huidige gebruiker een admin is; zo niet, dan wordt de gebruiker omgeleid naar de loginpagina.
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] == 0) {
    header("Location: loginPage.php");
    exit;
}

require("pdo.php"); // Verbinding maken met de database met behulp van PDO

// Verwerking van POST-verzoeken, wat betekent dat er een formulier is ingediend om het wachtwoord te resetten.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $password = trim($_POST["password"]); // Het nieuwe wachtwoord dat is ingediend via het formulier
        $hash = password_hash($password, PASSWORD_DEFAULT); // Het wachtwoord wordt gehasht voor opslag in de database
        $id = $_POST["id"]; // Het gebruikers-ID dat is ontvangen via het verborgen veld in het formulier
        $email = $_POST["email"]; // Het e-mailadres van de gebruiker
        $voornaam = $_POST["voornaam"]; // De voornaam van de gebruiker
        $secret = rand(10000000, 99999999); // Genereert een willekeurige geheime code

        // Voorbereiden van de SQL-query om het wachtwoord, de `passwordReset`-status en de geheime code bij te werken voor de gebruiker met het opgegeven ID.
        $query = "UPDATE `users` SET `userPassword` = :password, `passwordReset` = 1, `secret` = :secr WHERE `id` = :ID";
        $values = [":ID" => $id, ":password" => $hash, ":secr" => $secret];

        // Voorbereiden en uitvoeren van de SQL-query met PDO om veilig gegevens in te voegen in de database.
        $res = $pdo->prepare($query);
        $res->execute($values);

        // Versturen van een e-mail naar de gebruiker met de nieuwe informatie, inclusief het nieuwe wachtwoord en de geheime code.
        sendMail($email, $secret, $voornaam, $password);

        // Na succesvolle verwerking wordt de gebruiker doorverwezen naar de overzichtspagina van gebruikers.
        header("Location: userOverzicht.php");
        exit;
    } catch (PDOException $e) {
        echo 'Query error: ' . $e->getMessage(); // Afhandeling van fouten bij het uitvoeren van de SQL-query
        die();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage(); // Algemene foutafhandeling
        die();
    }
}

// Als het verzoek een GET-verzoek is en er een geldig `id` wordt ontvangen via de querystring.
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    try {
        $id = $_GET["id"]; // Het ontvangen gebruikers-ID uit de querystring
        // Voorbereiden van de SQL-query om de naam, voornaam en e-mail van de gebruiker op te halen op basis van het ID.
        $query = "SELECT `naam`, `voornaam`, `email` FROM `users` WHERE `id` = :ID";
        $values = [':ID' => $id];

        // Voorbereiden en uitvoeren van de SQL-query om informatie op te halen met PDO.
        $res = $pdo->prepare($query);
        $res->execute($values);

        // Het ophalen van de resultaten van de query en opslaan in een associatieve array `$row`.
        $row = $res->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query error: ' . $e->getMessage(); // Afhandeling van fouten bij het uitvoeren van de SQL-query
        die();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage(); // Algemene foutafhandeling
        die();
    }
} else {
    header("Location: userOverzicht.php"); // Als er geen geldig `id` is ontvangen, wordt de gebruiker omgeleid naar de overzichtspagina van gebruikers.
    exit;
}

require("header.php"); // Inclusie van het header-bestand voor de HTML-pagina
?>
<!-- Het HTML-gedeelte van de pagina voor het weergeven van het formulier om het wachtwoord te resetten -->
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-6">
            <div class="mb-3">
                <!-- Het tonen van de naam en voornaam van de gebruiker voor wie het wachtwoord wordt gereset -->
                <h3>Wachtwoord resetten voor <?php echo $row["naam"] . " " . $row["voornaam"]; ?></h3>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="Password" class="form-label">Nieuw tijdelijk wachtwoord</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <br>
                <button type="submit" class="btn btn-danger btn-lg">Reset</button>
                <!-- Verborgen velden om het gebruikers-ID, e-mailadres en voornaam door te geven bij het verzenden van het formulier -->
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="email" value="<?php echo $row["email"]; ?>">
                <input type="hidden" name="voornaam" value="<?php echo $row["voornaam"]; ?>">
            </form>
        </div>
        <div class="col-sm-6"></div>
    </div>
</div>
<?php require("footer.php"); // Inclusie van het footer-bestand voor de HTML-pagina ?>
