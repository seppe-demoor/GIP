<?php
    require("start.php");

    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        header("Location: loginPage.php");
    }

    require("pdo.php");

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $query = "SELECT `name`,`id` FROM `customers`";
    }

    try
    {
        $res = $pdo->prepare($query);
        $res->execute();
    }
    catch (PDOException $e)
    {
        echo 'Query error.';
        die();
    }

    require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <span class="float-end">
                        <a href="customerNew.php" class="bi bi-person-plus text-succes fs-4"></a>
                </span>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Naam</th>
                        <th>inspecteren</th>
                    </tr>
                    <?php if($res->rowCount() != 0) : ?>
                        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                            <tr>
                                <td><?php echo $row["name"]; ?></td>
                                <td>
                                    <a href="customerOverzicht2.php?id=<?php echo$row["id"];?>"class="bi bi-eye text-succes fs-4"></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else :?>
                        <tr><td colspan='6'>Geen gegevens gevonden</td></tr>
                    <?php endif; ?>
            </div>
        </div>
    </div>