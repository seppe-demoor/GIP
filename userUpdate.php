<!DOCTYPE html>
<?php
require("start.php");

$showAlert = false;

require("header.php");
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Gebruiker</title>
    <link rel="stylesheet" href="path/to/your/bootstrap/css">
    <style>

        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .form-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px; /* You can adjust the max-width as needed */
            width: 100%;
        }

        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="form-container">
            <?php if ($showAlert) : ?>
                <div class="alert alert-danger">
                    <strong>FOUT!</strong> Deze combinatie gebruikersnaam en wachtwoord bestaat niet.
                </div>
            <?php endif; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
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

</body>

</html>
<?php
require("footer.php");
?>
