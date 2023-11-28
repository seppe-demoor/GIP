<!DOCTYPE html>
<?php
require("start.php");

if (!isset($_SESSION['admin']) || $_SESSION['admin'] == 0)  {
    header("Location: beveiligd.php");
    exit;
}

require("pdo.php");
$query = "SELECT `userID`, `GUID`, `userName`, `naam`, `voornaam`, `email`, `admin`
              FROM `users` 
              WHERE `active` = 1";
    //values voor de PDO
    try {
        $res = $pdo->prepare($query);
        $res->execute();
    } catch (PDOException $e) {
        //error in query
        echo "Query error:" . $e;
        die();
    }
require("header.php");
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overzicht gebruikers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #E9E2D6;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 50px;
            margin-left: 50px;
            margin-right: 50px;
        }

        h3 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #D9D0C4;
        }

        .actions {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .modal {
            display: none;
            background: rgba(0, 0, 0, 0.7);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 400px;
            text-align: center;
        }

        .modal-content h4 {
            margin-bottom: 10px;
        }

        .modal-content p {
            margin-bottom: 20px;
        }

        .modal-content button {
            padding: 10px;
            margin-right: 10px;
            cursor: pointer;
        }
    </style>
</head>
<div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                    <?php if ($deleted): ?>
                        <a href="userOverzicht.php"><i class="bi bi-person-heart fs-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Actieve gebruikers"></i></a>
                    <?php else: ?>
                        <a href="userNew.php"><i class="bi bi-person-plus-fill fs-2 text success" data-bs-toggle="tooltip" data-bs-placement="top" title="Nieuwe gebruiker"></i></a>
                        &nbsp;
                        <a href="userOverzicht.php?deleted"><i class="bi bi-person-fill-slash fs-2 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijderde gebruikers"></i></a>
                    <?php endif; ?>
                </span>
                <h3>Overzicht
                    <?php if ($deleted) echo " verwijderde "; ?>
                    gebruikers
                </h3>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Gebruikersnaam</th>
                        <th>Naam</th>
                        <th>Voornaam</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Acties</th>
                    </tr>
                    <?php if($res->rowCount() != 0) : ?>
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?php echo $row["username"]; ?></td>
                                <td><?php echo $row["naam"]; ?></td>
                                <td><?php echo $row["voornaam"]; ?></td>
                                <td><?php echo $row["email"]; ?></td>
                                <td><?php echo $row["admin"]? '<i class="bi bi-check-square-fill text-success"></i>' : '<i class="bi bi-square"></i>'; ?></td>
                                <td>
                                    <?php if($deleted): ?>
                                        <i id="Activate" class="bi bi-person-up text-success fs-2" onclick='showModalReactivate("<?php echo $row["username"];?>","<?php echo $row["GUID"];?>")' data-bs-toggle="modal" data-bs-target="#ReactivateUser" data-bs-toggle="tooltip" data-bs-placement="top" title="Gebruiker terug activeren"></i>
                                    <?php else: ?>                                    
                                        <a href="userUpdate.php?GUID=<?php echo $row["GUID"]; ?>"><i class="bi bi-pencil-square text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit gebruiker"></i></a>
                                        <i id="Delete" class="bi bi-x-square text-danger" onclick='showModalDelete("<?php echo $row["username"];?>","<?php echo $row["GUID"];?>")' data-bs-toggle="modal" data-bs-target="#DeleteUser" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijder gebruiker"></i>
                                        <a href="userWWreset.php?GUID=<?php echo$row["GUID"]; ?>"><i class="bi bi-arrow-clockwise text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset wachtwoord"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else :?>
                        <tr><td colspan='6'>Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal delete user -->
    <div class="modal fade" id="DeleteUser">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Verwijder gebruiker</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je gebruiker <span id="userDEL"></span> wil verwijderen?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" value="" id="KnopVerwijder" class="btn btn-danger" onclick="deactivateUser(this.value)">Ja verwijder</button>
            </div>

            </div>
        </div>
    </div>

    <!-- Modal reactivate user -->
    <div class="modal fade" id="ReactivateUser">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Gebruiker terug activeren</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je gebruiker <span id="userACT"></span> wil heractiveren?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" value="" id="KnopActivate" class="btn btn-success" onclick="activateUser(this.value)">Ja heractiveer</button>
            </div>

            </div>
        </div>
    </div>

    <script>
        //Deleten van een user
        function showModalDelete(username, guid) {
            document.getElementById('userDEL').innerHTML = username;
            document.getElementById('KnopVerwijder').value = guid;
        }

        function deactivateUser(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange = function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
                    //console.log(ajx.responseText);
                    location.reload();
                }
            };
            ajx.open("POST", "userDelete.php", true);
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajx.send("GUID=" + id);
        }

        
        //Heractiveren van een user
        function showModalReactivate(username, guid) {
            document.getElementById('userACT').innerHTML = username;
            document.getElementById('KnopActivate').value = guid;
        }

        function activateUser(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange = function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
                    //console.log(ajx.responseText);
                    location.reload();
                }
            };
            ajx.open("POST", "userActivate.php", true);
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajx.send("GUID=" + id);
        }
    </script>
<?php
require("footer.php");
?>
