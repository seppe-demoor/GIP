<?php
require_once('pdo.php');

try {
    $save = false;

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Ongeldige aanvraagmethode.');
    }

    if (!isset($_POST["action"])) {
        throw new Exception('Actie niet ingesteld.');
    }

    $action = $_POST["action"];
    $now = new DateTime();
    $currentTime = $now->format("Y-m-d H:i:s");

    switch ($action) {
        case "save_project":
            $start_date = $_POST["start_date"] ?? null;
            $end_date = $_POST["end_date"] ?? null;

            if (!$start_date || !$end_date) {
                throw new Exception('Start- en einddatum zijn verplicht.');
            }

            $sql = "INSERT INTO `work_time` (`start_time`, `end_time`, `active`) VALUES (?, ?, '1')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $start_date, $end_date);
            $save = $stmt->execute();

            if ($save) {
                $project_id = $conn->insert_id;
                $sql = "UPDATE `work_time` SET `project_id` = ? WHERE `id` = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $project_id, $project_id);
                $stmt->execute();
            }
            break;

        case "start_project":
            $project_id = $_POST["project_id"] ?? null;

            if (!$project_id) {
                throw new Exception('Project ID is verplicht.');
            }

            $conn->query("UPDATE `work_time` SET `active` = 0");

            $sql = "INSERT INTO `work_time` (`project_id`, `start_time`, `active`) VALUES (?, ?, '1')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $project_id, $currentTime);
            $save = $stmt->execute();
            break;

        case "end_project":
            $project_id = $_POST["project_id"] ?? null;

            if (!$project_id) {
                throw new Exception('Project ID is verplicht.');
            }

            $sql = "UPDATE `work_time` SET `end_time` = ?, `active` = 0 WHERE `active` = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $currentTime);
            $save = $stmt->execute();
            break;

        case "edit_event":
            $event_id = $_POST["event_id"] ?? null;
            $title = $_POST["title"] ?? null;
            $description = $_POST["description"] ?? null;
            $start_time = $_POST["start_time"] ?? null;
            $end_time = $_POST["end_time"] ?? null;

            if (!$event_id || !$title || !$description || !$start_time || !$end_time) {
                throw new Exception('Alle velden zijn verplicht.');
            }

            $sql = "UPDATE work_time 
                    INNER JOIN projects ON work_time.project_id = projects.id 
                    SET projects.title = ?, projects.description = ?, work_time.start_time = ?, work_time.end_time = ? 
                    WHERE work_time.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $description, $start_time, $end_time, $event_id);
            $save = $stmt->execute();
            break;

        default:
            throw new Exception('Ongeldige actie.');
    }

    $conn->close();

    if ($save) {
        header("Location: homePage.php");
        exit;
    } else {
        throw new Exception('Er is een fout opgetreden tijdens het opslaan van gegevens.');
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
