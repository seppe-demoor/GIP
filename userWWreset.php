<?php
require("start.php");
require("pdo.php");

$showAlert = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password1 = $_POST["password1"];
    $password2 = $_POST["password2"];
    $secret = trim($_POST["secret"]);
    $id = $_SESSION["id"];

    $query = "SELECT `secret` FROM `users` WHERE `id` = :ID";
    $values = [':ID' => $id];

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
    } catch (PDOException $e) {
        // error in query
        echo "Query error:" . $e;
        die();
    }
    $row = $res->fetch(PDO::FETCH_ASSOC);

    if ($row["secret"] == $secret) {
        // secret is OK
        if ($password1 === $password2) {
            $password = password_hash($password1, PASSWORD_DEFAULT);
            $query = "UPDATE `users` SET `userPassword` = :pw, `passwordReset` = 0 WHERE `id` = :ID";
            $values = [':pw' => $password, ':ID' => $id,];

            try {
                $res = $pdo->prepare($query);
                $res->execute($values);
                header("Location: beveiligd.php");
                exit;
            } catch (PDOException $e) {
                // error in query
                echo "Query error:" . $e;
                die();
            }
        } else {
            $showAlert = true;
            $alertText = "<strong>FOUT!</strong> De 2 getypde wachtwoorden zijn niet gelijk, probeer opnieuw.";
        }
    } else {
        // secret is fout
        $showAlert = true;
        $alertText = "<strong>FOUT!</strong> Uw code is niet correct ingegeven.";
    }
}
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['secret'])) {
    $secret = $_GET['secret'];
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
                <?php if ($showAlert) : ?>
                    <div class="alert">
                        <?php echo $alertText; ?>
                    </div>
                <?php endif; ?>
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
<?php
    require("footer.php");
?>