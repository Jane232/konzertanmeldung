<?php
session_start();
//Einbinden der Funktionen
//define("SEP", DIRECTORY_SEPARATOR);
require_once("backend".DIRECTORY_SEPARATOR."config.php");
require_once("layers".SEP."html.php");

//VARIABLEN
// Links zu Ordnern
$subfolder = "backend";
$sep = DIRECTORY_SEPARATOR;
$linkToSub = $subfolder.$sep;
$linkToTab = $linkToSub."tabellen".$sep;
html_initialize_setup_var();
//dump(process_event_import_csv(DIR_USER."admin/events.json", 'C:\xampp\htdocs\dashboard\chor\anmeldung\events\neustesEvent.csv'));

?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo DEF_TITLE; ?> </title>
   <link rel="stylesheet" href="style.css">
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>

  <body>

    <center style="margin: 2em 10%;">
    <a href="http://www.musik.stadtkirche-pforzheim.de"><img src="Musik-Stempel rund.png" alt="Logo" ></a>
    <?php
        //dump(process_event_import_csv("events.json", "events/severEvents.csv"));
        html_user_authentication();
        if (!check_user_login()) {
            exit();
        }
        html_define_group();
        html_show_site();

        echo '<p style="width: 70%;"> '.DEF_FUßZEILE.'</p><br>';
     ?>

</center>
</body>
</html>
