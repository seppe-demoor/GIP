<!DOCTYPE html>
<html lang="en">
<?php
session_start();
require("start.php");

if (isset($_SESSION['username'])) {
    //user is reeds aangemeld
    header("Location: beveiligd.php");
    exit;
}

$showAlert = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require("pdo.php");

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    //query klaarzetten
    $query = "SELECT `GUID`,`username`,`userPassword`,`passwordReset`,`admin`,`active` FROM `users` WHERE `username` = :username";
    //values voor de pdo
    $values = [':username' => $username];


    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
    }
    catch (PDOException $e) {
        //error query
        echo 'Query error <br>'.$e;
        die();
    }
    //haal rij op uit resultaat

    $row = $res->fetch(PDO::FETCH_ASSOC);

    if ($row['active'] == true) {
        if ($username == $row['username'] && password_verify($password, $row['userPassword'])) {
            $_SESSION["username"] = $username;
            $_SESSION['CREATED'] = time();
            $_SESSION['GUID'] = $row['GUID'];
            $_SESSION['admin'] = $row['admin'];
            

            //if ($row['passwordReset']) {
            //    header("Location: userWWreset.php");
            //    exit;
            //} else {
                
            //}
        } else {
            //userid en wachtwoord komen niet overeen   
            $showAlert = true;
        }
    } else {
        //geen actieve user
        $showAlert = true;
    }
}
require("header.php");
?>
<head>
    <title>Login Page</title>
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
            box-shadow: 0px 5px 10px rgba(0.5   , 00.5, 0.5, 0.5);
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
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

                    <div>
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" id="Username" name="username" required>
                    </div>

                        <div>
                            <label for="Password" class="form-label">password</label>
                            <input type="password" class="form-control" id="Password" name="password" required>
                            <input class="form-check-input" type="checkbox" id="laatzien" onchange="wwcheck()">
                            <label class="form-check-label" for="laatzien">toon wachtwoord</label>
                        </div>
                        <button type="submit" class="btn" name="secure">Login</button>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    function wwcheck(){
        let wwzien =document.getElementById('laatzien').checked;
        if (wwzien == true) {
            document.getElementById('Password').type = 'text';
        }
        else {
            document.getElementById('Password').type = 'password';
        }
    }
</script>
    <?php require("footer.php"); ?>
</body>
</html>
