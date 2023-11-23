<!DOCTYPE html>
<?php


if (isset($_SESSION['user'])) {
    $isLoggedIn = true;
} else {
    $isLoggedIn = false;
}
?>
