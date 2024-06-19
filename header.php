<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="MDS"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color:   white;
            margin: 0;
            padding: 0;
        }

        .navbar {
            height: 10%;
            background-color: #333;
        }

        .header-btn{
            text-decoration: none;
            padding: 0.5rem 0.8rem;
            background-color: #333;
            color: white;
            border-radius: 0.1rem;
            border: none;
            margin: 0rem 0.5rem 0rem 0.5rem;
            height: 40px;
        }
        .home-btn{
            text-decoration: none;
            padding: 0.5rem 0.8rem;
            background-color: white;
            border-radius: 0.1rem;
            border: none;
            margin: 0rem 0.5rem 0rem 0.5rem;
            height: 40px;
        }

        .navbar a:hover {
            background-color: #f72314;
            padding: 0.8rem;
            color: white;
        }

        .navbar a.active {
            background-color: white;
            color: black;
        }

        /* The container <div> - needed to position the dropdown content */
        .dropdown {
        position: relative;
        display: inline-block;
        }
        /* Dropdown Content (Hidden by Default) */
        .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f1f1f1;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        transition: all in 0.5s;
        }

        /* Links inside the dropdown */
        .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        }

        /* Change color of dropdown links on hover */
        .dropdown-content a:hover {
            background-color: #ddd;
        }

        /* Show the dropdown menu on hover */
        .dropdown:hover .dropdown-content {display: block;}

        /* Change the background color of the dropdown button when the dropdown content is shown */
        .dropdown:hover .dropbtn {background-color: #3e8e41;}

    </style>
        <link rel="icon" href= "NWNSoftware.png"  type="image/x-icon">
</head>
<body>

<div class="navbar">
    <a href="homePage.php">
        <img src="NWNSoftware.png" class="home-btn"/>
    </a>


    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == true): ?>
        <div class="dropdown">
  <button class="header-btn">Overzicht</button>
  <div class="dropdown-content">
            <a class="dropdown-btn" href="userOverzicht.php">Gebruikers</a>
            <a class="dropdown-btn" href="facturenOverzicht.php">Facturen</a>
            <a class="dropdown-btn" href="customerOverzicht.php">klanten</a>
            <a class="dropdown-btn" href="countryOverzicht.php">Landen</a>
  </div>
</div>
    <?php endif; ?>
    <?php ?>

    <?php if (!isset($_SESSION["admin"])) : ?>
        <a class="header-btn" href="loginPage.php">Login</a>
    <?php else : ?>
        <a href="logoutPage.php" class="header-btn">Logout</a>
    <?php endif; ?>
</div>