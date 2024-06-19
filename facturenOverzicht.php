<?php
require("start.php");

if (!isset($_SESSION["admin"]) || $_SESSION["admin"] == 0) {
    header("Location: loginPage.php");
    exit();
}

require("pdo.php");

try {
    // Fetch projects for the dropdown menu
    $projects_query = $pdo->query("SELECT * FROM `projects`");

    // Check if a project is selected
    $selectedProjectId = null;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["project_id"])) {
        $selectedProjectId = $_POST["project_id"] == 'all' ? null : $_POST["project_id"];
    }

    // Prepare the main query
    $query = "SELECT p.title, i.total_amount, i.invoice_date, i.id as invoice_id, pdf.pdf_data 
              FROM Invoices i 
              JOIN projects p ON p.customer_id = i.customer_id 
              LEFT JOIN pdf_invoices pdf ON i.id = pdf.invoice_id";
    if ($selectedProjectId) {
        $query .= " WHERE p.id = :project_id";
    }

    $res = $pdo->prepare($query);
    if ($selectedProjectId) {
        $res->bindParam(':project_id', $selectedProjectId, PDO::PARAM_INT);
    }
    $res->execute();
} catch (PDOException $e) {
    echo 'Query error: ' . $e->getMessage();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
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
                    <option value="all" <?= $selectedProjectId === null ? 'selected' : '' ?>>Alle Projecten</option>
                    <?php foreach ($projects_query as $project) : ?>
                        <option value="<?= $project['id'] ?>" <?= $selectedProjectId == $project['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>
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
                        <th>PDF</th>
                    </tr>
                    <?php if($res->rowCount() != 0) : ?>
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>€<?= htmlspecialchars($row["total_amount"], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row["invoice_date"], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($row["pdf_data"]) : ?>
                                        <a href="view_pdf.php?invoice_id=<?= $row['invoice_id'] ?>" target="_blank">Bekijk PDF</a>
                                    <?php else : ?>
                                        Geen PDF
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr><td colspan='4'>Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <?php require("footer.php"); ?>
</body>
</html>
