<!DOCTYPE html>
<?php
    session_start();
?>
<html>
    <head>
        <title>inhooud session</title>
    </head>
    <body>
        <pre>
            <?php
                print_r($_SESSION);
            ?>
        </pre>
    </body>
</html>