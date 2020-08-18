
<?php
//Einbinden der Funktionen
require_once("functions.php");

//VARIABLEN

// Links zu Ordnern
$subfolder = "anmeldung";
$sep = (strpos(getcwd(), "/") === false) ? '\\' : '/' ;
$linkToSub = $subfolder.$sep;
$linkToTab = $linkToSub."tabellen".$sep;

//Variablen deklarieren aus setup.txt
$fh = fopen($linkToSub.'setup.txt', 'r');
// korekte Initialisierung (nach Typ) der Variablen aus setup.txt
while ($line = fgets($fh)) {
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
}
fclose($fh);


//CODE

// wenn Formular ausgefüllt ist, dann wird if ausgeführt
// Eintragen des User-Inputs in CSV
$eingetragen = "";
if (isset($_POST["send"])) {
    if (getLines($linkToTab.$_POST["event"]) < $maxZuschauer+1) {//Wenn Event noch nicht voll ist, dann:
        // Nummer des Users im Event an 1. Stelle
        $content = $contentCSV = getLines($linkToTab.$_POST["event"]);
        // Iterierung über alle POST-Vars
        foreach ($_POST as $key => $value) {
            // Alle eintragen bis auf event und send (nicht in CSV erwünscht)
            // Eintragen => Input des Users mit "," getrennt an den String hängen
            switch ($key) {
              case 'event':
              case 'send':
                break;
              default:
              //$content = $content."___".$value;
              $contentCSV .= ",".$value;
                break;
            }
        }
        //An jede Zeile einen Zeilenumbruch
        //$content = $content."\n";
        $contentCSV= $contentCSV."\n";

        //.txt
        //$fp = fopen($linkToTab.$_POST["event"].".txt", 'a');
        //fwrite($fp, $content);
        //fclose($fp);

        //.csv -> Tabelle (.CSV schreiben) / preg_replace um ungewünschte Leerzeichen o.ä. zu filtern
        $fp = fopen($linkToTab.preg_replace('/\s+/', '', $_POST["event"]).".csv", 'a');
        fwrite($fp, $contentCSV);
        fclose($fp);
        $eingetragen = true;
    } else {
        $eingetragen = false;
    }
}
?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo $title; ?> </title>
   <link rel="stylesheet" href="style.css">
  </head>

  <body>
    <?php
    if (isset($_GET["sent"])) {
        echo '<center style="margin: 20px 0 0 0;">
        <!--Logo oben-->
        <a href="http://www.musik.stadtkirche-pforzheim.de"><img src="Musik-Stempel rund.png" alt="Logo" ></a><center>';
        if ($eingetragen === true) {
            echo'<div style="color:black;font-size: 1.4em;margin:10% 0 0 0;">'.$textSuccess.'</div>';
        } elseif ($eingetragen === false) {
            echo'<div style="color:black;font-size: 1.4em;margin:10% 0 0 0;">'.$textFailed.'</div>';
        }
        echo '<br><br><a href="index.php">Hier zurück zur Anmeldung</a>
        <p style="padding: 0 15%; position: fixed;bottom: 0; right: 0; margin: 10% auto 5% auto ;"> '.$fußzeile.'</p></center>';
    } else {
        echo '<center style="margin: 20px 0 0 0;">
        <!--Logo oben-->
        <a href="http://www.musik.stadtkirche-pforzheim.de"><img src="Musik-Stempel rund.png" alt="Logo" ></a>
        <!--Text-Oben-->
        <p style="width: 70%;">'.$textOben.'</p>

      <!--Formular-->
    <form class="input-box" style="margin: 3em 0 0 0" action="index.php?sent=true" method="post" >
      <h1>Anmeldung</h1>
      <select name="event" required>
        <option label="Konzerte:"></option>';

        // Events aus event.txt lesen und in select bzw. option einfügen
        $lable = $nr = "";
        $fh = fopen($linkToSub.'events.txt', 'r');
        //Iterieren über alle Zeilen
        while ($line = fgets($fh)) {
            // an "-" in Array spalten
            $t = explode("-", $line);
            $linesOfCurrentEvent = getLines($linkToTab.$t[0]);
            if ($lable != "" ||$nr != "") {
                $lable .= ",";
                $nr .= ",";
            }
            $nr .= $t[0];
            //Verschiedene Anzeigen: normal / (x Plätze übrig!) / ausgebucht (...)
            if ($linesOfCurrentEvent < $maxZuschauer+1) {
                $freeSeats = $maxZuschauer + 1 - $linesOfCurrentEvent;
                if ($freeSeats<$freiePlätzeZeigenAb+1) {
                    $lable .= $t[1]."($freeSeats Plätze übrig!)";
                } else {
                    $lable .= $t[1];
                }
            } else {
                $lable .= "ausgebucht (".$t[1].")";
            }
        }
        fclose($fh);
        echo xsvToOption(",", $nr, "", $lable);
        echo'</select>';

        //Inputfelder aus json-Format lesen und in HTML umwandeln
        $json = json_decode(file_get_contents($linkToSub."inputfelder.txt"), true);
        //Für alle inputs
        foreach ($json["input"] as $key => $val) {
            // Wenn Label vorhanden
            if (!empty($json["input"][$key]["label"])) {
                echo '<label for="'.$key.'">'.$json["input"][$key]["label"].'</label>';
            }
            echo '<input type="'.$json["input"][$key]["type"].'" name="'.$key.'" placeholder="'.$key.'" '.$json["input"][$key]["required"].'>';
        }
        //Submit-Button
        echo'<button type="submit" name="send" >Absenden</button></form>
        <!--Text-Unten-->
        <p style="width: 70%;"> '.$fußzeile.'</p><br>
      </center>
      ';
    }
    ?>

</body>
</html>
