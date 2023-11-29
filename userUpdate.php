<!DOCTYPE html>
<?php
require("start.php");

require("pdo.php");
$GUID = $_GET["GUID"];
/*var_dump($GUID);
die();*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $GUID = $_POST["GUID"];
    $username = trim($_POST["username"]);
    $naam = trim($_POST["naam"]);
    $voornaam =trim($_POST["voornaam"]);
    $email = trim($_POST["email"]);
    $admin = isset($_POST["admin"]) ? 1 : 0;

    $query = "UPDATE `users` 
              SET username = :username, naam = :naam, voornaam = :voornaam, email = :email, `admin` = :adm 
              WHERE `GUID` = :GUID";
    $values = [
        ":GUID" => $GUID];
    var_dump($values);
    // execute query

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
        header("Location: userOverzicht.php");
        exit;
    } catch (PDOException $e) {
        // error in de query
        echo 'Query error<br>' . $e; 
        die();
    }
}

/* if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["GUID"])) {
    $GUID = $_GET["GUID"];
} else {
    header("Location: userOverzicht.php");
    exit;
} */
$query = "SELECT * FROM users WHERE GUID = :GUID";
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute(['GUID' => $GUID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Query error: ' . $e->getMessage();
    die();
}


require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
            <input type="hidden" name="shop_id" value="<?php echo $user['GUID']; ?>">
                    <div class="mb-3">
                        <label for="Username" class="form-label">Gebruikersnaam</label>
                        <input type="text" class="form-control" id="Username" name="username" required value="<?php echo $user['username'];?>">
                    </div>
                    <div class="mb-3">
                        <label for="Naam" class="form-label">Naam</label>
                        <input type="text" class="form-control" id="Naam" name="naam" required value="<?php echo $user['naam']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="Voornaam" class="form-label">Voornaam</label>
                        <input type="text" class="form-control" id="Voornaam" name="voornaam" required value="<?php echo $user['voornaam']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="email" required value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" />
                        <label class="form-check-label" for="flexSwitchCheckDefault">Admin</label>
                    </div>
                    <br>
                    <input type="hidden" name="guid" value="<?php echo $GUID; ?>">
                    <button type="submit" class="btn btn-success">Gebruiker updaten</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>
<?php
require("footer.php");
?>