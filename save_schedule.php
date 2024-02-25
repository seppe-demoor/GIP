<?php 
require_once('pdo.php');


$save = false;

// Check if $_POST["action"] is set
if (isset($_POST["action"])) {
    if ($_POST["action"] == "save_schedule") {
        extract($_POST);
        $allday = isset($allday);
        
        $save = $conn->query($sql);
        
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

$conn->close();
?>

