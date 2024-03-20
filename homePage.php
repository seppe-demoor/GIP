<?php
require("start.php");
require("pdo.php");

// Set locale to Dutch
setlocale(LC_TIME, 'nl_NL');

// Als de gebruiker niet is ingelogd, doorsturen naar de inlogpagina

if (!isset($_SESSION["email"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");

$project_id = null;
// Logica voor het opslaan van een project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_project"])) {
    if (isset($_POST["title"]) && isset($_POST["description"])) {
        $title = $_POST["title"];
        $description = $_POST["description"];
        $project_id = $_POST["project_id"];

        // Voorbereiden en uitvoeren van de SQL-query voor het invoegen van een nieuw project
        $stmt = $conn->prepare("INSERT INTO `projects` (`title`, `description`) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);
        $stmt->execute();
    }
}

// Logica voor het beëindigen van een project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["end_project"])) {
    if (isset($_POST["title"]) && isset($_POST["description"])) {
        $title = $_POST["title"]; 
        $description = $_POST["description"];
        $project_id = $_POST["project_id"];

        // Voorbereiden en uitvoeren van de SQL-query voor het bijwerken van het eindtijd van een project
        $stmt = $conn->prepare("UPDATE `projects` SET `end_time` = NOW() WHERE `title` = ?");
        $stmt->bind_param("s", $title);
        $stmt->execute();
    }
}
// Ophalen van schema's en projecten uit de database
$schedules = $conn->query("SELECT l.id as work_id, p.id as project_id, p.title, p.description, l.start_time, l.end_time FROM projects p JOIN work_time l ON p.id = l.project_id;");
$projects = $conn->query("SELECT * FROM `projects`");
$sched_res = [];
$project_res = [];

// Verwerken van de queryresultaten en opslaan in arrays voor later gebruik
while ($row = $projects->fetch_assoc()) {
    $row['title'] = $row['title'];
    $row['description'] = $row['description'];
    $project_res[$row['id']] = $row;
}

while ($row = $schedules->fetch_assoc()) {
    if (!empty($row['start_time']) && !empty($row['end_time'])) {
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_time']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['end_time']));
    } else {
        $row['sdate'] = 'N/A';
        $row['edate'] = 'N/A';
    }
    $sched_res[$row['work_id']] = $row; 
}

// Hanteren van projectselectie
$selectedProject = null;
$selectedProjectId = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["select_project"])) {
    $selectedProjectId = $_POST["project_id"];
    $_SESSION["selected_project_id"] = $selectedProjectId;
}

// Laden van geselecteerde projectinformatie uit de sessie
if (isset($_SESSION["selected_project_id"])) {
    $selectedProjectId = $_SESSION["selected_project_id"];
    $selectedProject = $conn->query("SELECT * FROM `projects` WHERE `id` = $selectedProjectId")->fetch_assoc();
}

// Zichtbaarheid van de groene balk instellen in de sessie bij het starten van een project
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["start_project"])) {
    $_SESSION["green_bar_visible"] = true;
}

// Laden van de zichtbaarheid van de groene balk uit de sessie
$greenBarVisible = isset($_SESSION["green_bar_visible"]) ? $_SESSION["green_bar_visible"] : false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling</title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
    <script src="./js/jquery-3.6.0.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./fullcalendar/lib/main.min.js"></script>
    <style>
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }

        html,
        body {
            height: 100%;
            width: 100%;
        }

        .btn-info.text-light:hover,
        .btn-info.text-light:focus {
            background: #000;
        }

        table,
        tbody,
        td,
        tfoot,
        th,
        thead,
        tr {
            border-color: #ededed !important;
            border-style: solid;
            border-width: 1px !important;
        }
    </style>
</head>

