<!DOCTYPE html>
<?php
require("start.php");
require("pdo.php");



if (!isset($_SESSION["username"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");
?>
    <div>
        <button><a href="userOverzicht.php">overzicht user</a></button>
    </div>
    <div>
        <button><a href="userOverzicht2.0.php">overzicht2.0 user</a></button>
    </div>
    <div>
        <button><a href="userUpdate.php">update user</a></button>
    </div>
    <div>
        <button><a href="userNew.php">maak een nieuwe user aan user</a></button>
    </div>
        
        
<?php
require("footer.php");
?>
