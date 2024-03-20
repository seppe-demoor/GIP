<?php
// Inclusie van het PDO-bestand
require_once('pdo.php');

// Variabele om op te slaan of de actie is voltooid
$save = false;

// Controleer of $_POST["action"] is ingesteld
if (isset($_POST["action"])) {
    // Actie om een nieuw project op te slaan
    if ($_POST["action"] == "save_project") {
        // Extractie van $_POST-variabelen
        extract($_POST);

        // SQL-query om het nieuwe project in te voegen
        $sql = "INSERT INTO `projects` (`title`,`description`) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $title, $description);
        $save = $stmt->execute();
    } 
    // Actie om een project te starten
    elseif ($_POST["action"] == "start_project") {
        // Extraheren van de project-ID en huidige tijd
        $project_id = $_POST["project_id"];
        $now = new DateTime();
        $startTime = $now->format("Y-m-d H:i:s");
        
        // SQL-query om het werk voor dit project te starten
        $sql = "UPDATE `work_time` SET `active` = 0";
        $save = $conn->query($sql);

        $sql = "INSERT INTO `work_time` (`project_id`,`start_time`,`active`) VALUES ('$project_id','$startTime','1')";
        $save = $conn->query($sql);
    } 
    // Actie om een project te beëindigen
    elseif ($_POST["action"] == "end_project") {
        // Extraheren van de project-ID en huidige tijd
        $project_id = $_POST["project_id"];
        $now = new DateTime();
        $endTime = $now->format("Y-m-d H:i:s");
        
        // SQL-query om het werk voor dit project te beëindigen
        $sql = "UPDATE `work_time` SET `end_time` = '$endTime', `active` = 0 WHERE `active` = 1 ";
        $save = $conn->query($sql);
    }
}

// Actie om een gebeurtenis te bewerken
if ($_POST["action"] == "edit_event") {
    // Extraheren van gegevens vanuit $_POST
    $event_id = $_POST["event_id"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];

    // SQL-query om de gebeurtenis bij te werken
    $sql = "UPDATE work_time 
            INNER JOIN projects ON work_time.project_id = projects.id 
            SET projects.title=?, projects.description=?, work_time.start_time=?, work_time.end_time=? 
            WHERE work_time.id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $description, $start_time, $end_time, $event_id);
    $save = $stmt->execute();
}

// Sluiten van de databaseverbinding
$conn->close();

// Bericht weergeven op basis van het resultaat van de actie
if ($save) {
    echo "Gegevens zijn succesvol opgeslagen.";
} else {
    echo "Er is een fout opgetreden tijdens het opslaan van gegevens.";
}
?>
