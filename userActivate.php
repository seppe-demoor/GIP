<?php
require("start.php");

if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    require("pdo.php");

    $query = "UPDATE `users` SET `active`=1 WHERE `id` = :ID";

    $values = [":ID" => $id];

    try
    {
        $res = $pdo->prepare($query);
        $res->execute($values);
    }
    catch (PDOException $e)
    {
        echo 'Query error.' . $e;
        die();
    }
}
?>