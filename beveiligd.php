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
      <a href="kalender.php">Kalender</a>
      <a href="userTap.php">overzicht user</a>
  </div>
<?php
require("footer.php");
?>
