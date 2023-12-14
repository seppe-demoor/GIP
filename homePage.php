<!DOCTYPE html>
<?php
require("start.php");
require("header.php");



//require("pdo.php");

/*$queryGasten = "SELECT g.'datum', g.'bericht', u.'userName' FROM 'gastenboek' g, 'users' u WHERE g.'GUID' = u.'GUID'";
try {
    $res = $pdo->prepare($queryGasten);
    $res->execute();
}
catch (PDOException $e) {
    //error query
    echo 'Query error <br>'.$e;
    die();
}
*/
?>
<style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #E9E2D6;
            margin: 0;
            padding: 0;
        }
        </style>

    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-2">
                <h3></h3>
            </div>
            <div class="col-sm-8">
            </div>
        </div>
    </div>

