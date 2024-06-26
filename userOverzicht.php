<?php
// Start de sessie
require("start.php");

// Controleer of de gebruiker een admin is, zo niet, stuur door naar de loginpagina
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] == 0) {
    header("Location: loginPage.php");
    exit;
}

// Verbind met de database
require("pdo.php");

// Controleer of de parameter 'deleted' is ingesteld om te bepalen of verwijderde gebruikers moeten worden weergegeven
$deleted = isset($_GET['deleted']);
$query = $deleted ? "SELECT `id`,`naam`,`voornaam`,`email`,`phone_number`,`admin` FROM `users` WHERE `active` = 0" : "SELECT `id`,`naam`,`voornaam`,`email`,`phone_number`,`admin` FROM `users` WHERE `active` = 1";

try {
    // Bereid en voer de query uit
    $res = $pdo->prepare($query);
    $res->execute();
} catch (PDOException $e) {
    // Toon een foutmelding bij een queryfout
    echo 'Query error: ' . $e->getMessage();
    die();
}

// Vereist het headerbestand
require("header.php");
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-12">
            <span class="float-end">
                <?php if ($deleted): ?>
                    <!-- Link om actieve gebruikers weer te geven -->
                    <a href="userOverzicht.php"><i class="bi bi-person-heart fs-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Actieve gebruikers"></i></a>
                <?php else: ?>
                    <!-- Link om een nieuwe gebruiker toe te voegen -->
                    <a href="userNew.php"><i class="bi bi-person-plus-fill fs-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Nieuwe gebruiker"></i></a>
                    &nbsp;
                    <!-- Link om verwijderde gebruikers weer te geven -->
                    <a href="userOverzicht.php?deleted"><i class="bi bi-person-fill-slash fs-2 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijderde gebruikers"></i></a>
                <?php endif; ?>
            </span>
            <h3>Overzicht <?php echo $deleted ? "verwijderde" : ""; ?> gebruikers</h3>
            <table class="table table-hover table-striped">
                <tr>
                    <th>Naam</th>
                    <th>Voornaam</th>
                    <th>Email</th>
                    <th>Telefoonnummer</th>
                    <th>Admin</th>
                    <th>Acties</th>
                </tr>
                <?php if ($res->rowCount() != 0): ?>
                    <!-- Loop door de resultaten en toon de gegevens in de tabel -->
                    <?php while ($row = $res->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?php echo $row["naam"]; ?></td>
                            <td><?php echo $row["voornaam"]; ?></td>
                            <td><?php echo $row["email"]; ?></td>
                            <td><?php echo $row["phone_number"]; ?></td>
                            <td><?php echo $row["admin"] ? '<i class="bi bi-check-square-fill text-success"></i>' : '<i class="bi bi-square"></i>'; ?></td>
                            <td>
                                <?php if ($deleted): ?>
                                    <!-- Pictogram om een gebruiker opnieuw te activeren -->
                                    <i id="Activate" class="bi bi-person-up text-success fs-2" onclick='showModalReactivate("<?php echo $row["email"];?>","<?php echo $row["id"];?>")' data-bs-toggle="modal" data-bs-target="#ReactivateUser" data-bs-toggle="tooltip" data-bs-placement="top" title="Gebruiker terug activeren"></i>
                                <?php else: ?>
                                    <!-- Link om een gebruiker te bewerken -->
                                    <a href="userUpdate.php?id=<?php echo $row["id"]; ?>"><i class="bi bi-pencil text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit gebruiker"></i></a>
                                    <!-- Pictogram om een gebruiker te verwijderen -->
                                    <i id="Delete" class="bi bi-trash text-danger" onclick='showModalDelete("<?php echo $row["email"];?>","<?php echo $row["id"];?>")' data-bs-toggle="modal" data-bs-target="#DeleteUser" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijder gebruiker"></i>
                                    <!-- Link om het wachtwoord van een gebruiker te resetten -->
                                    <a href="resetUser.php?id=<?php echo $row["id"]; ?>"><i class="bi bi-unlock text-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Reset wachtwoord"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Bericht als er geen gegevens zijn gevonden -->
                    <tr><td colspan='6'>Geen gegevens gevonden</td></tr>
                <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal voor het verwijderen van een gebruiker -->
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

<!-- Modal voor het heractiveren van een gebruiker -->
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
    // Functie om de verwijder-modal weer te geven
    function showModalDelete(email, uuid) {
        document.getElementById('userDEL').innerHTML = email;
        document.getElementById('KnopVerwijder').value = uuid;
    }

    // Functie om een gebruiker te deactiveren via AJAX
    function deactivateUser(id) {
        let ajx = new XMLHttpRequest(); // Maak een nieuw XMLHttpRequest object aan
        ajx.onreadystatechange = function () {
            if (ajx.readyState == 4 && ajx.status == 200) { // Controleer of de request voltooid is en succesvol was
                location.reload(); // Herlaad de pagina om de wijzigingen weer te geven
            }
        };
        ajx.open("POST", "userDelete.php", true); // Open een POST request naar 'userDelete.php'
        ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // Stel de content-type header in voor form data
        ajx.send("id=" + id); // Verstuur de request met de gebruikers-ID
    }

    // Functie om de heractiveer-modal weer te geven
    function showModalReactivate(email, uuid) {
        document.getElementById('userACT').innerHTML = email;
        document.getElementById('KnopActivate').value = uuid;
    }

    // Functie om een gebruiker te heractiveren via AJAX
    function activateUser(id) {
        let ajx = new XMLHttpRequest(); // Maak een nieuw XMLHttpRequest object aan
        ajx.onreadystatechange = function () {
            if (ajx.readyState == 4 && ajx.status == 200) { // Controleer of de request voltooid is en succesvol was
                location.reload(); // Herlaad de pagina om de wijzigingen weer te geven
            }
        };
        ajx.open("POST", "userActivate.php", true); // Open een POST request naar 'userActivate.php'
        ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); // Stel de content-type header in voor form data
        ajx.send("id=" + id); // Verstuur de request met de gebruikers-ID
    }
</script>
<?php
// Vereist het footerbestand
require("footer.php");
?>
