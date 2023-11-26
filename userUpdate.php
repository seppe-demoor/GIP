<!DOCTYPE html>
<?php
require("start.php");

require("pdo.php");
$GUID = $_GET['GUID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $GUID = $_POST["GUID"];
    $username = trim($_POST["username"]);
    $naam = trim($_POST["naam"]);
    $voornaam = trim($_POST["voornaam"]);
    $email = trim($_POST["email"]);
    $admin = isset($_POST["admin"]) ? 1 : 0;

    $query = "UPDATE `users` 
              SET userName = :userName, naam = :naam, voornaam = :voornaam, email = :email, `admin` = :adm 
              WHERE `GUID` = :GUID";
    $values = [
        ":GUID" => $GUID
    ];

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Update</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E9E2D6;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 5rem;
            margin-left: 50px;
            margin-right: 50px;
        }

        .form-check {
            margin-top: 1rem;
        }

        .row {
            margin: 0;
        }

        .col-sm-6 {
            width: 50%;
            float: left;
            box-sizing: border-box;
        }

        .col-sm-6 {
            width: 50%;
            float: left;
            box-sizing: border-box;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
            line-height: 1.25;
            border-radius: 0.2rem;
            border: 1px solid #ced4da;
            box-sizing: border-box;
        }

        .form-check-input {
            margin-top: 0.25rem;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            background-color: #28a745;
            border: 1px solid #28a745;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
        }

        .btn-success {
            color: #fff;
            background-color: #ef4444;
            border-color: #ef4444;
        }

        .btn-success:hover {
            color: #fff;
            background-color: #cd0000;
            border-color: #cd0000;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="shop_id" value="<?php echo $user['GUID']; ?>">
                <div class="mb-3">
                    <label for="Username" class="form-label">Gebruikersnaam</label>
                    <input type="text" class="form-control" id="Username" name="username" required value="<?php echo $user['username']; ?>">
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
            <!-- Additional content for the right column, if needed -->
        </div>
    </div>
</div>

</body>
</html>
<?php
require("footer.php");
?>
