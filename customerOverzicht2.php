<?php
require("start.php");

// Check if the user is logged in as admin
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1) {
    header("Location: loginPage.php");
    exit(); // Terminate script to prevent further execution
}

require("pdo.php");

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $query = "SELECT `name`, `phone_number`, `email`, `street`, `place`, `zip_code`, `house_number`, `province`, `country`, `VAT_number` FROM `customers` WHERE id = :id";
   try
   {
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
   }
   catch (PDOException $e)
   {
       echo 'Query error.';
       die();
   }
}

require("header.php");
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="name">Naam</label>
                            <input type="text" id="name" class="form-control" value="<?php echo $res["name"]; ?>">
                        </div>
                        <div class="col">
                            <label for="phone_number">Telefoon nummer</label>
                            <input type="text" id="phone_number" class="form-control" value="<?php echo $res["phone_number"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email">Email</label>
                            <input type="text" id="email" class="form-control" value="<?php echo $res["email"]; ?>">
                        </div>
                        <div class="col">
                            <label for="street">Straat</label>
                            <input type="text" id="street" class="form-control" value="<?php echo $res["street"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="place">Stad</label>
                            <input type="text" id="place" class="form-control" value="<?php echo $res["place"]; ?>">
                        </div>
                        <div class="col">
                            <label for="zip_code">Postcode</label>
                            <input type="text" id="zip_code" class="form-control" value="<?php echo $res["zip_code"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="house_number">Huisnummer</label>
                            <input type="text" id="house_number" class="form-control" value="<?php echo $res["house_number"]; ?>">
                        </div>
                        <div class="col">
                            <label for="province">Provincie</label>
                            <input type="text" id="province" class="form-control" value="<?php echo $res["province"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="country">Land</label>
                            <input type="text" id="country" class="form-control" value="<?php echo $res["country"]; ?>">
                        </div>
                        <div class="col">
                            <label for="VAT_number">BTW nummer</label>
                            <input type="text" id="VAT_number" class="form-control" value="<?php echo $res["VAT_number"]; ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="name">Naam</label>
                            <input type="text" id="name" class="form-control" value="<?php echo $res["name"]; ?>">
                        </div>
                        <div class="col">
                            <label for="phone_number">Telefoon nummer</label>
                            <input type="text" id="phone_number" class="form-control" value="<?php echo $res["phone_number"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email">Email</label>
                            <input type="text" id="email" class="form-control" value="<?php echo $res["email"]; ?>">
                        </div>
                        <div class="col">
                            <label for="street">Straat</label>
                            <input type="text" id="street" class="form-control" value="<?php echo $res["street"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="place">Stad</label>
                            <input type="text" id="place" class="form-control" value="<?php echo $res["place"]; ?>">
                        </div>
                        <div class="col">
                            <label for="zip_code">Postcode</label>
                            <input type="text" id="zip_code" class="form-control" value="<?php echo $res["zip_code"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="house_number">Huisnummer</label>
                            <input type="text" id="house_number" class="form-control" value="<?php echo $res["house_number"]; ?>">
                        </div>
                        <div class="col">
                            <label for="province">Provincie</label>
                            <input type="text" id="province" class="form-control" value="<?php echo $res["province"]; ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="country">Land</label>
                            <input type="text" id="country" class="form-control" value="<?php echo $res["country"]; ?>">
                        </div>
                        <div class="col">
                            <label for="VAT_number">BTW nummer</label>
                            <input type="text" id="VAT_number" class="form-control" value="<?php echo $res["VAT_number"]; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require("footer.php"); ?>
