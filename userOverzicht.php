<?php
require("start.php");
require("header.php");

if(!isset($_SESSION["username"])) {
    header("location: loginPage.php");
    exit;
}

require("pdo.php");

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['deleted'])) {
    $query = "SELECT GUID, username, naam, voornaam, email, passwordReset, active, admin, userPassword FROM users WHERE active =0";
    $deleted = true;
} else {
    $query = "SELECT GUID, username, naam, voornaam, email, passwordReset, active, admin, userPassword FROM users WHERE active =1";
    $deleted = false;
}

try {
    $res = $pdo->prepare($query);
    $res->execute();
} catch (PDOException $e) {
    //error query
    echo 'Query error <br>'.$e;
    die();
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-sm-12">
            <span class="float-end">
                <?php if ($deleted): ?>
                    <a href="userOverzicht.php"><i class="bi bi-person-heart fs-2 text-succes" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="active gebruikers"></i></a>
                <?php else : ?>
                    <a href="userNew.php"><i class="bi bi-person-plus-fill fs-2" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Reset wachtwoord"></i></a>
                    &nbsp;
                    <a href="users.php?deleted"><i class="bi bi-person-fill-slach fs-2 text-danger"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="verwijderde gebruikers"></i></a>
                <?php endif; ?>
            </span>
            <h3>overzicht
                <?php if ($deleted) echo " verwijderde "; ?>
                gebruikers
            </h3>
            <table class="table table-hover table-striped">
                <!-- Table content here -->
            </table>
        </div>
    </div>
</div>

<!-- Modal Delete user -->
<div class="modal fade" id="DeleteUser">
    <!-- Modal content here -->
</div>

<script>
    function showModalDelete(username, guid) {
        document.getElementById('userDEL').innerHTML = username;
        document.getElementById('KnopVerwijder').value = guid;
    }

    function deactivateUser(id) {
        console.log(id);
        let ajx = new XMLHttpRequest();
        ajx.onreadystatechange = function () {
            if (ajx.readyState == 4 && ajx.status == 200) {
                console.log(ajx.responseText);
                location.reload();
            }
        };
        ajx.open("POST", "userDelete.php", true);
        ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajx.send("GUID=" + id);
    }
</script>

<?php
require("footer.php");
?>
