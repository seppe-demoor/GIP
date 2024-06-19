<?php
require("start.php"); // Laad het startbestand voor initiële instellingen en sessiebeheer
require("pdo.php"); // Laad het PDO-bestand voor de databaseverbinding

// Stel de locale in op Nederlands
setlocale(LC_TIME, 'nl_NL');

// Als de gebruiker niet is ingelogd, doorsturen naar de inlogpagina
if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php"); // Laad de header voor de pagina

// Logica voor het beëindigen van een project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["end_project"])) {
    if (isset($_POST["title"]) && isset($_POST["description"])) {
        $title = $_POST["title"];
        $description = $_POST["description"];
        $project_id = $_POST["project_id"];

        try {
            // Voorbereiden van de query om de eindtijd van het project bij te werken
            $stmt = $conn->prepare("UPDATE `projects` SET `end_time` = NOW() WHERE `title` = ?");
            $stmt->bind_param("s", $title); // Bind de titel parameter
            $stmt->execute(); // Voer de query uit
        } catch (Exception $e) {
            die($e->getMessage()); // Stop het script en toon de foutmelding als er een uitzondering optreedt
        }
    }
}

// Ophalen van schema's en projecten uit de database
try {
    // Query om schema's op te halen die zijn gekoppeld aan projecten
    $schedules = $conn->query("SELECT l.id as work_id, p.id as project_id, p.title, p.description, l.start_time, l.end_time FROM projects p JOIN work_time l ON p.id = l.project_id;");
    // Query om alle projecten op te halen
    $projects = $conn->query("SELECT * FROM `projects`");

    if (!$schedules || !$projects) {
        throw new Exception("Query error: " . $conn->error); // Gooi een uitzondering als een van de queries mislukt
    }

    $sched_res = [];
    $project_res = [];

    // Vul $project_res array met projectgegevens
    while ($row = $projects->fetch_assoc()) {
        $project_res[$row['id']] = $row;
    }

    // Loop door elke rij in de resultaten van de $schedules query
        while ($row = $schedules->fetch_assoc()) {
            // Controleer of de 'start_time' niet leeg is; zo ja, converteer het naar een leesbaar datumformaat
            // Zo niet, zet het op 'N/A'
            $row['sdate'] = !empty($row['start_time']) ? date("F d, Y h:i A", strtotime($row['start_time'])) : 'N/A';             
            $row['edate'] = !empty($row['end_time']) ? date("F d, Y h:i A", strtotime($row['end_time'])) : 'N/A';
        
            // Voeg de verwerkte rij toe aan de $sched_res array, waarbij de 'work_id' als sleutel wordt gebruikt
            $sched_res[$row['work_id']] = $row;
        }

} catch (Exception $e) {
    die($e->getMessage()); // Stop het script en toon de foutmelding als er een uitzondering optreedt
}

// Hanteren van projectselectie
$selectedProject = null;
$selectedProjectId = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["select_project"])) {
    $selectedProjectId = $_POST["project_id"];
    $_SESSION["selected_project_id"] = $selectedProjectId;
}

if (isset($_SESSION["selected_project_id"])) {
    $selectedProjectId = $_SESSION["selected_project_id"];
    try {
        // Query om het geselecteerde project op te halen
        $selectedProject = $conn->query("SELECT title, description FROM `projects` WHERE `id` = '$selectedProjectId'")->fetch_assoc();
        if (!$selectedProject) {
            throw new Exception("Geen project gevonden met ID: $selectedProjectId"); // Gooi een uitzondering als het project niet wordt gevonden
        }
    } catch (Exception $e) {
        die($e->getMessage()); // Stop het script en toon de foutmelding als er een uitzondering optreedt
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["start_project"])) {
    $_SESSION["green_bar_visible"] = true;
}

$greenBarVisible = isset($_SESSION["green_bar_visible"]) ? $_SESSION["green_bar_visible"] : false;
?>

