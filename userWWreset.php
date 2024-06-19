<?php
require("start.php"); // Start de sessie en laad initiële instellingen
require("pdo.php"); // Maak verbinding met de database via PDO

$showAlert = false; // Variabele om te bepalen of een waarschuwing moet worden getoond

// Controleer of het verzoek een POST-verzoek is
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Haal de wachtwoorden en de geheime code uit het POST-verzoek
    $password1 = $_POST["password1"];
    $password2 = $_POST["password2"];
    $secret = trim($_POST["secret"]);
    $id = $_SESSION["id"]; // Haal de gebruiker-ID uit de sessie

    try {
        // Query om de geheime code van de gebruiker op te halen uit de database
        $query = "SELECT `secret` FROM `users` WHERE `id` = :ID";
        $res = $pdo->prepare($query);
        $res->execute([':ID' => $id]);
        $row = $res->fetch(PDO::FETCH_ASSOC); // Haal de resultaten op als een associatieve array

        // Controleer of de ingevoerde geheime code overeenkomt met de code in de database
        if ($row["secret"] == $secret) {
            // Controleer of de twee ingevoerde wachtwoorden overeenkomen
            if ($password1 === $password2) {
                // Hash het nieuwe wachtwoord voor veiligheid
                $password = password_hash($password1, PASSWORD_DEFAULT);
                // Query om het wachtwoord te updaten in de database
                $query = "UPDATE `users` SET `userPassword` = :pw, `passwordReset` = 0 WHERE `id` = :ID";
                $res = $pdo->prepare($query);
                $res->execute([':pw' => $password, ':ID' => $id]);
                // Stuur de gebruiker door naar een beveiligde pagina
                header("Location: beveiligd.php");
                exit; // Stop de verdere uitvoering van het script
            } else {
                // Toon een waarschuwing als de wachtwoorden niet overeenkomen
                $showAlert = true;
                $alertText = "<strong>FOUT!</strong> De 2 getypte wachtwoorden zijn niet gelijk, probeer opnieuw.";
            }
        } else {
            // Toon een waarschuwing als de geheime code onjuist is
            $showAlert = true;
            $alertText = "<strong>FOUT!</strong> Uw code is niet correct ingegeven.";
        }
    } catch (PDOException $e) {
        // Toon een foutbericht als de query mislukt
        echo "Query error: " . $e->getMessage();
        die(); // Stop de verdere uitvoering van het script
    }
}

// Controleer of het verzoek een GET-verzoek is en of de 'secret' parameter is ingesteld
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['secret'])) {
    $secret = $_GET['secret']; // Haal de geheime code uit de URL-parameters
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            margin-top: 50px;
        }
        .col-sm-6 {
            margin-bottom: 20px;
        }
        h3 {
            margin-bottom: 20px;
        }
        .alert {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        form {
            margin-top: 20px;
        }
        .form-label {
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .btn-success {
            display: inline-block;
            font-weight: 400;
            color: #fff;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            background-color: #28a745;
            border: 1px solid #28a745;
            padding: 10px 20px;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <h3>Je moet je wachtwoord opnieuw instellen</h3>
            </div>
            <div class="col-sm-6">
                <!-- Toon een waarschuwing als de variabele $showAlert waar is -->
                <?php if ($showAlert) : ?>
                    <div class="alert">
                        <?php echo $alertText; // Toon de waarschuwingstekst ?>
                    </div>
                <?php endif; ?>
                <!-- Formulier voor het opnieuw instellen van het wachtwoord -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="form-group">
                        <label for="Password1" class="form-label">Nieuw wachtwoord</label>
                        <input type="password" class="form-control" id="Password1" name="password1" required>
                    </div>
                    <div class="form-group">
                        <label for="Password2" class="form-label">Hertyp je wachtwoord</label>
                        <input type="password" class="form-control" id="Password2" name="password2" required>
                    </div>
                    <div class="form-group">
                        <label for="secret" class="form-label">Geef je code in vanuit je email</label>
                        <input type="text" class="form-control" id="secret" name="secret" value="<?php echo isset($secret) ? $secret : "" ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success">Reset</button>
                </form>
            </div>
        </div>
    </div>
<?php require("footer.php"); // Laad de footer van de pagina ?>
</body>
</html>
