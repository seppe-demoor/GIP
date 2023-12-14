<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
</head>

<body>

<?php
require("start.php");
if (!isset($_SESSION['username'])) {
    //user is reeds aangemeld
    header("Location: loginPage.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require("pdo.php");
    $username = trim($_POST["username"]);
    $naam = trim($_POST["naam"]);
    $voornaam = trim($_POST["voornaam"]);
    $email = trim($_POST["email"]);
    $admin = isset($_POST["admin"]) ? 1 : 0;
    $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    $secret = isset($_POST["secret"]) ? trim($_POST["secret"]) : "0";  // Set secret to 0 if not provided

    //create GUID
    $GUID = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

    $query = "INSERT INTO `users` (GUID, username, naam, voornaam, email, userPassword, admin, secret)
            VALUES (:ID, :username, :naam, :voornaam, :email, :userPassword, :adm, :secret)";
    $values = [':ID' => $GUID, ':username' => $username, ':naam'=> $naam,
            ':voornaam'=> $voornaam, ':email'=>$email, ':userPassword'=> $password, ':adm'=>$admin, ':secret' => $secret];

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        //error in query
        echo "Query error:" . $e;
        die();
    }
    header("Location: userOverzicht.php");
}

require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                    <div class="mb-3">
                        <label for="Username" class="form-label">Gebruikersnaam</label>
                        <input type="text" class="form-control" id="Username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="Naam" class="form-label">Naam</label>
                        <input type="text" class="form-control" id="Naam" name="naam" required>
                    </div>
                    <div class="mb-3">
                        <label for="Voornaam" class="form-label">Voornaam</label>
                        <input type="text" class="form-control" id="Voornaam" name="voornaam" required>
                    </div>
                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="Password" class="form-label">Wachtwoord</label>
                        <input type="password" class="form-control" id="Password" name="password" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="admin" name="admin" />
                        <label class="form-check-label" for="admin">Admin</label>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-success">Gebruiker aanmaken</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>