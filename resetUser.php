<!DOCTYPE html>
<?php
// reset pw door admin

function sendMail($to, $secret, $voornaam, $ww) { 
    $from = "yorben.vandermeiren@leerling.go-ao.be";
    $subject = "Rest van je wachtwoord";
    $message = "Best $voornaam, 
We hebben je wachtwoord gereset.
Je nieuwe wachtwoord is $ww.
Je moet ook nog deze code ingeven: $secret.
Klik op onderstaande link:
http://seppe.go-ao.be/GIP/resetUser.php?secret=$secret

Met vriendelijke groeten,
Admin van de website.";

    if(mail($to, $subject, $message, $from)) {
        echo "Mail is verstuurd";
    } else {
        echo "Mail is niet verzonden";
    }
}
    require("start.php");

    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        header("Location: loginPage.php");
    }

    require("pdo.php");

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = trim($_POST["password"]);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $GUID = $_POST["GUID"];
        $email = $_POST["email"];
        $secret = rand(10000000,99999999);

        $query = "UPDATE `users` SET `userPassword` = :password, `passwordReset` = 1, `secret` = :secr WHERE `GUID` = :ID";
        $values = [":ID" => $GUID, ":password" => $hash, ":secr" => $secret];
        var_dump($values);
        try
        {
            $res = $pdo->prepare($query);
            $res->execute($values);
            sendMail($email, $secret, $voornaam, $password);
            header("Location: userOverzicht.php");
            exit;
        }
        catch (PDOException $e)
        {
            echo 'Query error.' . $e;
            die();
        }
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["GUID"])) {
        $GUID = $_GET["GUID"];
        $query = "SELECT `naam`,`voornaam`,`email` FROM `users` WHERE `GUID` = :ID";
        $values = [':ID' => $GUID];
    
        try
        {
            $res = $pdo->prepare($query);
            $res->execute($values);
        }
        catch (PDOException $e)
        {
            echo 'Query error.' . $e;
            die();
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: userOverzicht.php");
        exit;
    }

    require("header.php");
?>
    <div class="container mt-5">
    <div class="row">
        <!-- Kolom 1 -->
        <div class="col-sm-6">
            <div class="mb-3">
                <h3>Wachtwoord resetten voor <?php echo $row["naam"] . " " . $row["voornaam"]; ?></h3>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

            <div class="mb-3">
                <label for="Password" class="form-label">Nieuw tijdelijk wachtwoord</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <br>
            <button type="submit" class="btn btn-danger btn-lg">Reset</button>
            <input type="hidden" name="GUID" value="<?php echo $GUID; ?>">
            <input type="hidden" name="email" value="<?php echo $row["email"]; ?>">
            <input type="hidden" name="voornaam" value="<?php echo $row["voornaam"]; ?>">
            </form>
        </div>
        <!-- Kolom 2 -->
        <div class="col-sm-6">

        </div>
    </div>
    </div>