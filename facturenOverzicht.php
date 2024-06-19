<?php
// Laad het startbestand voor initiële instellingen en sessiebeheer
require("start.php");

// Controleer of de gebruiker is ingelogd als beheerder
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] == 0) {
    // Als de gebruiker geen beheerder is, stuur dan naar de inlogpagina en stop de uitvoering
    header("Location: loginPage.php");
    exit();
}

// Laad het PDO-bestand voor de databaseverbinding
require("pdo.php");

try {
    // Haal projecten op voor het dropdown menu
    $projects_query = $pdo->query("SELECT * FROM `projects`");

    // Controleer of een project is geselecteerd
    $selectedProjectId = null;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["project_id"])) {
        // Als 'all' is geselecteerd, zet $selectedProjectId op null, anders op de geselecteerde waarde
        $selectedProjectId = $_POST["project_id"] == 'all' ? null : $_POST["project_id"];
    }

    // Bereid de hoofdquery voor om facturen en bijbehorende projectinformatie op te halen
    $query = "SELECT p.title, i.total_amount, i.invoice_date, i.id as invoice_id, pdf.pdf_data 
              FROM Invoices i 
              JOIN projects p ON p.customer_id = i.customer_id 
              LEFT JOIN pdf_invoices pdf ON i.id = pdf.invoice_id";
    if ($selectedProjectId) {
        // Voeg een WHERE clausule toe als een specifiek project is geselecteerd
        $query .= " WHERE p.id = :project_id";
    }

    // Bereid de query voor met behulp van PDO
    $res = $pdo->prepare($query);
    if ($selectedProjectId) {
        // Bind de waarde van $selectedProjectId aan de parameter :project_id in de query
        $res->bindParam(':project_id', $selectedProjectId, PDO::PARAM_INT);
    }
    // Voer de query uit
    $res->execute();
} catch (PDOException $e) {
    // Afhandeling van eventuele fouten bij het uitvoeren van de query
    echo 'Query error: ' . $e->getMessage();
    die();
}

// Laad de header voor de pagina
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
            <!-- Formulier voor het selecteren van een project -->
            <form method="post" action="">
                <div class="form-group">
                    <!-- Label voor het dropdown menu -->
                    <label for="projectSelect">Selecteer een Project</label>
                    <!-- Dropdown menu om een project te selecteren -->
                    <select class="form-control" name="project_id" id="projectSelect">
                        <!-- Optie om alle projecten te tonen -->
                        <option value="all" <?= $selectedProjectId === null ? 'selected' : '' ?>>Alle Projecten</option>
                        <!-- Loop door alle projecten en voeg een optie toe voor elk project -->
                        <?php foreach ($projects_query as $project) : ?>
                            <!-- Optie voor een specifiek project -->
                            <option value="<?= $project['id'] ?>" <?= $selectedProjectId == $project['id'] ? 'selected' : '' ?>>
                                <!-- Toon de titel van het project, veilig voor speciale tekens -->
                                <?= htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <!-- Knop om het formulier in te dienen -->
                    <button type="submit" class="btn btn-primary">Selecteer Project</button>
                </div>
            </form>

            <div class="row">
                <div class="col-sm-12">
                    <!-- Tabel om de resultaten weer te geven -->
                    <table class="table table-hover table-striped">
                        <tr>
                            <th>Naam</th>
                            <th>Prijs</th>
                            <th>Aangemaakt op Datum</th>
                            <th>PDF</th>
                        </tr>
                        <!-- Controleert of de query resultaten heeft opgeleverd -->
                        <?php if($res->rowCount() != 0) : ?>
                            <!-- Haalt elke rij uit de queryresultaten op -->
                            <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                                <tr>
                                    <!-- Toon de titel van het project -->
                                    <td><?= htmlspecialchars($row["title"], ENT_QUOTES, 'UTF-8') ?></td>
                                    <!-- Toon het totale bedrag van de factuur -->
                                    <td>€<?= htmlspecialchars($row["total_amount"], ENT_QUOTES, 'UTF-8') ?></td>
                                    <!-- Toon de datum van de factuur -->
                                    <td><?= htmlspecialchars($row["invoice_date"], ENT_QUOTES, 'UTF-8') ?></td>
                                    <!-- Toon de link naar de PDF of een bericht als er geen PDF is -->
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
                            <!-- Bericht als er geen gegevens zijn -->
                            <tr><td colspan='4'>Geen gegevens gevonden</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

    <?php require("footer.php"); ?>
</body>
</html>
