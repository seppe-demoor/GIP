<!DOCTYPE html>
<?php
session_start();
require("start.php");
require("pdo.php");
if (!isset($_SESSION["username"])) {
    header("Location: loginPage.php");
    exit;
}

require("header.php");

// Haal de GUID van de aangemelde gebruiker op
$loggedInUser = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT guid FROM 'users' WHERE 'username' = :username");
$stmt->bindParam(':username', $loggedInUser, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$userGUID = $result['guid'];

// Haal alle berichten op voor de aangemelde gebruiker
$stmt = $pdo->prepare("SELECT * FROM berichten WHERE user_guid = :user_guid");
$stmt->bindParam(':user_guid', $userGUID, PDO::PARAM_INT);
$stmt->execute();
$berichten = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-sm-4">
            <h3>Beveiligde pagina van <?php echo $_SESSION['username']; ?></h3>
        </div>
        <div class="col-sm-8">
            <table class="table">
        
                <body>
                    <?php foreach ($berichten as $bericht): ?>
                        <tr>
                            <td><?php echo $bericht['id']; ?></td>
                            <td><?php echo $bericht['onderwerp']; ?></td>
                            <td><?php echo $bericht['inhoud']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </body>
            </table>
        </div>
    </div>
</div>

<?php
require("footer.php");
?>
