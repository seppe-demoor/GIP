<!DOCTYPE html>
<?php
require("start.php");

if (!isset($_SESSION['admin']) || $_SESSION['admin'] == 0)  {
    header("Location: beveiligd.php");
    exit;
}

require("pdo.php");
$query = "SELECT `userID`, `GUID`, `userName`, `naam`, `voornaam`, `email`, `admin`
              FROM `users` 
              WHERE `active` = 1";
    //values voor de PDO
    try {
        $res = $pdo->prepare($query);
        $res->execute();
    } catch (PDOException $e) {
        //error in query
        echo "Query error:" . $e;
        die();
    }
require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <h3>Overzicht gebruikers</h3>
                <table class="table table-hover table-striped">
                    <tr>
                        <th>Gebruikersnaam</th>
                        <th>Naam</th>
                        <th>Voornaam</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Acties</th>
                    </tr>
                    <?php while ($row = $res->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td><?php echo $row["userName"]; ?></td>
                            <td><?php echo $row["naam"]; ?></td>
                            <td><?php echo $row["voornaam"] ?></td>
                            <td><?php echo $row["email"] ?></td>
                            <td><?php echo $row["admin"] ? '<i class="bi bi-check-square-fill text-success"></i>' : '<i class="bi bi-square"></i>';?></td>
                            <?php echo "User ID to delete: " . $row['admin']; ?>
                            <td>
                                    <a href="userUpdate.php?GUID=<?php echo $row['GUID']; ?>">
                                        <i class="bi bi-pencil-square text-success fa-2x"></i>
                                    </a>
                                    <!-- Add a link to open the MDB modal for delete confirmation -->
                                    <a href="confirmDelete.php?GUID=<?php echo $row['GUID']; ?>">
                                        <i class="bi bi-x-square text-danger fa-2x"></i>
                                    </a>
                                    <a href="WWreset.php?GUID=<?php echo $row['GUID']; ?>">
                                        <i class="bi bi-arrow-clockwise text-info fa-2x"></i>
                                    </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
    <div id="deleteModal" style="display: none; background: rgba(0, 0, 0, 0.7); position: fixed; top: 0; left: 0; width: 100%; height: 100%; justify-content: center; align-items: center;">
        <div style="background: white; padding: 20px; border-radius: 5px; max-width: 400px; text-align: center;">
            <h4>Verwijderen</h4>
            <p>Weet je zeker dat je de gebruiker wilt verwijderen?</p>
            <button onclick="deleteUser()">Ja</button>
            <button onclick="closeModal()">Nee</button>
        </div>
    </div>
<?php
require("footer.php");
?>