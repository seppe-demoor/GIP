<!DOCTYPE html>
<?php
// Reset voor gebruiker
require("startphp.php");

if (!isset($_SESSION['username'])) {
    // User is reeds aangemeld
    header("Location: login.php");
    exit;
}
require("pdo.php");

function sendMail($to, $secret, $voornaam, $ww) {
    $from = "claudiustefan.calin@leerling.go-ao.be";
    $subject = "Onderwerp van de mail";
    $message = "Beste $voornaam,
we hebben je wachwoord gereset.
Je nieuwe wachwoord is $ww.
Je moet ook nog deze code ingeven: $secret.
Klik op onderstaande link:
https://claudiu.go-ao.be/login%20CC/userWWreset.php?secret=$secret
Met vriendelijke groeten,
Admin van de website.";

    if (mail($to, $subject, $message, $from)) {
        echo "Bericht is verzonden";
    } else {
        echo "Bericht is niet verzonden";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $wachtwoord = trim($_POST["password"]);
    $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
    $GUID = $_POST['guid'];
    $email = $_POST["email"];
    $voornaam = $_POST['voornaam'];
    $secret = rand(10000000, 99999999);

    $query = "UPDATE `users` SET `userPassword` = :pw, `passwordReset` = 1, `secret` = :secr WHERE `GUID` = :ID";
    $values = [':pw' => $hash, ':ID' => $GUID, ':secr' => $secret];

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
        sendMail($email, $secret, $voornaam, $wachtwoord);
        header("Location: useroverzicht.php");
        exit;
    } catch (PDOException $e) {
        // Error in de query
        echo 'Query error<br>' . $e;
        die();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["GUID"])) {
    $GUID = $_GET["GUID"];
    $query = "SELECT `naam`, `voornaam`, `email` FROM `users` WHERE `GUID` = :ID";
    $values = [':ID' => $GUID];

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        // Error in de query
        echo 'Query error<br>' . $e;
        die();
    }
    $row = $res->fetch(PDO::FETCH_ASSOC);
}

require("header.php");
?>
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
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="mb-3">
                        <h3>Wachtwoord van <?php echo $row['naam']; ?></h3>
                    </div>
                    <div class="mb-3">
                        <label for="Password" class="form-label">Nieuw tijdelijk ww</label>
                        <input type="password" class="form-control" id="Password" name="password" required>
                    </div>
                    <input type="hidden" value="<?php echo $GUID; ?>" name="guid">
                    <input type="hidden" name="email" value="<?php echo $row["email"]; ?>">
                    <input type="hidden" name="voornaam" value="<?php echo $row["voornaam"]; ?>">
                    <button type="submit" class="btn btn-success">reset</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>
</body>
</html>

<?php
require("footer.php");
?>
