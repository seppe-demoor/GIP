<?php
    // Laad het startbestand voor initiële instellingen en sessiebeheer
    require("start.php");

    // Controleer of de gebruiker is ingelogd als beheerder
    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        // Als de gebruiker geen beheerder is, stuur dan naar de inlogpagina
        header("Location: loginPage.php");
    }

    // Laad het PDO-bestand voor de databaseverbinding
    require("pdo.php");

    // Controleer of het verzoek een GET-verzoek is
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Definieer de SQL-query om de namen en IDs van alle klanten op te halen
        $query = "SELECT `name`,`id` FROM `customers`";
    }

    try {
        // Bereid de query voor met behulp van PDO
        $res = $pdo->prepare($query);
        // Voer de query uit
        $res->execute();
    } catch (PDOException $e) {
        // Afhandeling van eventuele fouten bij het uitvoeren van de query
        echo 'Query error.';
        die();
    }

    // Laad de header voor de pagina
    require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                    <!-- Link om een nieuwe klant toe te voegen -->
                    <a href="customerNew.php" class="bi bi-person-plus text-success fs-4"></a>
                </span>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Naam</th>
                        <th>Inspecteren</th>
                    </tr>
                    <!-- Controleert of de query resultaten heeft opgeleverd -->
                    <?php if($res->rowCount() != 0) : ?>
                        <!-- Haalt elke rij uit de queryresultaten op -->
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <!-- Toon de naam van de klant -->
                                <td><?php echo $row["name"]; ?></td>
                                <td>
                                    <!-- Link om de klantgegevens te inspecteren -->
                                    <a href="customerOverzicht2.php?id=<?php echo $row["id"];?>" class="bi bi-eye text-primary fs-4"></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else :?>
                        <!-- Bericht als er geen gegevens zijn -->
                        <tr><td colspan='6'>Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
            </div>
        </div>
    </div>
