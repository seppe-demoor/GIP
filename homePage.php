<?php
require("start.php");
require("pdo.php");

// Set locale to Dutch
setlocale(LC_TIME, 'nl_NL');

if (!isset($_SESSION["username"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");

// Project Save Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["save_project"])) {
    if (isset($_POST["title"]) && isset($_POST["description"])) {
        $title = $_POST["title"];
        $description = $_POST["description"];

        // Placeholder: Replace with your actual database insert logic
        $stmt = $conn->prepare("INSERT INTO `projects` (`title`, `description`) VALUES (?, ?)");
        $stmt->bind_param("ss", $title, $description);
        $stmt->execute();
    }
}

$schedules = $conn->query("SELECT * FROM `schedule_list`");
$projects = $conn->query("SELECT * FROM `projects`");
$sched_res = [];
$project_res = [];

while ($row = $projects->fetch_assoc()) {
    $row['title'] = $row['title'];
    $row['description'] = $row['description'];
    $project_res[$row['id']] = $row;
}

while ($row = $schedules->fetch_assoc()) {
    $row['sdate'] = date("F d, Y h:i A", strtotime($row['start_datetime']));
    $row['edate'] = date("F d, Y h:i A", strtotime($row['end_datetime']));
    $sched_res[$row['id']] = $row;
}

// Handle Project Selection
$selectedProject = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["select_project"])) {
    $projectId = $_POST["project_id"];

    // Placeholder: Replace with your logic to fetch project details
    $selectedProject = $conn->query("SELECT * FROM `projects` WHERE `id` = $projectId")->fetch_assoc();
}

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
                        <h5 class="card-title">Schedule Form</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <form action="save_schedule.php" method="post" id="schedule-form">
                                <input type="hidden" name="id" value="">
                                <div class="form-group mb-2">
                                    <label for="title" class="control-label">Titel</label>
                                    <input type="text" class="form-control form-control-sm rounded-0" name="title"
                                        id="title" required value="<?= $selectedProject ? $selectedProject['title'] : '' ?>">
                                </div>
                                <div class="form-group mb-2">
                                    <label for="description" class="control-label">Beschrijving</label>
                                    <textarea rows="3" class="form-control form-control-sm rounded-0"
                                        name="description" id="description"
                                        required><?= $selectedProject ? $selectedProject['description'] : '' ?></textarea>
                                </div>
                                <div class="form-group mb-2">
                                    <?php if ($selectedProject) : ?>
                                        <button class="btn btn-success btn-sm rounded-0" type="button"
                                            onclick="startProject()">Start</button>
                                        <button class="btn btn-danger btn-sm rounded-0" type="button"
                                            onclick="endProject()">End</button>
                                    <?php else : ?>
                                        <label for="start_datetime" class="control-label">Start Datum</label>
                                        <input type="datetime-local" class="form-control form-control-sm rounded-0"
                                            name="start_datetime" id="start_datetime" required>
                                        <label for="end_datetime" class="control-label">Eind Datum</label>
                                        <input type="datetime-local" class="form-control form-control-sm rounded-0"
                                            name="end_datetime" id="end_datetime" required>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="text-center">
                            <button class="btn btn-primary btn-sm rounded-0" type="submit"
                                form="schedule-form"><i class="fa fa-save"></i> Save</button>
                            <button class="btn btn-default border btn-sm rounded-0" type="reset"
                                form="schedule-form"><i class="fa fa-reset"></i> Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Project List and Select Form -->
                <div class="card rounded-0 shadow mt-3">
                    <div class="card-header bg-gradient bg-primary text-light mt-3">
                        <h5 class="card-title">Create or Select Project</h5>
                    </div>
                    <div class="card-body">
                        <form action="homepage.php" method="post" id="project-form">
                            <div class="form-group mb-2">
                                <label for="projectTitle" class="control-label">Title</label>
                                <input type="text" class="form-control form-control-sm rounded-0" name="title"
                                    id="projectTitle" >
                            </div>
                            <div class="form-group mb-2">
                                <label for="projectDescription" class="control-label">Description</label>
                                <textarea rows="3" class="form-control form-control-sm rounded-0"
                                    name="description" id="projectDescription" ></textarea>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-0" type="submit"
                                name="save_project"><i class="fa fa-save"></i> Save Project</button>
                            <hr>
                            <div class="mb-2">
                                <label for="projectSelect" class="control-label">Select a Project</label>
                                <select class="form-control form-control-sm rounded-0" name="project_id"
                                    id="projectSelect">
                                    <?php
                                    foreach ($project_res as $project) {
                                        echo "<option value='{$project['id']}'>{$project['title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-sm rounded-0" type="submit"
                                name="select_project"><i class="fa fa-check"></i> Select Project</button>
                        </form>
                    </div>
                </div>
                <!-- End of Project List and Select Form -->

            </div>
        </div>
    </div>

    <!-- ... (your existing modal code) ... -->
    <?php
    if (isset($conn)) $conn->close();
    ?>
    <script>
        var scheds = <?= json_encode($sched_res) ?>;

        function startProject() {
            // Placeholder: Replace with your logic to handle project start
            alert("Project Started");
        }

        function endProject() {
            // Placeholder: Replace with your logic to handle project end
            alert("Project Ended");
        }
    </script>
    <script src="./js/script.js"></script>
</body>

</html>