<body class="bg-light">
    <div id="green-bar" class="alert alert-success text-center" style="display: <?= $greenBarVisible ? 'block' : 'none' ?>">
        <strong>Je <?= $selectedProject['title'] ?? 'geen project' ?> is nog aan het lopen</strong>
    </div>
    <div class="container py-5" id="page-container">
        <div class="row">
            <div class="col-md-9">
                <div id="calendar"></div>
            </div>

            <div class="col-md-3">
                <div class="text-end">
                    <form method="post" action="invoice.php">
                        <button type="submit" class="btn btn-primary btn-sm rounded-0" name="factuur">Maak Factuur</button>
                    </form>
                </div>

                <div class="card rounded-0 shadow">
                    <div class="card-header bg-gradient bg-primary text-light">
                        <h5 class="card-title">Tijden Opslaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form action="save_schedule.php" method="post" id="schedule-form">
                                <input type="hidden" name="id" value="">
                                <div class="form-group mb-2">
                                    <label for="title" class="control-label">Titel</label>
                                    <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" required value="<?= $selectedProject['title'] ?? '' ?>">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="description" class="control-label">Beschrijving</label>
                                    <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="description" required><?= $selectedProject['description'] ?? '' ?></textarea>
                                </div>
                                <div id="dateTimeContainer" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label for="start_time" class="control-label">Start</label>
                                        <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_time" id="start_time">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="end_time" class="control-label">Eind</label>
                                        <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_time" id="end_time">
                                    </div>
                                </div>

                                <div id="buttonsContainer" class="form-group mb-2 text-center">
                                    <?php if ($selectedProject) : ?>
                                        <button class="btn btn-success btn-sm rounded-0" type="button" onclick="startProject()">Start</button>
                                        <button class="btn btn-danger btn-sm rounded-0" type="button" onclick="endProject()">Eind</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="text-center">
                        <button id="saveButton" class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form" style="display: none;"><i class="fa fa-save"></i> Save</button>
                        <button id="cancelButton" class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form" style="display: none;"><i class="fa fa-reset"></i> Cancel</button>
                    </div>
                </div>

                <div class="card rounded-0 shadow mt-3">
                    <div class="card-header bg-gradient bg-primary text-light mt-3">
                        <h5 class="card-title">Mijn Project</h5>
                    </div>
                    <div class="card-body">
                        <form action="homePage.php" method="post" id="project-form">
                            <div class="form-group mb-2">
                                <label for="projectSelect" class="control-label">Selecteer een Project</label>
                                <select class="form-control form-control-sm rounded-0" name="project_id" id="projectSelect">
                                    <?php foreach ($project_res as $projectId => $project) : ?>
                                        <option value="<?= $projectId ?>" <?= ($selectedProjectId == $projectId) ? "selected" : "" ?>><?= $project['title'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-0" type="submit" name="select_project"><i class="fa fa-check"></i> Selecteer Project</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>    

    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title">kalender Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">
                    <div class="container-fluid">
                        <dl>
                            <dt class="text-muted">Titel</dt>
                            <dd id="title" class="fw-bold fs-4"></dd>
                            <dt class="text-muted">Beschrijving</dt>
                            <dd id="description" class=""></dd>
                            <dt class="text-muted">Start</dt>
                            <dd id="start" class=""></dd>
                            <dt class="text-muted">Eind</dt>
                            <dd id="end" class=""></dd>
                        </dl>
                    </div>
                </div>
                <div class="modal-footer rounded-0">
                    <div class="text-end">
                        <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="">Bewerk</button>
                        <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="">Verwijder</button>
                        <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Sluiten</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-edit-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title">Evenement Bewerken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">
                    <form action="save_schedule.php" method="post" id="edit-event-form">
                        <input type="hidden" name="action" value="edit_event">
                        <input type="hidden" name="event_id" id="edit-event-id">
                        <div class="form-group mb-2">
                            <label for="edit-title" class="control-label">Titel</label>
                            <input type="text" class="form-control form-control-sm rounded-0" name="title" id="edit-title" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="edit-description" class="control-label">Beschrijving</label>
                            <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="edit-description" required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label for="edit-start-time" class="control-label">Startdatum</label>
                            <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_time" id="edit-start-time" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="edit-end-time" class="control-label">Einddatum</label>
                            <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_time" id="edit-end-time" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm rounded-0">Bijwerken</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($conn)) $conn->close(); // Sluit de databaseverbinding als deze bestaat
    ?>
    <script>
        var scheds = <?php echo json_encode($sched_res); ?>;

        function startProject() {
            // Start een AJAX-verzoek om een project te starten
            $.ajax({
               
                type: 'POST',
                // Stel de URL in waar het verzoek naar toe moet
                url: 'save_schedule.php',
                // Specificeer de gegevens die naar de server worden gestuurd
                data: {
                    action: 'start_project',
                    // Gebruik de project_id, waarbij een standaardwaarde van 1 wordt gebruikt als $selectedProjectId nul is
                    project_id: <?php echo is_null($selectedProjectId) ? 1 : $selectedProjectId ?>,
                    // Haal de waarde op van het inputveld met ID 'title'
                    title: $('#title').val(),
                    // Haal de waarde op van het inputveld met ID 'description'
                    description: $('#description').val()
                },
                // Functie die wordt uitgevoerd als het verzoek succesvol is
                success: function (response) {
                    // Toon de groene balk
                    $('#green-bar').show();
                    // Zet de sessievariabele "green_bar_visible" op true
                    <?php $_SESSION["green_bar_visible"] = true; ?>
                },
                error: function (error) {
                    alert("Error starting project");
                }
            });
        }


        function endProject() {
            // Start een AJAX-verzoek om een project te beëindigen
            $.ajax({
                type: 'POST',
                // Stel de URL in waar het verzoek naar toe moet
                url: 'save_schedule.php',
                // Specificeer de gegevens die naar de server worden gestuurd
                data: {
                    action: 'end_project',
                    // Gebruik de project_id, waarbij een standaardwaarde van 1 wordt gebruikt als $selectedProjectId nul is
                    project_id: <?php echo is_null($selectedProjectId) ? 1 : $selectedProjectId ?>,
                    // Haal de waarde op van het inputveld met ID 'title'
                    title: $('#title').val(),
                    // Haal de waarde op van het inputveld met ID 'description'
                    description: $('#description').val()
                },
                // uitgevoerd als het verzoek succesvol is
                success: function (response) {
                    // Verberg de groene balk
                    $('#green-bar').hide();
                    // Verwijder de sessievariabele "green_bar_visible"
                    <?php unset($_SESSION["green_bar_visible"]); ?>
                    // Herlaad de pagina
                    location.reload();
                },
                error: function (error) {
                    alert("Error ending project");
                }
            });
        }


        $(document).on("click", "#edit", function() {
            // Haal de waarde van het data-id attribuut op van het geklikte element en sla deze op in de variabele eventId
            var eventId = $(this).data("id");
            // Gebruik het eventId om het bijbehorende event op te halen uit de scheds array en sla het op in de variabele event
            var event = scheds[eventId];
            // Zet de waarde van het inputveld met ID "edit-event-id" op eventId
            $("#edit-event-id").val(eventId);
            $("#edit-title").val(event.title);
            $("#edit-description").val(event.description);

            // Definieer een functie om een datumstring te formatteren naar een ISO-string zonder seconden
            var formatDate = function(dateString) {
                return new Date(dateString).toISOString().slice(0, 16);
            };

            // Zet de waarde van het inputveld met ID "edit-start-time" op de geformatteerde starttijd van het event
            $("#edit-start-time").val(formatDate(event.start_time)); 
            $("#edit-end-time").val(formatDate(event.end_time)); 

            // Toon het modaal venster met ID "event-edit-modal"
            $("#event-edit-modal").modal("show");
        });

    </script>
    <script src="./js/script.js"></script>
<?php
require("footer.php"); // Laad de footer voor de pagina
?>
