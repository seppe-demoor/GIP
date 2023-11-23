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

<?php
require("footer.php");
?>
