<!DOCTYPE html>
<?php
require("start.php");
require("pdo.php");



if (!isset($_SESSION["username"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");
require("footer.php");
?>
