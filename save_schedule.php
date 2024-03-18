<?php
require_once('pdo.php');

$save = false;

// Check if $_POST["action"] is set
if (isset($_POST["action"])) {
    if ($_POST["action"] == "save_project") {
        extract($_POST);

        $sql = "INSERT INTO `projects` (`title`,`description`) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $title, $description);
        $save = $stmt->execute();
        $allday = isset($allday);
    } elseif ($_POST["action"] == "start_project") {
        $project_id = $_POST["project_id"];
        $now = new DateTime();
        $startTime = $now->format("Y-m-d H:i:s");
        
        $sql = "UPDATE `work_time` SET `active` = 0";
        $save = $conn->query($sql);

        $sql = "INSERT INTO `work_time` (`project_id`,`start_time`,`active`) VALUES ('$project_id','$startTime','1')";
        $save = $conn->query($sql);
    } elseif ($_POST["action"] == "end_project") {
        $project_id = $_POST["project_id"];
        $now = new DateTime();
        $endTime = $now->format("Y-m-d H:i:s");
        
        $sql = "UPDATE `work_time` SET `end_time` = '$endTime', `active` = 0 WHERE `active` = 1 ";
        $save = $conn->query($sql);
    }
}

if ($_POST["action"] == "edit_event") {
    $event_id = $_POST["event_id"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];

    $sql = "UPDATE work_time 
    INNER JOIN projects ON work_time.project_id = projects.id 
    SET projects.title=?, projects.description=?, work_time.start_time=?, work_time.end_time=? 
    WHERE work_time.id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $title, $description, $start_time, $end_time, $event_id);
$save = $stmt->execute();

}

$conn->close();

if ($save) {
    echo "Gegevens zijn succesvol opgeslagen.";
} else {
    echo "Er is een fout opgetreden tijdens het opslaan van gegevens.";
}
?>
