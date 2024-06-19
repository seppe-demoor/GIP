<?php
require("start.php");

// Check if the user is logged in as admin
if (!isset($_SESSION["admin"]) || $_SESSION["admin"] != 1) {
    header("Location: loginPage.php");
    exit();
}

require("pdo.php");

$res = []; // Initialize the $res array to prevent undefined index errors

try {
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
        $customer_id = $_GET["id"];
        $stmt = $pdo->prepare("SELECT c.name, c.phone_number, c.email, c.street, c.place, c.zip_code, c.house_number, c.province, c.country, c.VAT_number, p.id AS project_id, p.title, p.description, price_per_hour, p.customer_id FROM customers c LEFT JOIN projects p ON c.id = p.customer_id WHERE c.id = :id");
        $stmt->execute(['id' => $customer_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        // Haal de landnaam op op basis van het country ID
        $stmt_country = $pdo->prepare("SELECT name FROM countries WHERE id = :country");
        $stmt_country->execute(['country' => $res["country"]]);
        $country = $stmt_country->fetch(PDO::FETCH_ASSOC)["name"];
        
        $res["country"] = $country; // Voeg de landnaam toe aan $res array
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = trim($_POST["title"]);
        $description = trim($_POST["description"]);
        $price_per_hour = trim($_POST["price_per_hour"]);
        $customer_id = $_POST["id"]; // Assigning customer_id from GET request

        $query_projects = "INSERT INTO projects (title, description, price_per_hour, customer_id) VALUES (:title, :description, :price_per_hour, :customer_id)";
        $values = [
            ':title' => $title, 
            ':description' => $description,
            ':price_per_hour'=> $price_per_hour,
            ':customer_id' => $customer_id
        ];

        $stmt_projects = $pdo->prepare($query_projects);
        $stmt_projects->execute($values);

        $stmt = $pdo->prepare("SELECT c.name, c.phone_number, c.email, c.street, c.place, c.zip_code, c.house_number, c.province, c.country, c.VAT_number, p.id AS project_id, p.title, p.description, price_per_hour, p.customer_id FROM customers c LEFT JOIN projects p ON c.id = p.customer_id WHERE c.id = :id");
        $stmt->execute(['id' => $customer_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo 'Query error: ' . $e->getMessage();
    die();
}

require("header.php");
?>

<div class="container mt-4">
    <a href="customerOverzicht.php" class="btn btn-secondary mb-4"><i class="fa fa-arrow-left"></i> Terug</a>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="name">Naam</label>
                            <input type="text" id="name" class="form-control" value="<?= htmlspecialchars($res["name"]); ?>">
                        </div>
                        <div class="col">
                            <label for="phone_number">Telefoon nummer</label>
                            <input type="text" id="phone_number" class="form-control" value="<?= htmlspecialchars($res["phone_number"]); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="email">Email</label>
                            <input type="text" id="email" class="form-control" value="<?= htmlspecialchars($res["email"]); ?>">
                        </div>
                        <div class="col">
                            <label for="street">Straat</label>
                            <input type="text" id="street" class="form-control" value="<?= htmlspecialchars($res["street"]); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="place">Stad</label>
                            <input type="text" id="place" class="form-control" value="<?= htmlspecialchars($res["place"]); ?>">
                        </div>
                        <div class="col">
                            <label for="zip_code">Postcode</label>
                            <input type="text" id="zip_code" class="form-control" value="<?= htmlspecialchars($res["zip_code"]); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="house_number">Huisnummer</label>
                            <input type="text" id="house_number" class="form-control" value="<?= htmlspecialchars($res["house_number"]); ?>">
                        </div>
                        <div class="col">
                            <label for="province">Provincie</label>
                            <input type="text" id="province" class="form-control" value="<?= htmlspecialchars($res["province"]); ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label for="country">Land</label>
                            <input type="text" id="country" class="form-control" value="<?= htmlspecialchars($res["country"]); ?>">
                        </div>
                        <div class="col">
                            <label for="VAT_number">BTW nummer</label>
                            <input type="text" id="VAT_number" class="form-control" value="<?= htmlspecialchars($res["VAT_number"]); ?>">
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
                <?php
                try {
                    $stmt = $pdo->prepare("SELECT p.id AS project_id, p.title, p.description, p.price_per_hour FROM projects p WHERE p.customer_id = :customer_id");
                    $stmt->execute(['customer_id' => $customer_id]);
                    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    echo 'Query error: ' . $e->getMessage();
                    die();
                }

                foreach ($projects as $project) {
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="title">Titel</label>
                                        <input type="text" id="title" class="form-control" value="<?= htmlspecialchars($project["title"]); ?>">
                                    </div>
                                    <div class="col">
                                        <label for="description">Beschrijving</label>
                                        <input type="text" id="description" class="form-control" value="<?= htmlspecialchars($project["description"]); ?>">
                                    </div>
                                    <div class="col">
                                        <label for="price_per_hour">Prijs per uur</label>
                                        <input type="text" id="price_per_hour" class="form-control" value="<?= htmlspecialchars($project["price_per_hour"]); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                }
                ?>
                <form method="post" action="customerOverzicht2.php">
                    <div class="row mb-3">
                        <div class="form-group mb-2">
                            <label for="projectTitle" class="control-label">Titel</label>
                            <input type="text" class="form-control form-control-sm rounded-0" name="title" id="projectTitle" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="projectDescription" class="control-label">Beschrijving</label>
                            <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="projectDescription" required></textarea>
                        </div>
                        <div class="form-group mb-2">
                            <label for="projectprice_per_hour" class="control-label">Prijs per uur</label>
                            <input type="text" class="form-control form-control-sm rounded-0" name="price_per_hour" id="projectprice_per_hour" required>
                        </div>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($customer_id); ?>">
                        <button class="btn btn-primary btn-sm rounded-0" type="submit" name="save_project"><i class="fa fa-save"></i> Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require("footer.php"); ?>
