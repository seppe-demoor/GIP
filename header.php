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
    </style>
</head>
<body>

    <div class="navbar">
        <a class="active" href="homePage.php">HomePage</a>
        
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
            <a class="nav-link" href="facturen.php">facturen</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Beveiligde admin pagina's</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="beveiligd2.php">admin pagina</a></li>
                                    <li><a class="dropdown-item" href="session.php" target="_blank">bekijk session</a></li>
                                    <li><a class="dropdown-item" href="userNew.php">niewe user</a></li>
                                    <li><a class="dropdown-item" href="userResetWWAdmin.php">reset password</a></li> 
                                    <li><a class="dropdown-item" href="userUpdate.php">update user</a></li>
                                    <li><a class="dropdown-item" href="userOverzicht.php">overzicht user</a></li>                              
                                </ul>
                            </li>
                        <?php endif; ?>        

        <?php if (!isset($_SESSION["admin"])) : ?>
            <a href="loginPage.php">Login</a>
        <?php else : ?>
            <a href="logoutPage.php">Logout</a>
        <?php endif; ?>
    </div>

</body>
</html>
