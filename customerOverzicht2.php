<?php
require("start.php");
require "vendor/autoload.php";
use Ramsey\Uuid\Uuid;

// Check if the user is logged in as admin
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1) {
    header("Location: loginPage.php");
    exit();
}

require("pdo.php");

$res = []; // Initialize the $res array to prevent undefined index errors

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $id = $_GET["id"];
    try {
        $stmt = $pdo->prepare("SELECT c.name, c.phone_number, c.email, c.street, c.place, c.zip_code, c.house_number, c.province, c.country, c.VAT_number, p.id AS project_id, p.title, p.description, p.customer_id FROM customers c LEFT JOIN projects p ON c.id = p.customer_id WHERE c.id = :id");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query error.';
        die();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = Uuid::uuid4()->toString();
    
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $customer_id = $_GET["id"]; // Assigning customer_id from GET request

    $query_projects = "INSERT INTO projects (id, title, description, customer_id) VALUES (:id, :title, :description, :customer_id)";
    $values = [
        ':id' => $id,
        ':title' => $title, 
        ':description' => $description, 
        ':customer_id' => $customer_id
    ];
    
    try {
        $stmt_projects = $pdo->prepare($query_projects);
        $stmt_projects->execute($values);
    } catch (PDOException $e) {
        echo 'Query error: ' . $e->getMessage();
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
        </div>
        <div class="col-md-6">
            <div class="container mt-4">
                <div class="mb-4">
                    <h2>Projecten</h2>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="title">Titel</label>
                                        <input type="text" id="title" class="form-control" value="<?php echo $res["title"]; ?>">
                                    </div>
                                    <div class="col">
                                        <label for="description">Beschrijving</label>
                                        <input type="text" id="description" class="form-control" value="<?php echo $res["description"]; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <form method="post" action="">
            <div class="row mb-3">
                <div class="form-group mb-2">
                    <label for="projectTitle" class="control-label">Titel</label>
                    <input type="text" class="form-control form-control-sm rounded-0" name="title" id="projectTitle" required>
                </div>
                <div class="form-group mb-2">
                    <label for="projectDescription" class="control-label">Beschrijving</label>
                    <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="projectDescription" required></textarea>
                </div>
                <button class="btn btn-primary btn-sm rounded-0" type="submit" name="save_project"><i class="fa fa-save"></i> Save Project</button>
            </div>
        </form>
    </div>
</div>

<?php require("footer.php"); ?>
