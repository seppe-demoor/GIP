<!DOCTYPE html>
<?php
require("start.php");

require("pdo.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $naam = trim($_POST["naam"]);
    $voornaam =trim($_POST["voornaam"]);
    $email = trim($_POST["email"]);
    $phone_number = trim($_POST["phone_number"]);
    $admin = isset($_POST["admin"]) && $_POST["admin"] == "on" ? 1 : 0;

    $query = "UPDATE `users` 
              SET  naam = :naam, voornaam = :voornaam, email = :email, phone_number = :phone_number, `admin` = :adm 
              WHERE `id` = :id";
    $values = [":id" => $id, "naam" => $naam, "voornaam" => $voornaam, "email" => $email,  "phone_number" => $phone_number, "adm" => $admin];
    // execute query

    try {
        $res = $pdo->prepare($query);
        $res->execute($values);
        header("Location: userOverzicht.php");
        exit;
    } catch (PDOException $e) {
        // error in de query
        echo 'Query error<br>' . $e; 
        die();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["id"])) {
    $id = $_GET["id"];
    $query = "SELECT * FROM users WHERE id = :id";
    $values = ['id' => $id];
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($values);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo 'Query error: ' . $e->getMessage();
        die();
    }
} else {
    header("Location: userOverzicht.php");
    exit;
}


require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-6">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
            <input type="hidden" name="shop_id" value="<?php echo $user['id']; ?>">
                    <div class="mb-3">
                        <label for="Naam" class="form-label">Naam</label>
                        <input type="text" class="form-control" id="Naam" name="naam" required value="<?php echo $user['naam']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="Voornaam" class="form-label">Voornaam</label>
                        <input type="text" class="form-control" id="Voornaam" name="voornaam" required value="<?php echo $user['voornaam']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="email" required value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">telefoon nummer</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" required value="<?php echo $user['phone_number']; ?>">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" name="admin" <?php echo $user['admin'] ? 'checked' : ''; ?> />
                        <label class="form-check-label" for="admin">Admin</label>
                    </div>
                    <br>
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-success">Gebruiker updaten</button>
                </form>
            </div>
            <div class="col-sm-6">
            </div>
        </div>
    </div>