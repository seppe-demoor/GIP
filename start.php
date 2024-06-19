<!DOCTYPE html>
<?php
session_start(); // Start or resume the session

if (isset($_SESSION['user'])) {
    $isLoggedIn = true; // Set $isLoggedIn to true if 'user' session variable is set
} else {
    $isLoggedIn = false; // Set $isLoggedIn to false if 'user' session variable is not set
}
?>
