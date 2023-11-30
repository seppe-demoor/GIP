<!DOCTYPE html>
<html>
    <head>
        <title>Testmail</title>
        <meta charset="utf-8">
    </head>
    <body>
       <?php 
            $to = "yorben.vandermeiren@leerling.go-ao.be";
            $from = "yorben.vandermeiren@leerling.go-ao.be";
            $subject = "onderwerp van de mail";
            $message = "Best, dit is een testmailtje";

            if(mail($to, $subject, $message, $from)) {
                echo "Mail is verstuurd";
            } else {
                echo "Mail is niet verzonden";
            }
       ?> 
    </body>
</html>