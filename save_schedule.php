<?php
require_once('pdo.php');  // Inclusief het bestand 'pdo.php' om de databaseverbinding op te zetten met behulp van PDO

try {
    $save = false;  // Initialiseert de variabele $save op false

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        throw new Exception('Ongeldige aanvraagmethode.');  // Gooit een error als de request methode niet POST is
    }

    if (!isset($_POST["action"])) {
        throw new Exception('Actie niet ingesteld.');  // Gooit een error als de parameter "action" niet is ingesteld in POST
    }

    $action = $_POST["action"];  // Haalt de waarde van "action" op uit $_POST
    $now = new DateTime();  // Maakt een nieuw DateTime object aan om de huidige datum en tijd op te halen
    $currentTime = $now->format("Y-m-d H:i:s");  // Formateert de huidige datum en tijd als "Y-m-d H:i:s"

    switch ($action) {
        case "save_project":
            $start_date = $_POST["start_date"] ?? null;  // Haalt start_date op uit $_POST of zet deze op null als deze niet bestaat
            $end_date = $_POST["end_date"] ?? null;  // Haalt end_date op uit $_POST of zet deze op null als deze niet bestaat

            if (!$start_date || !$end_date) {
                throw new Exception('Start- en einddatum zijn verplicht.');  // Gooit een error als start_date of end_date ontbreekt
            }

            // SQL-query om start_time, end_time in te voegen en active op 1 te zetten in de 'work_time' tabel
            $sql = "INSERT INTO `work_time` (`start_time`, `end_time`, `active`) VALUES (?, ?, '1')";
            $stmt = $conn->prepare($sql);  // Bereidt de SQL-query voor
            $stmt->bind_param("ss", $start_date, $end_date);  // Koppelt parameters aan de SQL-query
            $save = $stmt->execute();  // Voert de SQL-query uit en wijst het resultaat toe aan $save

            if ($save) {
                $project_id = $conn->insert_id;  // Haalt het laatst ingevoegde ID op
                // SQL-query om de 'project_id' bij te werken in de 'work_time' tabel gebaseerd op het laatst ingevoegde ID
                $sql = "UPDATE `work_time` SET `project_id` = ? WHERE `id` = ?";
                $stmt = $conn->prepare($sql);  // Bereidt de SQL-query voor
                $stmt->bind_param("ii", $project_id, $project_id);  // Koppelt parameters aan de SQL-query
                $stmt->execute();  // Voert de SQL-query uit
            }
            break;

        case "start_project":
            $project_id = $_POST["project_id"] ?? null;  // Haalt project_id op uit $_POST of zet deze op null als deze niet bestaat

            if (!$project_id) {
                throw new Exception('Project ID is verplicht.');  // Gooit een error als project_id ontbreekt
            }

            // SQL-query om alle actieve projecten uit te schakelen in de 'work_time' tabel
            $conn->query("UPDATE `work_time` SET `active` = 0");

            // SQL-query om project_id, start_time in te voegen en active op 1 te zetten in de 'work_time' tabel
            $sql = "INSERT INTO `work_time` (`project_id`, `start_time`, `active`) VALUES (?, ?, '1')";
            $stmt = $conn->prepare($sql);  // Bereidt de SQL-query voor
            $stmt->bind_param("is", $project_id, $currentTime);  // Koppelt parameters aan de SQL-query
            $save = $stmt->execute();  // Voert de SQL-query uit en wijst het resultaat toe aan $save
            break;

        case "end_project":
            $project_id = $_POST["project_id"] ?? null;  // Haalt project_id op uit $_POST of zet deze op null als deze niet bestaat

            if (!$project_id) {
                throw new Exception('Project ID is verplicht.');  // Gooit een error als project_id ontbreekt
            }

            // SQL-query om end_time bij te werken en active op 0 te zetten in de 'work_time' tabel voor het actieve project
            $sql = "UPDATE `work_time` SET `end_time` = ?, `active` = 0 WHERE `active` = 1";
            $stmt = $conn->prepare($sql);  // Bereidt de SQL-query voor
            $stmt->bind_param("s", $currentTime);  // Koppelt parameters aan de SQL-query
            $save = $stmt->execute();  // Voert de SQL-query uit en wijst het resultaat toe aan $save
            break;

        case "edit_event":
            $event_id = $_POST["event_id"] ?? null;  // Haalt event_id op uit $_POST of zet deze op null als deze niet bestaat
            $title = $_POST["title"] ?? null;  // Haalt title op uit $_POST of zet deze op null als deze niet bestaat
            $description = $_POST["description"] ?? null;  // Haalt description op uit $_POST of zet deze op null als deze niet bestaat
            $start_time = $_POST["start_time"] ?? null;  // Haalt start_time op uit $_POST of zet deze op null als deze niet bestaat
            $end_time = $_POST["end_time"] ?? null;  // Haalt end_time op uit $_POST of zet deze op null als deze niet bestaat

            if (!$event_id || !$title || !$description || !$start_time || !$end_time) {
                throw new Exception('Alle velden zijn verplicht.');  // Gooit een error als een verplicht veld ontbreekt
            }

            // SQL-query om title, description, start_time, end_time bij te werken in de 'work_time' en 'projects' tabellen
            // waar work_time.id overeenkomt met event_id
            $sql = "UPDATE work_time 
                    INNER JOIN projects ON work_time.project_id = projects.id 
                    SET projects.title = ?, projects.description = ?, work_time.start_time = ?, work_time.end_time = ? 
                    WHERE work_time.id = ?";
            $stmt = $conn->prepare($sql);  // Bereidt de SQL-query voor
            $stmt->bind_param("ssssi", $title, $description, $start_time, $end_time, $event_id);  // Koppelt parameters aan de SQL-query
            $save = $stmt->execute();  // Voert de SQL-query uit en wijst het resultaat toe aan $save
            break;

        default:
            throw new Exception('Ongeldige actie.');  // Gooit een error voor een ongeldige actie
    }

    $conn->close();  // Sluit de databaseverbinding

    if ($save) {
        header("Location: homePage.php");  // Stuurt de gebruiker door naar homePage.php als $save waar is
        exit;
    } else {
        throw new Exception('Er is een fout opgetreden tijdens het opslaan van gegevens.');  // Gooit een error als er een fout optrad tijdens het opslaan van gegevens
    }
} catch (Exception $e) {
    echo $e->getMessage();  // Vangt alle erroren op die in de try-block worden gegooid en geeft het foutbericht weer
}
?>