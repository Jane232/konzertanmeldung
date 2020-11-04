<?php
session_start();
//Einbinden der Funktionen
//define("SEP", DIRECTORY_SEPARATOR);
require_once("backend".DIRECTORY_SEPARATOR."config.php");
require_once("layers".DIRECTORY_SEPARATOR."html.php");
//VARIABLEN
// Links zu Ordnern
$subfolder = "backend";
$sep = DIRECTORY_SEPARATOR;
$linkToSub = $subfolder.$sep;
$linkToTab = $linkToSub."tabellen".$sep;
html_initialize_setup_var();
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
        define_user();
        html_define_group();
        $emailFeedback = $feedback="";
        if (isset($_POST["event"])) {
            if (!check_entry_existing()) {
                if (check_Event_available(UBE.SUBFOLDER.$_POST["event"].".json", $_POST["event"])) {
                    $feedback = setValueToJSON($_POST["event"]);
                    $emailFeedback = (html_mail_send_event_reminder())?"Es wurde erfolgreich eine Bestätigungsemail an Sie versandt.":"Es gab einen Fehler beim E-Mail-Versandt";
                } else {
                    $feedback = DEF_TEXT_FAIL;
                }
            } else {
                $feedback = DEF_TEXT_ENTRYEXISTED;
            }
            echo "<div style='margin-top:3em;'><h1><u><b>$feedback</b></u></h1></div>";
            echo $emailFeedback;
        }
        echo '<br><br><a href="index.php"> Hier zurück zur Anmeldung</a>';
        echo '<p style="width: 70%; margin-top:3em;"> '.DEF_FUßZEILE.'</p><br>';
     ?>
</center>

</body>
</html>
