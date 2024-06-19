<?php
// Laad de benodigde libraries
require "vendor/autoload.php";
use Ramsey\Uuid\Uuid; // Gebruik de Ramsey\Uuid library voor het genereren van UUID's
require("start.php"); // Start de sessie

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['email'])) {
    header("Location: loginPage.php"); // Als de gebruiker niet is ingelogd, stuur deze door naar de login pagina
    exit;
}

// Verwerk het formulier als de verzoekmethode POST is
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Verbind met de database
        require("pdo.php");
        
        // Haal de waarden uit het formulier op en trim deze
        $id = Uuid::uuid4()->toString(); // Genereer een nieuw UUID voor de gebruiker
        $naam = trim($_POST["naam"]);
        $voornaam = trim($_POST["voornaam"]);
        $email = trim($_POST["email"]);
        $phone_number = trim($_POST["phone_number"]);
        $admin = isset($_POST["admin"]) ? 1 : 0; // Zet admin naar 1 als de checkbox is aangevinkt, anders naar 0
        $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT); // Hash het wachtwoord
        $secret = trim($_POST["secret"]) ?? "0"; // Zet secret naar 0 als deze niet is opgegeven

        // Definieer de SQL query om de nieuwe gebruiker in de database in te voegen
        $query = "INSERT INTO `users` (id, naam, voornaam, email, phone_number, userPassword, admin, secret)
                  VALUES (:id, :naam, :voornaam, :email, :phone_number, :userPassword, :adm, :secret)";
        
        // Associeer de waarden met de query
        $values = [
            ':id' => $id, 
            ':naam' => $naam, 
            ':voornaam' => $voornaam, 
            ':email' => $email, 
            ':phone_number' => $phone_number, 
            ':userPassword' => $password, 
            ':adm' => $admin, 
            ':secret' => $secret
        ];

        try {
            // Bereid en voer de query uit
            $res = $pdo->prepare($query);
            $res->execute($values);
        } catch (PDOException $e) {
            // Toon een foutmelding bij een queryfout
            echo "Query error: " . $e->getMessage();
            die();
        }
        // Na succesvolle invoer, stuur de gebruiker door naar de overzichtspagina
        header("Location: userOverzicht.php");
        exit;
    } catch (Exception $e) {
        // Toon een foutmelding bij een algemene fout
        echo "Error: " . $e->getMessage();
        die();
    }
}

// Vereist het headerbestand
require("header.php");
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-6">
            <!-- Formulier voor het toevoegen van een nieuwe gebruiker -->
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="Naam" class="form-label">Naam</label>
                    <input type="text" class="form-control" id="Naam" name="naam" required>
                </div>
                <div class="mb-3">
                    <label for="Voornaam" class="form-label">Voornaam</label>
                    <input type="text" class="form-control" id="Voornaam" name="voornaam" required>
                </div>
                <div class="mb-3">
                    <label for="Email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="Email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Telefoon nummer</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                </div>
                <div class="mb-3">
                    <label for="Password" class="form-label">Wachtwoord</label>
                    <input type="password" class="form-control" id="Password" name="password" required>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="admin" name="admin" />
                    <label class="form-check-label" for="admin">Admin</label>
                </div>
                <br>
                <button type="submit" class="btn btn-success">Gebruiker aanmaken</button>
            </form>
        </div>
        <div class="col-sm-6"></div>
    </div>
</div>
<?php require("footer.php"); ?>
