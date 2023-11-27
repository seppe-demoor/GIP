<!DOCTYPE html>
<?php
require("start.php");

if (!isset($_SESSION['username'])) {
    //user is reeds aangemeld
    header("Location: login.php");
    exit;
}

require("pdo.php");
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['deleted'])) {
    $query = "SELECT `userID`, `GUID`, `userName`, `naam`, `voornaam`, `email`, `admin`
    FROM `users` 
    WHERE `active` = 0";
    $deleted = true;   
} else {
    $query = "SELECT `name`, `tax_rate`, `currency`, `code`, `iso_code`
              FROM `countries`";
    $deleted = false;
}

try {
    $res = $pdo->prepare($query);
    $res->execute();
} catch (PDOException $e) {
    echo "Query error:" . $e;
    die();
}
require("header.php");
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Overview</title>
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

        .float-end {
            float: right;
        }

        h3 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
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

        .text-primary {
            color: #007bff;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-success {
            color: #28a745;
        }

        .text-info {
            color: #17a2b8;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
            outline: none;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-warning {
            background-color: #ffc107;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                    <?php if ($deleted): ?>
                        <a href="userOverview.php?deleted" class="btn btn-danger">
                            <i class="bi bi-person-plus-fill fs-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="userNew.php" class="btn btn-primary">
                            <i class="bi bi-person-plus-fill fs-2"></i> New user
                        </a>
                        <a href="userOverview.php?deleted" class="btn btn-danger">
                            <i class="bi bi-person-fill-slash fs-2"></i> Delete user
                        </a>
                    <?php endif; ?>
                </span>
                <h3>Overview <?php echo $deleted ? "Deleted" : ""; ?></h3>
                <table>
                    <tr>
                        <th>name</th>
                        <th>taxe_rate</th>
                        <th>currency</th>
                        <th>code</th>
                        <th>iso_code</th>
                    </tr>
                    <?php if ($res->rowCount() != 0) : ?>
                        <?php while ($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?php echo $row["name"]; ?></td>
                                <td><?php echo $row["tax_rate"]."%"; ?></td>
                                <td><?php echo $row["currency"] ?></td>
                                <td><?php echo $row["code"] ?></td>
                                <td><?php echo $row["iso_code"]?></td>
                                <!--<td class="actions">
                                    <a href="userUpdate.php?GUID=<?php echo $row['GUID']; ?>">✏️
                                    <i data-bs-toggle="tooltip" data-bs-placement="top" title="Update user"></i>
                                    </a>
                                    <i style="cursor: pointer;" class="bi bi-x-square text-danger fa-2x"
                                            onclick='showModalDelete("<?php echo $row["userName"]; ?> "," <?php echo $row["GUID"]; ?>")'
                                            data-bs-toggle="modal" data-bs-target="#DeleteUser"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Delete user">❌</i>
                                    <a href="WWreset.php?GUID=<?php echo $row['GUID']; ?>">🔄
                                    <i data-bs-toggle="tooltip" data-bs-placement="top" title="restwachtwoord"></i>
                                    </a>
                                </td>-->
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">No data found</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="modal" id="DeleteUser">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete user</h4>
                <button type="button" class="btn-close" onclick="closeModal()"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the user <span id="userDEL"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn btn-danger" value="" id="KnopVerwijder"
                        onclick="deactivateUser(this.value)">Yes, delete</button>
            </div>
        </div>
    </div>

    <script>
        function showModalDelete(username, guid) {
            document.getElementById('userDEL').innerHTML = username;
            document.getElementById('KnopVerwijder').value = guid;
            document.querySelector('.modal').style.display = 'flex';
        }

        function closeModal() {
            document.querySelector('.modal').style.display = 'none';
        }

        function deactivateUser(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange= function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
                    closeModal();
                    //location.reload();
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
