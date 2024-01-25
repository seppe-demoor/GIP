<?php

/* Host name of the MySQL server. */
$host = 'yorben.go-ao.be';
/* MySQL account username. */
$user = '06InfoYorben';
/* MySQL account password. */
$passwd = 'y0rbm31r3n';
/* The default schema you want to use. */
$dbname = 'GIP1_facturatie';
/* The PDO object. */
$pdo = NULL;
/* Connection string, or "data source name". */
$dsn = 'mysql:host=' . $host . ';dbname=' . $dbname . ';charset=utf8';
// Set options
$options = array(
    PDO::ATTR_PERSISTENT    => true,
    PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8",
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
);
/* Connection inside a try/catch block. */
try
{
    /* PDO object creation. */
    $pdo = new PDO($dsn, $user, $passwd, $options);
}
catch (PDOException $e)
{
    /* If there is an error, an exception is thrown. */
    echo 'Database connection failed.';
    var_dump($e);
    die();
}

// Using mysqli for an alternative connection
$conn = new mysqli($host, $user, $passwd, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Cannot connect to the database: " . $conn->connect_error);
}

?>
