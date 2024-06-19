<?php 
require_once('pdo.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Undefined Schedule ID.');
    }

    $delete = $conn->query("DELETE FROM `work_time` WHERE id = '{$_GET['id']}'");

    if ($delete) {
        echo "<script> location.replace('./homePage.php') </script>";
    } else {
        throw new Exception("An Error occurred.\nError: " . $conn->error . "\nSQL: " . $conn->last_query);
    }
} catch (Exception $e) {
    echo "<script> alert('{$e->getMessage()}'); location.replace('./') </script>";
} finally {
    $conn->close();
    exit;
}
?>
