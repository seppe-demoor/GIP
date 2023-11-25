<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E9E2D6;
        }

        .container {
            margin-top: 50px;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }

        .form-check {
            margin-bottom: 15px;
        }

        button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>

<?php
require("start.php");
if (!isset($_SESSION['username'])) {
    //user is reeds aangemeld
    header("Location: login.php");
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

    //creat GUID
    $GUID = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

    $query = "INSERT INTO `users` (GUID, userName, naam, voornaam, email, userPassword, admin)
              VALUES (:ID, :userName, :naam, :voornaam, :email, :userPassword, :adm)";
    $values = [':ID' => $GUID, ':userName' => $username, ':naam'=> $naam,
                 ':voornaam'=> $voornaam, ':email'=>$email, ':userPassword'=> $password, ':adm'=>$admin];
    try {
    $res = $pdo->prepare($query);
    $res->execute($values);
    } catch (PDOException $e) {
        //error in query
        echo "Query error:" . $e;
        die();
    }
}
require("header.php");
?>

    <div class="container">
        <div class="row">
            <div class="col-sm-6">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <label for="Username">Gebruikersnaam</label>
                    <input type="text" id="Username" name="username" required>

                    <label for="Naam">Naam</label>
                    <input type="text" id="Naam" name="naam" required>

                    <label for="Voornaam">Voornaam</label>
                    <input type="text" id="Voornaam" name="voornaam" required>

                    <label for="Email">Email</label>
                    <input type="email" id="Email" name="email" required>

                    <label for="Password">Wachtwoord</label>
                    <input type="password" id="Password" name="password" required>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" name="admin">
                        <label class="form-check-label" for="flexSwitchCheckDefault">Admin</label>
                    </div>

                    <button type="submit">Gebruiker aanmaken</button>
                </form>
            </div>
            <div class="col-sm-6">
                <!-- Your content for the right column -->
            </div>
        </div>
    </div>

    <?php
require("footer.php");
    ?>

</body>

</html>
