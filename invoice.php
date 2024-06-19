<?php
require("start.php");
require("pdo.php");
require("header.php");

try {
    // Fetch projects from the database
    $projects_query = $conn->query("SELECT * FROM `projects`");
    if (!$projects_query) {
        throw new Exception("Query error: " . $conn->error);
    }
} catch (Exception $e) {
    echo "<script>
        alert('{$e->getMessage()}');
        window.location.href = 'homePage.php';
    </script>";
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["select_project"])) {
    $selectedProjectId = $_POST["project_id"];
    header("Location: invoiceShow.php?project_id=$selectedProjectId");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecteer een Project</title>
    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        form {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .btn {
            display: block;
            width: 100%;
            margin-top: 10px;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <form action="invoiceShow.php" method="post" id="project-form">
            <div class="form-group mb-2">
                <div class="mb-2">
                    <label for="projectSelect" class="control-label">Selecteer een Project</label>
                    <select class="form-control form-control-sm rounded-0" name="project_id" id="projectSelect">
                        <?php foreach ($projects_query as $project) : ?>
                            <option value="<?= $project['id'] ?>"><?= $project['title'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm rounded-0" type="submit" name="select_project">
                    <i class="fa fa-check"></i> Selecteer Project
                </button>
            </div>
        </form>
        <?php require("footer.php"); ?>
    </div>
</body>
</html>
