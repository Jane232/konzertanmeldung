<?php
session_start();
//Einbinden der Funktionen
require_once("functions.php");
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

    <?php if (!isset($_POST["start"])) {
    if (isset($_POST["event"])) {
        switch ($_POST["stimme"]) {
        case 'Sopran':
          $maxZuschauer = $platzSopran;
        break;
        case 'Alt':
          $maxZuschauer = $platzAlt;
        break;
        case 'Tenor':
          $maxZuschauer = $platzTenor;
        break;
        case 'Bass':
          $maxZuschauer = $platzBass;
          break;
      }
        $json = json_decode(file_get_contents($linkToTab.$_POST["event"].".json"), true);
        $belegtePlätze = (int) count($json[$_POST["stimme"]]);

        if ($maxZuschauer > $belegtePlätze) {
            $json[$_POST["stimme"]][StringLengthStrip($_POST["name"], $maxInputLenght)] = StringLengthStrip($_POST["email"], $maxInputLenght);
            $feedback = $textSuccess;
        } else {
            $feedback = $textFailed;
        }
        $jsonWriteNew = fopen($linkToTab.$_POST["event"].".json", 'w');
        fwrite($jsonWriteNew, json_encode($json));
        fclose($jsonWriteNew);
    }
    if (isset($_POST["event"]) && isset($feedback)) {
        echo "<h1><u><b>$feedback</b></u></h1>";
    }
    echo '<p style="width: 70%;">'.$textOben.'</p><br><h1>'.$title.'</h1> <br><form class="input-box" action="" method="post">
  <input type="text" name="name" placeholder="Name" required>
  <input type="email" name="email" placeholder="E-Mail" required>
  <select name="stimme" required>
  <option label="Stimme auswählen:"></option>';
    echo xsvToOption(",", "Sopran,Alt,Tenor,Bass");
    echo'</select>
  <button type="submit" name="start">Weiter</button>
</form>';
} elseif (isset($_POST["start"])) {
    echo '<h1>'.$title.'</h1> <p>'.$_POST["name"].' : '.$_POST["stimme"].'</p> <br><form class="input-box" action="" method="post">
     <input type="text" name="name" style="display:none;" value="'.$_POST["name"].'">
     <input type="text" name="stimme" style="display:none;" value="'.$_POST["stimme"].'">
     <input type="text" name="email" style="display:none;" value="'.$_POST["email"].'">';
    $readEvent = fopen($linkToSub.'events.txt', 'r');
    while ($line = fgets($readEvent)) {
        $t = explode("-", $line);
        $eventDocName = $t[0];
        if (!is_file($linkToTab.$eventDocName.".json")) {
            $jsonWriteNew = fopen($linkToTab.$eventDocName.".json", 'w');
            $json = '{"Sopran": {},"Alt": {},"Tenor": {},"Bass": {}}';
            fwrite($jsonWriteNew, $json);
            fclose($jsonWriteNew);
        }
        $json = json_decode(file_get_contents($linkToTab.$eventDocName.".json"), true);
        echo "<h1>$t[1]</h1>";
        $belegtePlätze = (int) sizeof($json[$_POST["stimme"]]);
        if ($belegtePlätze > 0) {
            echo "<p>Bereits eingetragen:</p><ul>";
            foreach ($json[$_POST["stimme"]] as $key =>$name) {
                echo "<li>$key</li>";
            }
            echo '</ul>';
        } else {
            echo "<p>Noch niemand eingetragen</p>";
        }
        switch ($_POST["stimme"]) {
          case 'Sopran':
            $maxZuschauer = $platzSopran;
          break;
          case 'Alt':
            $maxZuschauer = $platzAlt;
          break;
          case 'Tenor':
            $maxZuschauer = $platzTenor;
          break;
          case 'Bass':
            $maxZuschauer = $platzBass;
            break;
        }
        if ($belegtePlätze < $maxZuschauer) {
            if ($maxZuschauer - $belegtePlätze < $freiePlätzeZeigenAb) {
                if ($maxZuschauer - $belegtePlätze == 1) {
                    echo "<p>Noch ein Platz frei</p>";
                } else {
                    echo "<p>Noch ". ($maxZuschauer - $belegtePlätze)." Plätze frei</p>";
                }
            }
            echo '<button type="submit" name="event" value ="'.$eventDocName.'">bei '.$t[1].' eintragen</button>';
        } else {
            echo '<p>Leider sind alle Plätze dieser Probe belegt!</p>';
        }
        echo "<hr/>";
    }
    fclose($readEvent);
    echo '</form>';
}
echo '<p style="width: 70%;"> '.$fußzeile.'</p><br>';
 ?>
</center>
</body>
</html>
