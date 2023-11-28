<?php
require("start.php");

if (!isset($_SESSION["username"])) {
    header("Location: loginPage.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['GUID'])) {
    $GUID = $_POST['GUID'];
    require("pdo.php");

    $query = "UPDATE `users` SET `active`=1 WHERE `GUID` = :ID";

    $values = [":ID" => $GUID];

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