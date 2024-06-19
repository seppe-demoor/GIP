<?php
require("start.php");

// Check if user is logged in, otherwise redirect to login page
if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");
    exit;
}

// Handle POST request to deactivate a user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    try {
        require("pdo.php"); // Include PDO database connection
        $id = $_POST['id']; // Extract user ID from POST data
        $query = "UPDATE `users` SET `active` = 0 WHERE `id` = :ID"; // SQL query to update user status to inactive
        $values = [":ID" => $id]; // Bind ID parameter for PDO execution

        try {
            $res = $pdo->prepare($query); // Prepare SQL query
            $res->execute($values); // Execute SQL query with bound parameters
        } catch (PDOException $e) {
            echo 'Query error: ' . $e->getMessage(); // Handle PDOException (database query error)
            die();
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage(); // Handle general Exception (other PHP error)
        die();
    }
}
?>
