<?php
require("start.php");

if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
    header("Location: loginPage.php");
}

require("pdo.php");

// Fetch projects for the dropdown menu
$projects_query = $pdo->query("SELECT * FROM `projects`");

// Check if a project is selected
$selectedProjectId = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["project_id"])) {
    $selectedProjectId = $_POST["project_id"];
    $query = "SELECT p.title, i.total_amount, i.invoice_date 
              FROM Invoices i, projects p 
              WHERE p.customer_id = i.customer_id 
              AND p.id = :project_id";
} else {
    $query = "SELECT p.title, i.total_amount, i.invoice_date 
              FROM Invoices i, projects p 
              WHERE p.customer_id = i.customer_id";
}

try {
    $res = $pdo->prepare($query);
    if ($selectedProjectId) {
        $res->bindParam(':project_id', $selectedProjectId, PDO::PARAM_INT);
    }
    $res->execute();
} catch (PDOException $e) {
    echo 'Query error.';
    die();
}

require("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecteer een Project</title>
    <style>
        .container {
            margin-top: 20px;
        }
        form {
            margin-bottom: 20px;
        }
        .btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <form method="post" action="">
            <div class="form-group">
                <label for="projectSelect">Selecteer een Project</label>
                <select class="form-control" name="project_id" id="projectSelect">
                    <?php foreach ($projects_query as $project) : ?>
                        <option value="<?= $project['id'] ?>" <?= $selectedProjectId == $project['id'] ? 'selected' : '' ?>>
                            <?= $project['title'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Selecteer Project</button>
            </div>
        </form>

        <div class="row">
            <div class="col-sm-12">
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Naam</th>
                        <th>Prijs</th>
                        <th>Aangemaakt op Datum</th>
                    </tr>
                    <?php if($res->rowCount() != 0) : ?>
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?php echo $row["title"]; ?></td>
                                <td>€<?php echo  $row["total_amount"]; ?></td>
                                <td><?php echo $row["invoice_date"]; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr><td colspan='3'>Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <?php require("footer.php"); ?>
</body>
</html>
