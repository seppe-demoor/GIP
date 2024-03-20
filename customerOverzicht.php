<!DOCTYPE html>
<?php
    require("start.php");

    if (!isset($_SESSION["admin"]) && $_SESSION["admin"] == 0) {
        header("Location: loginPage.php");
    }

    require("pdo.php");

    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['deleted'])) {
        $query = "SELECT `name`, `phone_number`, `email`, `street`, `place`, `zip_code`, `house_number`, `province`, `country`, `VAT_number`, `projects` FROM `customers`";
        $deleted = true;
    } else {
        $query = "SELECT `name`, `phone_number`, `email`, `street`, `place`, `zip_code`, `house_number`, `province`, `country`, `VAT_number`, `projects` FROM `customers`";
        $deleted = false;
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

<div class="container mt-4">
    <div class="row">
        <?php while($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col">
                                <th>naam</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["name"]; ?>">
                            </div>
                            <div class="col">
                                <th>Telefoon nummer</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["phone_number"]; ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <th>Email</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["email"]; ?>">
                            </div>
                            <div class="col">
                                <th>Straat</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["street"]; ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <th>Stad</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["place"]; ?>">
                            </div>
                            <div class="col">
                                <th>Postcode</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["zip_code"]; ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <th>huisnummer</th>
                                <input type="text" class="form-control" placeholder=<?php echo $row["house_number"]; ?>>
                            </div>
                            <div class="col">
                                <th>Provincie</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["province"]; ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <th>Land</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["country"]; ?>">
                            </div>
                            <div class="col">
                                <th>BTW nummer</th>
                                <input type="text" class="form-control" placeholder="<?php echo $row["VAT_number"]; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>