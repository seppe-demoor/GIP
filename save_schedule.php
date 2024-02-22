<?php 
require_once('pdo.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script> alert('Error: No data to save.'); location.replace('./') </script>";
    exit;
}

$save = false;

// Check if $_POST["action"] is set
if (isset($_POST["action"])) {
    if ($_POST["action"] == "save_schedule") {
        extract($_POST);
        $allday = isset($allday);

        if(empty($id)){
            $sql = "INSERT INTO `schedule_list` (`title`,`description`) VALUES ('$title','$description')";
        } else {
            $sql = "UPDATE `schedule_list` SET `title` = '{$title}', `description` = '{$description}' WHERE `id` = '{$id}'";
        }
        
        $save = $conn->query($sql);
        
        if($save){
            echo "<script> alert('Schedule Successfully Saved.'); location.replace('./') </script>";
        } else {
            echo "<pre>";
            echo "An Error occured.<br>";
            echo "Error: ".$conn->error."<br>";
            echo "SQL: ".$sql."<br>";
            echo "</pre>";
        }
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
} else {
    // If $_POST["action"] is not set, handle the situation accordingly
    echo "<script> alert('Error: Action parameter not set.'); location.replace('./') </script>";
}

$conn->close();
?>

