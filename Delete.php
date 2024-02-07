<?php
require("start.php");
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = $_POST['id'];

    require('pdo.php');

    $query = "UPDATE users SET active = 0 WHERE id = :id";
    $values = [':ID' => $id];

    try {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_STR);
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
