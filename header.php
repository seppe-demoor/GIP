<?php
require('start.php');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log In</title>
<style>
    

        .navbar {
            overflow: hidden;
            background-color: #333;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        .navbar a:hover {
            background-color: #555;
        }

        .navbar a.active {
            background-color: #cd0000;
            color: white;
        }
    </style>
    </head>
<body>

    <div class="navbar">
        <a class="active" href="homePage.php">HomePage</a>
        <a href="beveiligd.php">Secured Page</a>
        <?php if (!isset($_SESSION["Gebruikersnaam"])) : ?>
            <a href="loginPage.php">Login</a>
        <?php else : ?>
            <a href="logout.php">Logout</a>
        <?php endif; ?>
    </div>
        </div>

</body>
</html>