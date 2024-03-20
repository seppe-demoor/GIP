<!DOCTYPE html>
<?php
    require("start.php");
    // Controleren of de gebruiker is ingelogd als beheerder
    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        header("Location: loginPage.php");
    }

    require("pdo.php");

    // Bepalen van de query op basis van de URL en instellingen voor actieve of verwijderde landen
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['deleted'])) {
        $query = "SELECT `id`,`name`, `tax_rate`, `currency`, `code`, `iso_code` FROM `countries` WHERE `active` = 0";
        $deleted = true;
    } else {
        $query = "SELECT `id`, `name`,`tax_rate`, `currency`, `code`, `iso_code` FROM `countries` WHERE `active` = 1";
        $deleted = false;
    }

    try
    {
        $res = $pdo->prepare($query);
        $res->execute();
    }
    catch (PDOException $e)
    {
        echo 'Query error.';
        die();
    }

    require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                    <?php if ($deleted): ?>
                        <a href="countryOverzicht.php"><i class="bi bi-heart-fill fs-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Actieve landen"></i></a>
                    <?php else: ?>
                        <a href="counrtyNew.php"><i class="bi bi-plus-circle fs-3 text success" data-bs-toggle="tooltip" data-bs-placement="top" title="Nieuwe land"></i></a>
                        &nbsp;
                        <a href="countryOverzicht.php?deleted"><i class="bi bi-dash-circle fs-3 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijderde landen"></i></a>
                    <?php endif; ?>
                </span>
                <h3>Overzicht
                    <?php if ($deleted) echo " verwijderde "; ?>
                    landen
                </h3>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Land</th>
                        <th>BTW</th>
                        <th>Value</th>
                        <th>Land code</th>
                        <th>ISO code</th>
                        <th>Acties</th>
                    </tr>
                    <?php if($res->rowCount() != 0) : ?>
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?php echo $row["name"]; ?></td>
                                <td><?php echo $row["tax_rate"]; ?></td>
                                <td><?php echo $row["currency"]; ?></td>
                                <td><?php echo $row["code"]; ?></td>
                                <td><?php echo $row["iso_code"] ?></td>
                                <td>
                                    <?php if($deleted): ?>
                                        <i id="Activate" class="bi bi-arrow-clockwise text-success fs-2" onclick='showModalReactivate("<?php echo $row["name"];?>","<?php echo $row["id"];?>")' data-bs-toggle="modal" data-bs-target="#Reactivatecountry" data-bs-toggle="tooltip" data-bs-placement="top" title="Gebruiker terug activeren"></i>
                                    <?php else: ?>                                    
                                        <a href="countryUpdate.php?id=<?php echo $row["id"]; ?>"><i class="bi bi-pencil text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit gebruiker"></i></a>
                                        <i id="countryDelete" class="bi bi-trash text-danger" onclick='showModalDelete("<?php echo $row["name"];?>","<?php echo $row["id"];?>")' data-bs-toggle="modal" data-bs-target="#Deletecountry" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijder gebruiker"></i>
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

    <!-- Modal delete country -->
    <div class="modal fade" id="Deletecountry">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Verwijder gebruiker</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je gebruiker <span id="countryDEL"></span> wil verwijderen?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" value="" id="KnopVerwijder" class="btn btn-danger" onclick="deactivatecountry(this.value)">Ja verwijder</button>
            </div>

            </div>
        </div>
    </div>

    <!-- Modal reactivate country -->
    <div class="modal fade" id="Reactivatecountry">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Gebruiker terug activeren</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je gebruiker <span id="countryACT"></span> wil heractiveren?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" value="" id="KnopActivate" class="btn btn-success" onclick="activatecountry(this.value)">Ja heractiveer</button>
            </div>

            </div>
        </div>
    </div>

    <script>
        //Deleten van een country
        function showModalDelete(name, id) {
            document.getElementById('countryDEL').innerHTML = name;
            document.getElementById('KnopVerwijder').value = id;
        }

        function deactivatecountry(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange = function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
                    //console.log(ajx.responseText);
                    location.reload();
                }
            };
            ajx.open("POST", "countryDelete.php", true);
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajx.send("id=" + id);
        }

        
        //Heractiveren van een country
        function showModalReactivate(name, id) {
            document.getElementById('countryACT').innerHTML = name;
            document.getElementById('KnopActivate').value = id;
        }

        function activatecountry(id) {
            let ajx = new XMLHttpRequest();
            ajx.onreadystatechange = function () {
                if (ajx.readyState == 4 && ajx.status == 200) {
                    //console.log(ajx.responseText);
                    location.reload();
                }
            };
            ajx.open("POST", "countryActivate.php", true);
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajx.send("id=" + id);
        }
    </script>