<body class="bg-light">
    <div id="green-bar" class="alert alert-success text-center" style="display: none;">
        <strong>Je <?php echo isset($selectedProject) ? $selectedProject['title'] : 'geen project' ?> is nog aan het lopen</strong>
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

                <div class="cardt rounded-0 shadow">
                    <div class="card-header bg-gradient bg-primary text-light">
                        <h5 class="card-title">Tijden Opslaan</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form action="save_schedule.php" method="post" id="schedule-form">
                                <input type="hidden" name="id" value="">
                                <div class="form-group mb-2">
                                    <label for="title" class="control-label">Titel</label>
                                    <input type="text" class="form-control form-control-sm rounded-0" name="title" id="title" required value="<?= $selectedProject ? $selectedProject['title'] : '' ?>">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="description" class="control-label">Beschrijving</label>
                                    <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="description" required><?= $selectedProject ? $selectedProject['description'] : '' ?></textarea>
                                </div>

                                <input type="checkbox" id="showDateTime" onchange="toggleDateTime()"> selecteer een datum

                                <div id="dateTimeContainer" style="display: none;">
                                    <div class="form-group mb-2">
                                        <label for="start_time" class="control-label">Start</label>
                                        <input type="datetime-local" class="form-control form-control-sm rounded-0" name="start_time" id="start_time" required>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label for="end_time" class="control-label">eind</label>
                                        <input type="datetime-local" class="form-control form-control-sm rounded-0" name="end_time" id="end_time" required>
                                    </div>
                                </div>
                                <div id="buttonsContainer">
                        <div class="form-group mb-2 text-center">
                            <?php if ($selectedProject) : ?>
                                <button class="btn btn-success btn-sm rounded-0" type="button" onclick="startProject()">Start</button>
                                <button class="btn btn-danger btn-sm rounded-0" type="button" onclick="endProject()">Eind</button>
                            <?php endif; ?>
                        </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kaartvoettekst voor knoppen -->
                <div class="card-footer">
                    <div class="text-center">
                        <button id="saveButton" class="btn btn-primary btn-sm rounded-0" type="submit" form="schedule-form" style="display: none;"><i class="fa fa-save"></i> Save</button>
                        <button id="cancelButton" class="btn btn-default border btn-sm rounded-0" type="reset" form="schedule-form" style="display: none;"><i class="fa fa-reset"></i> Cancel</button>
                    </div>
                </div>
                
                <!-- Kaart voor het weergeven van projecten -->
                <div class="card rounded-0 shadow mt-3">
                    <div class="card-header bg-gradient bg-primary text-light mt-3">
                        <h5 class="card-title">Mijn Project</h5>
                    </div>
                    <div class="card-body">
                            <!-- Formulier voor het selecteren van een project -->
                            <form action="homePage.php" method="post" id="project-form">
                            <div class="form-group mb-2">
                                <label for="projectTitle" class="control-label">Titel</label>
                                <input type="text" class="form-control form-control-sm rounded-0" name="title" id="projectTitle" required>
                            </div>
                            <div class="form-group mb-2">
                                <label for="projectDescription" class="control-label">Beschrijving</label>
                                <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="projectDescription" required></textarea>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-0" type="submit" name="save_project"><i class="fa fa-save"></i> Save Project</button>
                            <hr>
                            </form>
                            <form action="homePage.php" method="post" id="project-form">
                            <div class="form-group mb-2">
                            <div class="mb-2">
                                <label for="projectSelect" class="control-label">Selecteer een Project</label>
                                <select class="form-control form-control-sm rounded-0" name="project_id" id="projectSelect">
                                    <?php foreach ($project_res as $projectId => $project) : ?>
                                        <option value="<?= $projectId ?>" <?php echo ($selectedProjectId == $projectId) ? "selected" : "" ?>><?= $project['title'] ?></option>
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
    <!-- Modaal venster voor kalenderdetails -->
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

    <!-- Bewerkingsvenster -->
    <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-edit-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-0">
                <div class="modal-header rounded-0">
                    <h5 class="modal-title">Evenement Bewerken</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body rounded-0">                    
                    <!-- Formulier voor het bewerken van een evenement -->
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
    if (isset($conn)) $conn->close();
    ?>
    <script>
        var scheds = <?php echo json_encode($sched_res); ?>;
        function toggleDateTime() {
                var dateTimeContainer = document.getElementById("dateTimeContainer");
                var buttonsContainer = document.getElementById("buttonsContainer");

                if (document.getElementById("showDateTime").checked) {
                    dateTimeContainer.style.display = "block";
                    buttonsContainer.style.display = "none";
                } else {
                    dateTimeContainer.style.display = "none";
                    buttonsContainer.style.display = "block";
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                var showDateTimeCheckbox = document.getElementById("showDateTime");
                var saveButton = document.getElementById("saveButton");
                var cancelButton = document.getElementById("cancelButton");

                showDateTimeCheckbox.addEventListener("change", function() {
                    if (this.checked) {
                        saveButton.style.display = "inline-block";
                        cancelButton.style.display = "inline-block";
                    } else {
                        saveButton.style.display = "none";
                        cancelButton.style.display = "none";
                    }
                });
            });


        function startProject() {
            $.ajax({
                type: 'POST',
                url: 'save_schedule.php',
                data: {
                    action: 'start_project',
                    project_id: <?php echo is_null($selectedProjectId) ? 1 : $selectedProjectId ?>,
                    title: $('#title').val(),
                    description: $('#description').val()
                },
                success: function (response) {
                    $('#green-bar').show();
                    <?php $_SESSION["green_bar_visible"] = true; ?>
                },
                error: function (error) {
                    alert("Error starting project");
                }
            });
        }

        function endProject() {
            $.ajax({
                type: 'POST',
                url: 'save_schedule.php',
                data: {
                    action: 'end_project',
                    project_id: <?php echo is_null($selectedProjectId) ? 1 : $selectedProjectId ?>,
                    title: $('#title').val(),
                    description: $('#description').val()
                },
                success: function (response) {
                    $('#green-bar').hide();
                    <?php unset($_SESSION["green_bar_visible"]); ?>
                    location.reload();
                },
                error: function (error) {
                    alert("Error ending project");
                }
            });
        }
        
        $(document).on("click", "#edit", function() {
    var eventId = $(this).data("id");
    var event = scheds[eventId];
    $("#edit-event-id").val(eventId);
    $("#edit-title").val(event.title);
    $("#edit-description").val(event.description);
    
    // Korte versie van de datum formattering inline
    var formatDate = function(dateString) {
        return new Date(dateString).toISOString().slice(0, 16);
    };
    
    // Zet de startdatum en einddatum om en plaats ze in de inputvelden
    $("#edit-start-time").val(formatDate(event.start_time)); 
    $("#edit-end-time").val(formatDate(event.end_time)); 
    
    $("#event-edit-modal").modal("show");
});


    </script>
    <script src="./js/script.js"></script>
</body>

</html>
