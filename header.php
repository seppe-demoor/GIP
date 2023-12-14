<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="MDS">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <title>Dashboard</title>
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
        <link rel="icon" href= "NWNSoftware.png"  type="image/x-icon">
</head>
<body>

<div class="navbar">
    <a class="active" href="homePage.php">HomePage</a>

    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
            <a href="userOverzicht.php">overzicht gebruikers</a>
            <a href="countryOverzicht.php">overzicht landen</a>
        <!--<div class="dropdown">
            <button class="dropbtn">Users</button>
            <div class="dropdown-content">
                <a href="beveiligd2.php">admin pagina</a>
                <a href="facturen.php">facturen</a>
                <a href="session.php" target="_blank">bekijk session</a>    
                <a href="userNew.php">niewe user</a>
                <a href="userWWreset.php">reset password</a>
                <a href="userOverzicht2.0.php">overzicht user</a>
            </div>
        </div>-->
    <?php endif; ?>
    <?php ?>

    <?php if (!isset($_SESSION["admin"])) : ?>
        <a href="loginPage.php">Login</a>
    <?php else : ?>
        <a href="logoutPage.php" class="logout">Logout</a>
    <?php endif; ?>
</div>

</body>
</html>
