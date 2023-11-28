<?php
require("start.php");

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['GUID'])) {
    $GUID = $_POST['GUID'];
    require("pdo.php");

    $query = "UPDATE `users` SET `active`=0 WHERE `GUID` = :ID";

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

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gebruikers Overzicht</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 50px;
        }

        .float-end {
            float: right;
        }

        h3 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #ffffff;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            user-select: none;
            background-color: #28a745;
            border: 1px solid #28a745;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-warning {
            color: #212529;
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            color: #212529;
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="float-end">
            <a href="userNew.php" class="btn btn-primary" title="Nieuwe gebruiker">
                <i class="bi bi-person-plus-fill"></i>
            </a>
            <a href="userNew.php" class="btn btn-danger">
                <i class="bi bi-person-plus-fill"></i>
            </a>
        </div>

        <h3>Overzicht gebruikers</h3>

        <table>
            <tr>
                <th>Gebruikersnaam</th>
                <th>Naam</th>
                <th>Voornaam</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Acties</th>
            </tr>
            <?php while ($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                <tr>
                    <td><?php echo $row["username"]; ?></td>
                    <td><?php echo $row["naam"]; ?></td>
                    <td><?php echo $row["voornaam"] ?></td>
                    <td><?php echo $row["email"] ?></td>
                    <td><?php echo $row["admin"] ? '<i class="bi bi-check-square-fill text-success"></i>' : '<i class="bi bi-square"></i>'; ?></td>
                    <td>
                        <!-- Action buttons or links can be added here -->
                        <a href="#" onclick="showModalDelete('<?php echo $row["username"]; ?>', '<?php echo $row["GUID"]; ?>')" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#DeleteUser">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Modal delete user -->
    <div class="modal" id="DeleteUser">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Verwijder user</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je de gebruiker <span id="userDEL"></span> wil verwijderen?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" class="btn btn-danger" value="" id="KnopVerwijder" onclick="deactivateUser(this.value)">Ja verwijder</button>
            </div>
        </div>
    </div>

    <script>
        function showModalDelete(username, guid) {
            document.getElementById('userDEL').innerHTML = username;
            document.getElementById('KnopVerwijder').value = guid;
        }

        function deactivateUser(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange = function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
                    location.reload();
                }
            };
            ajx.open("POST", "confirmDelete.php", true);
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajx.send("GUID=" + id);
        }
    </script>
</body>
</html>

<?php
require("footer.php");
?>
