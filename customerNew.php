<?php
require "vendor/autoload.php";
use Ramsey\Uuid\Uuid;

require("header.php");
require("start.php");
require("pdo.php");

// Controleren of de gebruiker is aangemeld
if (!isset($_SESSION['email'])) {
    // Gebruiker is nog niet aangemeld, doorsturen naar de inlogpagina
    header("Location: loginPage.php");
    exit;
}

// Query voor het ophalen van landen
$query_countries = "SELECT `id`,`name` FROM `countries`";

try {
    // Voorbereiden van de query en uitvoeren
    $res_countries = $pdo->query($query_countries);
} catch (PDOException $e) {
    // Foutafhandeling bij een fout in de query
    echo "Query error:" . $e->getMessage();
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = Uuid::uuid4();
    
    // Ontvangen van de formuliergegevens
    $name = trim($_POST["name"]);
    $phone_number = trim($_POST["phone_number"]);
    $email = trim($_POST["email"]);
    $street = trim($_POST["street"]);
    $place = trim($_POST["place"]);
    $zip_code = trim($_POST["zip_code"]);
    $house_number = trim($_POST["house_number"]);
    $province = trim($_POST["province"]);
    $country = trim($_POST["country"]); // Het ID van het geselecteerde land
    $VAT_number = trim($_POST["VAT_number"]);
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    // Query voor het invoegen van de nieuwe klant in de database
    $query_customer = "INSERT INTO `customers` (id,name, phone_number, email, street, place, zip_code, house_number, province, country, VAT_number)
              VALUES (:id,:name, :phone_number, :email, :street, :place, :zip_code, :house_number, :province, :country, :VAT_number)";
    
    // Array met de te binden waarden voor de query
    $values = [
        ':id' => $id,
        ':name' => $name, 
        ':phone_number' => $phone_number, 
        ':email' => $email, 
        ':street' => $street, 
        ':place' => $place,
        ':zip_code' => $zip_code, 
        ':house_number' => $house_number, 
        ':province' => $province, 
        ':country' => $country, // Hier wordt het ID van het geselecteerde land gebruikt
        ':VAT_number' => $VAT_number
        
    ];
    
    try {
        // Voorbereiden van de query en uitvoeren
        $res_customer = $pdo->prepare($query_customer);
        $res_customer->execute($values);

        // Insert the project after inserting the customer
        $customer_id = $pdo->lastInsertId(); // Get the last inserted customer ID
        
        // Query for inserting the project
        $query_project = "INSERT INTO `projects` (`id`, `title`, `description`) VALUES (:id, :title, :description)";
        
        // Values for the project insert query
        $project_values = [
            ':id' => $customer_id,
            ':title' => $title,
            ':description' => $description
        ];

        // Prepare and execute the project insert query
        $res_project = $pdo->prepare($query_project);
        $res_project->execute($project_values);
        
        // Redirect to customer overview after successful insertion
        header("Location: customerOverzicht.php");
        exit;
    } catch (PDOException $e) {
        // Foutafhandeling bij een fout in de query
        echo "Query error:" . $e->getMessage();
        die();
    }
}

// Inclusief het headerbestand voor de opmaak van de pagina
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Formulier voor het invoeren van de gegevens voor een nieuwe klant -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Naam</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Telefoon nummer</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="street" class="form-label">Straat</label>
                        <input type="text" class="form-control" id="street" name="street" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="place" class="form-label">Plaats</label>
                        <input type="text" class="form-control" id="place" name="place" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="zip_code" class="form-label">Postcode</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="house_number" class="form-label">Huis nummer</label>
                        <input type="text" class="form-control" id="house_number" name="house_number" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="province" class="form-label">Provincie</label>
                        <input type="text" class="form-control" id="province" name="province" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="country" class="form-label">Land</label>
                        <select class="form-control" id="country" name="country" required>
                            <option value="">Selecteer een land</option>
                            <?php
                            if ($res_countries->rowCount() > 0) {
                                while ($row = $res_countries->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>"; // Hier wordt het ID van het land gebruikt
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="VAT_number" class="form-label">BTW nummer</label>
                        <input type="text" class="form-control" id="VAT_number" name="VAT_number" required>
                    </div>
                </div>
                <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Formulier voor het invoeren van de gegevens voor een nieuwe klant -->
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                        <!-- Customer Details -->
                        <!-- Your customer form fields here -->
                        
                        <!-- Project Details -->
                        <div class="form-group mb-2">
                            <label for="projectTitle" class="control-label">Titel</label>
                            <input type="text" class="form-control form-control-sm rounded-0" name="title" id="projectTitle" required>
                        </div>
                        <div class="form-group mb-2">
                            <label for="projectDescription" class="control-label">Beschrijving</label>
                            <textarea rows="3" class="form-control form-control-sm rounded-0" name="description" id="projectDescription" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-danger" name="save_customer">Klant aanmaken</button>
                    </form>
                </div>
            </div>
        </div>
    <?php
     require("footer.php");
    ?>