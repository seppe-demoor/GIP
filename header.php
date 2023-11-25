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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E9E2D6;
            margin: 0;
            padding: 0;
        }

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

        /* Additional style for dropdown */
        .dropdown-menu {
            background-color: #333;
        }

        .dropdown-item {
            color: white;
        }

        .dropdown-item:hover {
            background-color: #555;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a class="active" href="homePage.php">HomePage</a>
        
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
            

            <div class="dropdown">
                <!--<a href="#" class="dropdown-toggle">Beveiligde admin pagina's</a>-->
                <div class="dropdown-menu">
                    <!--<a href="beveiligd2.php" class="dropdown-item">admin pagina</a>
                    <a href="facturen.php" class="dropdown-item">facturen</a>
                    <a href="session.php" class="dropdown-item" target="_blank">bekijk session</a>
                    <a href="userNew.php" class="dropdown-item">niewe user</a>
                    <a href="userResetWWAdmin.php" class="dropdown-item">reset password</a>
                    <a href="userUpdate.php" class="dropdown-item">update user</a>
                    <a href="userOverzicht.php" class="dropdown-item">overzicht user</a>-->
                    <a href="beveiligd.php" class="dropdown-item">opties</a>

                </div>
            </div>
        <?php endif; ?>        

        <?php if (!isset($_SESSION["admin"])) : ?>
            <a href="loginPage.php">Login</a>
        <?php else : ?>
            <a href="logoutPage.php">Logout</a>
        <?php endif; ?>
    </div>

</body>
</html>
