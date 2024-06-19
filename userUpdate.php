<?php
// Start de sessie en vereist de noodzakelijke bestanden
require("start.php");
require("pdo.php");

// Controleer of het verzoek een POST-verzoek is
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Haal de gegevens op uit het POST-verzoek en trim eventuele overbodige spaties
    $id = $_POST["id"];
    $naam = trim($_POST["naam"]);
    $voornaam = trim($_POST["voornaam"]);
    $email = trim($_POST["email"]);
    $phone_number = trim($_POST["phone_number"]);
    // Controleer of het admin-vakje is aangevinkt en zet de waarde om naar 1 of 0
    $admin = isset($_POST["admin"]) && $_POST["admin"] == "on" ? 1 : 0;

    // Bereid de SQL-query voor om de gebruiker bij te werken
    $query = "UPDATE `users` 
              SET  naam = :naam, voornaam = :voornaam, email = :email, phone_number = :phone_number, `admin` = :adm 
              WHERE `id` = :id";
    $values = [":id" => $id, "naam" => $naam, "voornaam" => $voornaam, "email" => $email, "phone_number" => $phone_number, "adm" => $admin];

    try {
        // Bereid en voer de query uit
        $res = $pdo->prepare($query);
        $res->execute($values);
        // Redirect naar de gebruikersoverzichtspagina
        header("Location: userOverzicht.php");
        exit;
    } catch (PDOException $e) {
        // Toon een foutmelding bij een queryfout
        echo 'Query error<br>' . $e; 
        die();
    }
}

// Controleer of het verzoek een GET-verzoek is en of er een id-parameter is meegegeven
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $query = "SELECT * FROM users WHERE id = :id";
    $values = ['id' => $id];
    try {
        // Bereid en voer de select query uit
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);
        // Haal de gebruikersgegevens op
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Toon een foutmelding bij een queryfout
        echo 'Query error: ' . $e->getMessage();
        die();
    }
} else {
    // Redirect naar de gebruikersoverzichtspagina als er geen id-parameter is meegegeven
    header("Location: userOverzicht.php");
    exit;
}

// Vereist het headerbestand
require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
                <!-- Begin van het formulier voor het bijwerken van gebruikersgegevens -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                    <!-- Verborgen invoerveld voor de gebruikers-ID -->
                    <input type="hidden" name="shop_id" value="<?php echo $user['id']; ?>">
                    <div class="mb-3">
                        <!-- Invoerveld voor de naam van de gebruiker -->
                        <label for="Naam" class="form-label">Naam</label>
                        <input type="text" class="form-control" id="Naam" name="naam" required value="<?php echo $user['naam']; ?>">
                    </div>
                    <div class="mb-3">
                        <!-- Invoerveld voor de voornaam van de gebruiker -->
                        <label for="Voornaam" class="form-label">Voornaam</label>
                        <input type="text" class="form-control" id="Voornaam" name="voornaam" required value="<?php echo $user['voornaam']; ?>">
                    </div>
                    <div class="mb-3">
                        <!-- Invoerveld voor het e-mailadres van de gebruiker -->
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="email" required value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <!-- Invoerveld voor het telefoonnummer van de gebruiker -->
                        <label for="phone_number" class="form-label">Telefoonnummer</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required value="<?php echo $user['phone_number']; ?>">
                    </div>
                    <div class="form-check form-switch">
                        <!-- Checkbox voor het instellen van de admin-status van de gebruiker -->
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" name="admin" <?php echo $user['admin'] ? 'checked' : ''; ?> />
                        <label class="form-check-label" for="admin">Admin</label>
                    </div>
                    <br>
                    <!-- Verborgen invoerveld voor de gebruikers-ID -->
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <!-- Submit-knop voor het bijwerken van de gebruikersgegevens -->
                    <button type="submit" class="btn btn-success">Gebruiker updaten</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>
<?php
// Vereist het footerbestand
require("footer.php");
?>
