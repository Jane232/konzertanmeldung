<?php
//Einbinden der Funktionen
require_once("functions.php");
//VARIABLEN
// Links zu Ordnern
$subfolder = "anmeldung";
$sep = DIRECTORY_SEPARATOR;
$linkToSub = $subfolder.$sep;
$linkToTab = $linkToSub."tabellen".$sep;

$md5 = "";
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

if (isset($_POST["send"])) {
    // input sanitizen
    //Funktion die überprüft ob eine Postvar vom User auch wirklich existiert bzw. ob sie vorgesehen ist
    function checkIfPostIsInEvents($linkToSub, $post)
    {
        $json = json_decode(file_get_contents($linkToSub."inputfelder.txt"), true);

        foreach ($json["input"] as $key => $val) {
            //$key = preg_replace('/\s+/', '', $key);
            //echo $key."<br>".str_replace('_', '', $post)."<br>";
            if (preg_replace('/\s+/', '', $key) == str_replace('_', '', $post)) {
                $return = true;
                break;
            }
        }
        return $return = (isset($return)) ? $return : false ;
    }
    // Über alle Post Iterieren
    foreach ($_POST as $name => $value) {
        // Die Standart Post werden gefiltert
        switch ($name) {
          case 'event':
          case 'send':
            break;
          default:
          // Wenn Post in Liste steht, dann:
          if (checkIfPostIsInEvents($linkToSub, $name)) {
              // Post sanitizen
              $_POST[$name] = htmlentities(str_replace(array(',','\\','/','-','_','<','>'), '', $_POST[$name]), ENT_QUOTES, 'utf-8');
              // Wenn Post länger als $maxInputLenght ist wird der Rest abgeschnitten
              if (strlen($_POST[$name]) > $maxInputLenght) {
                  $_POST[$name] = substr($_POST[$name], 0, $maxInputLenght);
              }
          } else {
              //Falls Post nicht vorgesehen dann wird sie gelöscht
              unset($_POST[$name]);
          }
            break;
        }
    }
    // Erstellung des md5 Hashes für den Cookienamen um Doppelsendungen zu vermeiden!
    $token = "";
    foreach ($_POST as $value) {
        $token .= $value;
    }
    $md5 = md5($token);
}

//CODE
// wenn Formular ausgefüllt ist, dann wird if ausgeführt
// Eintragen des User-Inputs in CSV
$eingetragen = "";
if (isset($_POST["send"]) && !isset($_COOKIE[$md5])) {
    $token = "";
    foreach ($_POST as $value) {
        $token .= $value;
    }
    setcookie(md5($token), "CookieGegenDoppelEintraegeVon$token", time() + 7200);
    //setcookie(md5($token), "", time() + 7200);

    if (getLines($linkToTab.$_POST["event"]) < $maxZuschauer) {//Wenn Event noch nicht voll ist, dann:
        // Nummer des Users im Event an 1. Stelle
        $contentCSV = getLines($linkToTab.$_POST["event"])+1;
        // Iterierung über alle POST-Vars
        foreach ($_POST as $key => $value) {
            // Alle eintragen bis auf event und send (nicht in CSV erwünscht)
            // Eintragen => Input des Users mit "," getrennt an den String hängen
            switch ($key) {
              case 'event':
              case 'send':
                break;
              default:
              $contentCSV .= ",".$value;
                break;
            }
        }
        //An jede Zeile einen Zeilenumbruch
        $contentCSV= $contentCSV."\n";
        //.csv -> Tabelle (.CSV schreiben) / preg_replace um ungewünschte Leerzeichen o.ä. zu filtern
        $event = preg_replace('/\s+/', '', $_POST["event"]);
        $fp = fopen($linkToTab.$event.".csv", 'a');
        fwrite($fp, $contentCSV);
        fclose($fp);

        // Absicherung (Jeder User bekommt neue Datei)
        if (!is_dir($linkToTab.$event)) {
            if (mkdir($linkToTab.$event)) {
                $dirExists = true;
            } else {
                $dirExists = false;
            }
        } else {
            $dirExists = true;
        }
        //Wenn Verzeichniss vorhanden:
        if ($dirExists == true) {
            // Erstellt einzigartige Datei mit den CSV-Werten des Inputs
            $fp = fopen($linkToTab.$event.$sep.uniqid(), 'w');
            fwrite($fp, $contentCSV);
            fclose($fp);
        }

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
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>

  <body>
    <?php
    if (isset($_GET["sent"]) && !isset($_COOKIE[$md5])) {
        echo '<center style="margin: 20px 0 0 0;">
        <!--Logo oben-->
        <a href="http://www.musik.stadtkirche-pforzheim.de"><img src="Musik-Stempel rund.png" alt="Logo" ></a><center>';
        if ($eingetragen === true) {
            echo'<div style="color:black;font-size: 1.4em;margin:10% 0 0 0;">'.$textSuccess.'</div>';
        } elseif ($eingetragen === false) {
            echo'<div style="color:black;font-size: 1.4em;margin:10% 0 0 0;">'.$textFailed.'</div>';
        }
        echo '<br><br><a href="index.php">Hier zurück zur Anmeldung</a><br><br><br><p>'.$fußzeile.'</p></center>';
    } elseif (isset($_GET["sent"]) && isset($_COOKIE[$md5])) {
        echo '<center style="margin: 20px 0 0 0;">
               <a href="http://www.musik.stadtkirche-pforzheim.de"><img src="Musik-Stempel rund.png" alt="Logo" ></a>
                <br><br>Diese Person wurde schon eingetragen!<br><br>
                <a href="index.php">Hier zurück zur Anmeldung</a><br><br><br><p>'.$fußzeile.'</p>
              </center>';
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
            if ($linesOfCurrentEvent < $maxZuschauer) {
                $freeSeats = $maxZuschauer - $linesOfCurrentEvent;
                if ($freeSeats<$freiePlätzeZeigenAb+1) {
                    if ($freeSeats > 1) {
                        $lable .= $t[1]."($freeSeats Plätze übrig!)";
                    } else {
                        $lable .= $t[1]."($freeSeats Platz übrig!)";
                    }
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
