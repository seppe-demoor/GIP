<?php
require("start.php");
require("pdo.php");



if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");  //kijkt of de user is ingelogd
    exit;
}

require("header.php");
require("footer.php");
?>
