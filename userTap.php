<!DOCTYPE html>
<?php
session_start();
require("start.php");
require("pdo.php");



if (!isset($_SESSION["username"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");
?>
    <ul>
        <li><button><a href="userOverzicht.php">overzicht user</a></button></li>
        <li><button><a href="userUpdate.php">update user</a></button></li>
        <li><button><a href="userNew.php">maak een nieuwe user aan user</a></button></li>
    </ul>
<?php
require("footer.php");
?>
