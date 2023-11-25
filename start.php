<!DOCTYPE html>
<?php
session_start();

if (isset($_SESSION['user'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}
?>
