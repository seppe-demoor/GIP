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
?>
<head>
    <meta charset='utf-8' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script>

      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridWeek'
        });
        calendar.render();
      });
      

    </script>
  </head>
  <body>
    <div id='calendar'></div>
  </body>
<?php
require("footer.php");
?>