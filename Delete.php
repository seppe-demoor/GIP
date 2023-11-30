<?php
require("start.php")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['GUID'])) {
    $GUID = $_POST['GUID'];

    require('pdo.php');

    $query = "UPDATE users SET active = 0 WHERE GUID = :GUID";
    $values = [':ID' => $GUID]

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':GUID', $GUID, PDO::PARAM_STR);
        $stmt->execute($values);


        header("Location: useroverzicht.php");
        exit();
    } catch (PDOException $e) {

        error_log('Query error: ' . $e->getMessage());

        header("Location: useroverzicht.php");
        exit();
    }
}
?>
