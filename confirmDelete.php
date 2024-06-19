<?php
// Starten van de sessie
require("start.php");

// Controleren of de gebruiker is aangemeld
if (!isset($_SESSION["email"])) {
    // Gebruiker is nog niet aangemeld, doorsturen naar de inlogpagina
    header("Location: loginPage.php");
    exit;
}

// Inclusief het PDO-bestand voor databaseverbinding
require("pdo.php");

// Controleren of het verzoek een GET-verzoek is en of er een ID is opgegeven
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["id"])) {
    // Ontvangen van de gebruikers-ID uit de GET-parameters
    $id = $_GET['id'];
    
    // Query om de gebruikersgegevens op te halen op basis van de ID
    $query = "SELECT  `naam`, `voornaam`, `email` FROM `users` WHERE `id` = :id";
    
    try 
    {
        // Voorbereiden van de query en uitvoeren met de ontvangen ID als parameter
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        
        // Ophalen van de gebruikersgegevens
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } 
    catch (PDOException $e) 
    {
         echo 'Error fetching user data:' . $e->getMessage();
        header("Location: useroverzicht.php");
        exit();
    }
} else {
    // Als er geen ID is opgegeven in de GET-parameters, doorsturen naar het gebruikersoverzicht
    header("Location: userOverzicht.php");
    exit();
}

// Inclusief het headerbestand voor de opmaak van de pagina
require("header.php");
?>

<!-- HTML voor het bevestigen van het verwijderen van de gebruiker -->
<div class="container mt-5">
    <div class="row">
        <div class="col-sm-12">
            <h3>Bevestig Verwijderen</h3>
            <!-- Weergeven van de gebruikersnaam van de te verwijderen gebruiker -->
            <p>Weet je zeker dat je de gebruiker "<?php echo $user['username']; ?>" wilt verwijderen?</p>
            <!-- Link naar het script voor het daadwerkelijk verwijderen van de gebruiker -->
            <a href="Delete.php?id=<?php echo $id; ?>" class="btn btn-danger">Verwijderen</a>
            <!-- Link naar het gebruikersoverzicht voor annuleren van de actie -->
            <a href="userOverzicht.php" class="btn btn-secondary">Annuleren</a>
        </div>
    </div>
</div><?php
require("footer.php");
?>