<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>User Overview</title>
</head>

<body>

    <?php
    require("start.php");

    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        header("Location: loginPage.php");
    }

    require("pdo.php");

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['deleted'])) {
        $query = "SELECT `GUID`,`username`,`naam`,`voornaam`,`email`,`admin` FROM `users` WHERE `active` = 0";
        $deleted = true;
    } else {
        $query = "SELECT `GUID`,`username`,`naam`,`voornaam`,`email`,`admin` FROM `users` WHERE `active` = 1";
        $deleted = false;
    }

    try {
        $res = $pdo->prepare($query);
        $res->execute();
    } catch (PDOException $e) {
        echo 'Query error.';
        die();
    }

    require("header.php");
    ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                    <?php if ($deleted) : ?>
                        <a href="userOverzicht2.0.php">Active Users</a>
                    <?php else : ?>
                        <a href="userNew.php">New User</a>
                        &nbsp;
                        <a href="userOverzicht2.0.php?deleted">Deleted Users</a>
                    <?php endif; ?>
                </span>
                <h3>User Overview<?php echo $deleted ? " Deleted" : ""; ?></h3>
                <table class="table">
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Actions</th>
                    </tr>
                    <?php if ($res->rowCount() != 0) : ?>
                        <?php while ($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?php echo $row["username"]; ?></td>
                                <td><?php echo $row["naam"]; ?></td>
                                <td><?php echo $row["voornaam"]; ?></td>
                                <td><?php echo $row["email"]; ?></td>
                                <td><?php echo $row["admin"] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <?php if ($deleted) : ?>
                                        <button class="btn btn-success" onclick='showModalReactivate("<?php echo $row["username"];?>","<?php echo $row["GUID"];?>")'>Reactivate User</button>
                                    <?php else : ?>
                                        <a href="userUpdate.php?GUID=<?php echo $row["GUID"]; ?>" class="btn btn-warning">Edit User</a>
                                        <button class="btn btn-danger" onclick='showModalDelete("<?php echo $row["username"];?>","<?php echo $row["GUID"];?>")'>Delete User</button>
                                        <a href="resetUser.php?GUID=<?php echo$row["GUID"]; ?>" class="btn btn-info">Reset Password</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan='6'>No data found</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal delete user -->
    <div class="modal" id="DeleteUser">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Delete User</h4>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    Are you sure you want to delete user <span id="userDEL"></span>?
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                    <button type="button" value="" id="KnopVerwijder" class="btn btn-danger" onclick="deactivateUser(this.value)">Yes, delete</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal reactivate user -->
    <div class="modal" id="ReactivateUser">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Reactivate User</h4>
                    <button type="button" class="btn-close" data-dismiss="modal"></button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    Are you sure you want to reactivate user <span id="userACT"></span>?
                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Cancel</button>
                    <button type="button" value="" id="KnopActivate" class="btn btn-success" onclick="activateUser(this.value)">Yes, reactivate</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Delete user
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
            ajx.open("POST", "userDelete.php", true);
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajx.send("GUID=" + id);
        }

        // Reactivate user
        function showModalReactivate(username, guid) {
            document.getElementById('userACT').innerHTML = username;
            document.getElementById('KnopActivate').value = guid;
        }

        function activateUser(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange = function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
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

</body>

</html>
