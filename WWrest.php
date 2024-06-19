<?php
require("start.php"); // Start de sessie en laadt de initiële instellingen

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['email'])) {
    // Als de gebruiker niet is ingelogd, stuur door naar de login-pagina
    header("Location: loginPage.php");
    exit; // Stop de verdere uitvoering van het script
}

require("pdo.php"); // Maak verbinding met de database via PDO

// Functie om een e-mail te sturen naar de gebruiker
function sendMail($to, $secret, $voornaam, $ww) {
    $from = "seppe.demoor@leerling.go-ao.be"; // Afzender e-mailadres
    $subject = "Onderwerp van de mail"; // Onderwerp van de e-mail
    // Berichtinhoud van de e-mail
    $message = "Beste $voornaam,\nwe hebben je wachwoord gereset.\nJe nieuwe wachwoord is $ww.\nJe moet ook nog deze code ingeven: $secret.\nKlik op onderstaande link:\nhttps://seppe.go-ao.be/login%20CC/userWWreset.php?secret=$secret\nMet vriendelijke groeten,\nAdmin van de website.";
    // Stuur de e-mail en geef een bericht weer op basis van het resultaat
    echo mail($to, $subject, $message, $from) ? "Bericht is verzonden" : "Bericht is niet verzonden";
}

// Verwerk het POST-verzoek bij het indienen van het formulier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Hash het nieuwe wachtwoord voor veiligheid
    $hash = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    // Genereer een willekeurige geheime code
    $secret = rand(10000000, 99999999);
    // Bereid de waarden voor de SQL-query voor
    $values = [':pw' => $hash, ':ID' => $_POST['id'], ':secr' => $secret];

    try {
        // Update de gebruikersgegevens in de database
        $pdo->prepare("UPDATE `users` SET `userPassword` = :pw, `passwordReset` = 1, `secret` = :secr WHERE `id` = :ID")->execute($values);
        // Stuur een e-mail naar de gebruiker met het nieuwe wachtwoord en de geheime code
        sendMail($_POST["email"], $secret, $_POST['voornaam'], $_POST["password"]);
        // Stuur de gebruiker door naar een andere pagina
        header("Location: useroverzicht2.0.php");
        exit; // Stop de verdere uitvoering van het script
    } catch (PDOException $e) {
        // Toon een foutbericht als de query mislukt
        echo 'Query error<br>' . $e;
        die(); // Stop de verdere uitvoering van het script
    }
}

// Verwerk het GET-verzoek bij het openen van de pagina
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    try {
        // Haal de naam, voornaam en e-mail van de gebruiker op uit de database
        $res = $pdo->prepare("SELECT `naam`, `voornaam`, `email` FROM `users` WHERE `id` = :ID");
        $res->execute([':ID' => $_GET["id"]]);
        $row = $res->fetch(PDO::FETCH_ASSOC); // Sla de gegevens op in een associatieve array
    } catch (PDOException $e) {
        // Toon een foutbericht als de query mislukt
        echo 'Query error<br>' . $e;
        die(); // Stop de verdere uitvoering van het script
    }
}

require("header.php"); // Laad de header van de pagina
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord Reset</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; }
        .container { margin-top: 50px; }
        .col-sm-6 { margin-bottom: 20px; }
        h3 { margin-bottom: 20px; }
        .form-label { margin-bottom: 5px; }
        .form-control { width: 100%; padding: 10px; margin-bottom: 20px; box-sizing: border-box; }
        .btn-success { display: inline-block; font-weight: 400; color: #fff; text-align: center; vertical-align: middle; user-select: none; background-color: #28a745; border: 1px solid #28a745; padding: 10px 20px; font-size: 1rem; line-height: 1.5; border-radius: 0.25rem; transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; cursor: pointer; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
                <!-- Formulier voor wachtwoord reset -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-3">
                        <!-- Titel met de naam van de gebruiker -->
                        <h3>Wachtwoord van <?php echo $row['naam']; ?></h3>
                    </div>
                    <div class="mb-3">
                        <!-- Invoerveld voor het nieuwe wachtwoord -->
                        <label for="Password" class="form-label">Nieuw tijdelijk ww</label>
                        <input type="password" class="form-control" id="Password" name="password" required>
                    </div>
                    <!-- Verborgen velden om extra gegevens door te geven -->
                    <input type="hidden" value="<?php echo $_GET["id"]; ?>" name="id">
                    <input type="hidden" name="email" value="<?php echo $row["email"]; ?>">
                    <input type="hidden" name="voornaam" value="<?php echo $row["voornaam"]; ?>">
                    <!-- Knop om het formulier in te dienen -->
                    <button type="submit" class="btn btn-success">reset</button>
                </form>
            </div>
        </div>
    </div>
<?php require("footer.php"); // Laad de footer van de pagina ?>
</body>
</html>
