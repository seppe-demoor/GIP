<!DOCTYPE html>
<?php
require("start.php");

$showAlert = false;

require("header.php");
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-sm-12">
                <?php if ($showAlert) : ?>
                    <div class="alert alert-danger">
                        <strong>FOUT!</strong> Deze combinatie gebruikersnaam en wachtwoord bestaat niet.
                    </div>
                <?php endif; ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
                    <div class="mb-3">
                        <label for="Username" class="form-label">Gebruikersnaam</label>
                        <input type="text" class="form-control" id="Username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="Name" class="form-label">Naam</label>
                        <input type="text" class="form-control" id="Name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="FirstName" class="form-label">Voornaam</label>
                        <input type="text" class="form-control" id="FirstName" name="firstname" required>
                    </div>
                    <div class="mb-3">
                        <label for="Email" class="form-label">E-mailadress</label>
                        <input type="email" class="form-control" id="Email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="Pssword" class="form-label">wachtwoord</label>
                        <input type="text" class="form-control" id="Pssword" name="password" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="Switch" name="switch">
                        <label class="form-check-label" for="Switch">Admin</label>
                    </div>
                    
                    <br><br>
                    <button type="submit" class="btn btn-success btn-lg">Update Gebruiker</button>
                </form>
            </div>
        </div>
    </div>
<?php
require("footer.php");
?>