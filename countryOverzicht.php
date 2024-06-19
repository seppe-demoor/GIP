<?php
    // Start de sessie en laad initiële instellingen
    require("start.php");

    // Controleer of de gebruiker ingelogd is als beheerder
    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        // Als de gebruiker geen beheerder is, stuur dan naar de inlogpagina
        header("Location: loginPage.php");
    }

    // Laad het bestand voor de databaseverbinding
    require("pdo.php");

    // Bepaal de query op basis van de URL en instellingen voor actieve of verwijderde landen
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['deleted'])) {
        // Query voor verwijderde landen (actieve status = 0)
        $query = "SELECT `id`,`name`, `tax_rate`, `currency`, `code`, `iso_code` FROM `countries` WHERE `active` = 0";
        $deleted = true; // Zet de status op verwijderde landen
    } else {
        // Query voor actieve landen (actieve status = 1)
        $query = "SELECT `id`, `name`,`tax_rate`, `currency`, `code`, `iso_code` FROM `countries` WHERE `active` = 1";
        $deleted = false; // Zet de status op actieve landen
    }

    try {
        // Bereid de query voor en voer deze uit
        $res = $pdo->prepare($query);
        $res->execute();
    } catch (PDOException $e) {
        // Foutmelding als de query mislukt
        echo 'Query error.';
        die();
    }

    // Laad de header voor de pagina
    require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                    <?php if ($deleted): ?>
                        <!-- Link naar actieve landen -->
                        <a href="countryOverzicht.php"><i class="bi bi-heart-fill fs-2 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Actieve landen"></i></a>
                    <?php else: ?>
                        <!-- Link naar het toevoegen van een nieuw land -->
                        <a href="countryNew.php"><i class="bi bi-plus-circle fs-3 text-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Nieuwe land"></i></a>
                        &nbsp;
                        <!-- Link naar verwijderde landen -->
                        <a href="countryOverzicht.php?deleted"><i class="bi bi-dash-circle fs-3 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijderde landen"></i></a>
                    <?php endif; ?>
                </span>
                <h3>Overzicht
                    <?php if ($deleted) echo " verwijderde "; ?> <!-- Als $deleted active is gaat verwijderde komen staan op pagina verwijderde landen -->
                    landen
                </h3>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Land</th>
                        <th>BTW</th>
                        <th>Valuta</th>
                        <th>Land code</th>
                        <th>ISO code</th>
                        <th>Acties</th>
                    </tr>
                    
                    <?php if($res->rowCount() != 0) : ?>  <!-- Controleert of de query resultaten heeft opgeleverd -->
                        <!-- Toon de landen in de tabel -->
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>  <!-- Haalt elke rij uit de queryresultaten op -->
                            <tr>
                                <td><?php echo $row["name"]; ?></td>
                                <td><?php echo $row["tax_rate"]; ?></td>
                                <td><?php echo $row["currency"]; ?></td>
                                <td><?php echo $row["code"]; ?></td>
                                <td><?php echo $row["iso_code"] ?></td>
                                <td>
                                    <?php if($deleted): ?>
                                        <!-- Knop om het land te heractiveren -->
                                        <i id="Activate" class="bi bi-arrow-clockwise text-success fs-2" onclick='showModalReactivate("<?php echo $row["name"];?>","<?php echo $row["id"];?>")' data-bs-toggle="modal" data-bs-target="#Reactivatecountry" data-bs-toggle="tooltip" data-bs-placement="top" title="Land terug activeren"></i>
                                    <?php else: ?>                                    
                                        <!-- Link om het land te bewerken -->
                                        <a href="countryUpdate.php?id=<?php echo $row["id"]; ?>"><i class="bi bi-pencil text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Bewerk land"></i></a>
                                        <!-- Knop om het land te verwijderen -->
                                        <i id="countryDelete" class="bi bi-trash text-danger" onclick='showModalDelete("<?php echo $row["name"];?>","<?php echo $row["id"];?>")' data-bs-toggle="modal" data-bs-target="#Deletecountry" data-bs-toggle="tooltip" data-bs-placement="top" title="Verwijder land"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else :?>
                        <!-- Bericht als er geen gegevens zijn -->
                        <tr><td colspan='6'>Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal om land te verwijderen -->
    <div class="modal fade" id="Deletecountry">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Verwijder land</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je land <span id="countryDEL"></span> wil verwijderen?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" value="" id="KnopVerwijder" class="btn btn-danger" onclick="deactivatecountry(this.value)">Ja, verwijder</button>
            </div>

            </div>
        </div>
    </div>

    <!-- Modal om land te heractiveren -->
    <div class="modal fade" id="Reactivatecountry">
        <div class="modal-dialog">
            <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <h4 class="modal-title">Land terug activeren</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                Ben je zeker dat je land <span id="countryACT"></span> wil heractiveren?
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Annuleer</button>
                <button type="button" value="" id="KnopActivate" class="btn btn-success" onclick="activatecountry(this.value)">Ja, heractiveer</button>
            </div>

            </div>
        </div>
    </div>

    <script>
        // Functie om het verwijderingsmodal te tonen met landgegevens
        function showModalDelete(name, id) {
            document.getElementById('countryDEL').innerHTML = name; //Dit zorgt ervoor dat de naam van het land in de modal wordt getoond
            document.getElementById('KnopVerwijder').value = id;  // Dit zorgt ervoor dat de knop weet welk land moet worden verwijderd wanneer erop wordt geklikt
        }

        // Functie om een land te deactiveren via AJAX
        function deactivatecountry(id) {
            // Maak een nieuw XMLHttpRequest object aan om een HTTP-verzoek te versturen
            let ajx = new XMLHttpRequest();

            // Definieer een functie die wordt aangeroepen wanneer de readyState van het verzoek verandert
            ajx.onreadystatechange = function () {
                // Controleer of het verzoek is voltooid (readyState 4) en of de status 'OK' is (status 200)
                if (ajx.readyState == 4 && ajx.status == 200) {
                    // Herlaad de pagina na succesvol verwijderen
                    location.reload();
                }
            };

            // Open een nieuw POST-verzoek naar het script 'countryDelete.php'
            ajx.open("POST", "countryDelete.php", true);

            // Stel de Content-type header in om aan te geven dat de gegevens in het formulier-formaat worden verzonden
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            // Stuur het verzoek met de ID van het land dat moet worden gedeactiveerd
            ajx.send("id=" + id);
        }

        

        // Functie om het heractivatiemodal te tonen met landgegevens
        function showModalReactivate(name, id) {
            // Zoek het HTML-element met ID 'countryACT' en zet de inhoud ervan op de opgegeven naam
            document.getElementById('countryACT').innerHTML = name;
            // Zoek de knop met ID 'KnopActivate' en zet de waarde ervan op het opgegeven id
            document.getElementById('KnopActivate').value = id;
        }

        // Functie om een land te heractiveren via AJAX
        function activatecountry(id) {
            // Maak een nieuw XMLHttpRequest object aan om een HTTP-verzoek te versturen
            let ajx = new XMLHttpRequest();
            // Definieer een functie die wordt aangeroepen wanneer de readyState van het verzoek verandert
            ajx.onreadystatechange = function () {
                // Controleer of het verzoek is voltooid (readyState 4) en of de status 'OK' is (status 200)
                if (ajx.readyState == 4 && ajx.status == 200) {
                    // Herlaad de pagina na succesvol heractiveren
                    location.reload();
                }
            };
            // Open een nieuw POST-verzoek naar het script 'countryActivate.php'
            ajx.open("POST", "countryActivate.php", true);
            // Stel de Content-type header in om aan te geven dat de gegevens in het formulier-formaat worden verzonden
            ajx.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            // Stuur het verzoek met de ID van het land dat moet worden heractiveren
            ajx.send("id=" + id);
        }

    </script>
<?php
    // Laad de footer voor de pagina
    require("footer.php");
?>
