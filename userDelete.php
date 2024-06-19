<?php
require("start.php");

if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    try {
        require("pdo.php");
        $id = $_POST['id'];
        $query = "UPDATE `users` SET `active` = 0 WHERE `id` = :ID";
        $values = [":ID" => $id];

        try {
            $res = $pdo->prepare($query);
            $res->execute($values);
        } catch (PDOException $e) {
            echo 'Query error: ' . $e->getMessage();
            die();
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
        die();
    }
}
?>
