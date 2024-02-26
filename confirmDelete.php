<?php
require("start.php");

if (!isset($_SESSION["email"])) {
    //user is reeds aangemeld
    header("Location: loginPage.php");
    exit;
}

require("pdo.php");

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["id"])) {
    $id = $_GET['id'];
    
    $query = "SELECT  `naam`, `voornaam`, `email` FROM `users` WHERE `id` = :id";
    
    try 
    {
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } 
    catch (PDOException $e) 
    {
        error_log('Error fetching user data: ' . $e);
        header("Location: useroverzicht.php");
        exit();
    }
} else {
    header("Location: userOverzicht.php");
    exit();
}

require("header.php");
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-sm-12">
            <h3>Bevestig Verwijderen</h3>
            <p>Weet je zeker dat je de gebruiker "<?php echo $user['username']; ?>" wilt verwijderen?</p>
            <a href="Delete.php?id=<?php echo $id; ?>" class="btn btn-danger">Verwijderen</a>
            <a href="userOverzicht.php" class="btn btn-secondary">Annuleren</a>
        </div>
    </div>
</div>