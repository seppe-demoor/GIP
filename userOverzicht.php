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

<body>

    <div class="container">
        <div class="row">
            <div class="col-sm-12">
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
                            <td><?php echo $row["userName"]; ?></td>
                            <td><?php echo $row["naam"]; ?></td>
                            <td><?php echo $row["voornaam"] ?></td>
                            <td><?php echo $row["email"] ?></td>
                            <td><?php echo $row["admin"] ? '✔️' : '❌';?></td>
                            <td class="actions">
                                <a href="userUpdate.php?GUID=<?php echo $row['GUID']; ?>">✏️</a>
                                <a href="confirmDelete.php?GUID=<?php echo $row['GUID']; ?>">❌</a>
                                <a href="WWreset.php?GUID=<?php echo $row['GUID']; ?>">🔄</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <h4>Verwijderen</h4>
            <p>Weet je zeker dat je de gebruiker wilt verwijderen?</p>
            <button onclick="deleteUser()">Ja</button>
            <button onclick="closeModal()">Nee</button>
        </div>
    </div>

</body>

</html>
<?php
require("footer.php");
?>
