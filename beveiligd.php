<?php
require("start.php");
require("pdo.php");



if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");
require("footer.php");
?>
