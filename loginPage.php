<?php
require("start.php");

// Als de gebruiker al is ingelogd (sessie bestaat al), doorsturen naar homePage.php
if (isset($_SESSION['email'])) {
    header("Location: homePage.php");
    exit;
}

$showAlert = false; // Variabele om te controleren of een waarschuwing moet worden weergegeven

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        require("pdo.php"); // Databaseverbinding met PDO

        $email = trim($_POST["email"]); // Ophalen en opschonen van ingediende e-mail
        $password = trim($_POST["password"]); // Ophalen en opschonen van ingediend wachtwoord
        
        $query = "SELECT `id`, `email`, `userPassword`, `passwordReset`, `admin`, `active` FROM `users` WHERE `email` = :email";
        $values = [':email' => $email];

        $res = $pdo->prepare($query);
        $res->execute($values);

        $row = $res->fetch(PDO::FETCH_ASSOC);

        // Controleer of er een rij is geretourneerd en of de gebruiker actief is
        if ($row && $row['active']) {
            // Verifieer het ingediende wachtwoord met het gehashte wachtwoord in de database
            if (password_verify($password, $row['userPassword'])) {
                // Sessievariabelen instellen na succesvolle verificatie
                $_SESSION["email"] = $email;
                $_SESSION['CREATED'] = time();
                $_SESSION['id'] = $row['id'];
                $_SESSION['admin'] = $row['admin'];

                // Doorsturen naar de juiste pagina op basis van passwordReset-status
                header("Location: " . ($row['passwordReset'] ? "userWWreset.php" : "homePage.php"));
                exit;
            } else {
                $showAlert = true; // Als wachtwoord niet overeenkomt, toon een waarschuwing
            }
        } else {
            $showAlert = true; // Als gebruiker niet actief is of e-mail niet gevonden, toon een waarschuwing
        }
    } catch (PDOException $e) {
        echo 'Query error: ' . $e->getMessage(); // Vang databasefouten af
        die();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage(); // Vang algemene fouten af
        die();
    }
}

require("header.php"); // Inclusie van header-bestand voor HTML-pagina
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pagina</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E9E2D6;
            margin: 0;
            padding: 0;
        }

        .login-container {
            border: 1px solid #ddd; 
            border-radius: 10px;
            padding: 30px;
            background-color: lightgrey; 
            margin-top: 50px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0px 5px 10px rgba(0.5, 0.5, 0.5, 0.5);
            margin: 125px auto;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 70%;
            height: auto;
            padding-bottom: 20px;
        }

        .form-label {
            font-weight: bold;
            float: left;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
            resize: none;
        }

        .btn {
            background-color: #ef4444; 
            border-color: #ef4444; 
            color: white;
            padding: 13px; 
            font-size: 14px; 
            border-radius: 5px;
            width: 125px;
        }

        .btn:hover {
            background-color: #cd0000;
            border-color: #cd0000;
        }

        .forgot-password {
            text-align: right;
            font-size: 14px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="NWNSoftware.png" alt="logo">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div>
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div>
                    <label for="Password" class="form-label">Password:</label>
                    <input type="password" class="form-control" id="Password" name="password" required>
                    <div class="float-start">
                        <input class="form-check-input" type="checkbox" id="laatzien" onchange="wwcheck()">
                        <label class="form-check-label" for="laatzien">Toon wachtwoord</label>
                    </div>
                </div>
                <br><br>
                <button type="submit" class="btn" name="secure">Login</button>
                <?php
                if ($showAlert) {
                    echo '<div class="alert alert-danger mt-3" role="alert">Inloggegevens incorrect. Probeer opnieuw.</div>';
                }
                ?>
            </form>
        </div>
    </div>
    <script>
    function wwcheck() {
        let wwzien = document.getElementById('laatzien').checked;
        document.getElementById('Password').type = wwzien ? 'text' : 'password';
    }
    </script>
<?php
require("footer.php"); // Inclusie van footer-bestand voor HTML-pagina
?>
