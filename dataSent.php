<?php
session_start();
//Einbinden der Funktionen
//define("SEP", DIRECTORY_SEPARATOR);
require_once("anmeldung".DIRECTORY_SEPARATOR."staticVars.php");
//require_once("functions.php");
require_once("layers".DIRECTORY_SEPARATOR."html.php");

//VARIABLEN
// Links zu Ordnern
$subfolder = "anmeldung";
$sep = DIRECTORY_SEPARATOR;
$linkToSub = $subfolder.$sep;
$linkToTab = $linkToSub."tabellen".$sep;

$readSetup = fopen($linkToSub.'setup.txt', 'r');
// korekte Initialisierung (nach Typ) der Variablen aus setup.txt
while ($line = fgets($readSetup)) {
    $t = explode("--", $line);
    $name = $t[0];
    //Var-Name = $t[0]
    //Switch über 2. Arrayeintrag (Var-Type)
    //Var-Wert = $t[2]
    switch ($t[1]) {
  case 'int':
    $$name = (int) preg_replace('/\s+/', '', $t[2]);
    break;
  case 'bool':
    $$name = filter_var($t[2], FILTER_VALIDATE_BOOLEAN);
    break;
  default:
    $$name = $t[2];
    break;
}
    define("DEF_".strtoupper($name), $$name);
}
fclose($readSetup);
?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo $title; ?> </title>
   <link rel="stylesheet" href="style.css">
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>

  <body>
    <center style="margin: 2em 10%;">
    <a href="http://www.musik.stadtkirche-pforzheim.de"><img src="Musik-Stempel rund.png" alt="Logo" ></a>
    <?php
        if (isset($_POST["stimme"])) {
            define_voice($_POST["stimme"]);
        }
        if (isset($_POST["event"])) {
            $feedback = setValueToJSON($_POST["event"]);
            echo "<div style='margin-top:3em;'><h1><u><b>$feedback</b></u></h1></div>";
        }
        echo '<br><br><a href="index.php"> Hier zurück zur Anmeldung</a>';
        echo '<p style="width: 70%; margin-top:3em;"> '.DEF_FUßZEILE.'</p><br>';
     ?>
</center>

</body>
</html>
