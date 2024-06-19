<!DOCTYPE html>
<?php
    session_start(); // Start or resume the session
?>
<html>
<head>
    <title>inhoud session</title>
</head>
<body>
    <pre>
        <?php
            print_r($_SESSION); // Output the contents of $_SESSION
        ?>
    </pre>
</body>
</html>
