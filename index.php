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

    <?php
    $jsonInput = json_decode(file_get_contents($linkToSub."inputfelder.txt"), true);

    if (!isset($_POST["start"])) {
        if (isset($_POST["event"])) {
            $jsonContent = json_decode(file_get_contents($linkToTab.$_POST["event"].".json"), true);

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
            $belegtePlätze = (int) count($jsonContent[$_POST["stimme"]]);

            if ($maxZuschauer > $belegtePlätze) {
                foreach ($jsonInput["input"] as $key => $val) {
                    //$allInputs[] = array(StringLengthStrip($key, $maxInputLenght) => StringLengthStrip($_POST[$key], $maxInputLenght));
                    $allInputs[StringLengthStrip($key, $maxInputLenght)] = StringLengthStrip($_POST[$key], $maxInputLenght);
                }
                $jsonContent[$_POST["stimme"]][] = $allInputs;
                $feedback = $textSuccess;
            } else {
                $feedback = $textFailed;
            }
            $fileWriteNewJson = fopen($linkToTab.$_POST["event"].".json", 'w');
            fwrite($fileWriteNewJson, json_encode($jsonContent));
            fclose($fileWriteNewJson);
        }
        if (isset($_POST["event"]) && isset($feedback)) {
            echo "<h1><u><b>$feedback</b></u></h1>";
        }
        echo '<p style="width: 70%;">'.$textOben.'</p><br><h1>'.$title.'</h1> <br><form class="input-box" action="" method="post">';
        //Für alle inputs
        foreach ($jsonInput["input"] as $key => $val) {
            // Wenn Label vorhanden
            if (!empty($jsonInput["input"][$key]["label"])) {
                echo '<label for="'.$key.'">'.$jsonInput["input"][$key]["label"].'</label>';
            }
            $temp = (filter_var($jsonInput["input"][$key]["required"], FILTER_VALIDATE_BOOLEAN))?'required':' ';
            echo '<input type="'.$jsonInput["input"][$key]["type"].'" name="'.$key.'" placeholder="'.$key.'" '.$temp.'>';
        }
        echo'<select name="stimme" required>
  <option label="Stimme auswählen:"></option>';
        echo xsvToOption(",", "Sopran,Alt,Tenor,Bass");
        echo'</select>
  <button type="submit" name="start">Weiter</button>
</form>';
    } elseif (isset($_POST["start"])) {
        echo '<h1>'.$title.'</h1> <h1>'.$_POST["stimme"].':</h1> <br><div style="color:white;"><form class="input-box" action="" method="post">
     <input type="text" name="stimme" style="display:none;" value="'.$_POST["stimme"].'">';
        foreach ($jsonInput["input"] as $key => $val) {
            $value = (isset($_POST[$key]))?'value="'.$_POST[$key].'"':" ";
            echo '<input style="display:none;" type="'.$jsonInput["input"][$key]["type"].'" name="'.$key.'" '.$value.' >';
        }
        $readEvent = fopen($linkToSub.'events.txt', 'r');
        while ($line = fgets($readEvent)) {
            $t = explode("-", $line, 2);
            $eventDocName = $t[0];
            if (!is_file($linkToTab.$eventDocName.".json")) {
                $jsonContent = '{"Sopran": {},"Alt": {},"Tenor": {},"Bass": {}}';
                $fileWriteNewJson = fopen($linkToTab.$eventDocName.".json", 'w');
                fwrite($fileWriteNewJson, $jsonContent);
                fclose($fileWriteNewJson);
            }
            $jsonContent = json_decode(file_get_contents($linkToTab.$eventDocName.".json"), true);
            //-----------------------------------------------------------------------------------------------

            echo "<h2 style='font-weight: bold;'>$t[1]</h2>";
            //-----------------------------------------------------------------------------------------------

            $belegtePlätze = (int) sizeof((array)$jsonContent[$_POST["stimme"]]);
            echo "<p>";
            if ($belegtePlätze > 0) {
                echo "Bereits eingetragen:<ul style='list-style:none;'>";
                foreach ($jsonContent[$_POST["stimme"]] as $key =>$name) {
                    echo "<li>".$jsonContent[$_POST["stimme"]][$key]["Vorname"]." ".$jsonContent[$_POST["stimme"]][$key]["Name"]."</li>";
                }
                echo '</ul>';
            } else {
                echo "Noch niemand eingetragen";
            }
            echo "</p>";
            //-----------------------------------------------------------------------------------------------
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
                //-----------------------------------------------------------------------------------------------

                echo '<button type="submit" name="event" style="border-width: 4px;" value ="'.$eventDocName.'">bei '.$t[1].' eintragen</button>';
            } else {
                echo '<p>Leider sind alle Plätze dieser Probe belegt!</p>';
            }
            echo "<hr/>";
        }
        fclose($readEvent);
        echo '</form> </div>';
    }
echo '<p style="width: 70%;"> '.$fußzeile.'</p><br>';
 ?>
</center>
</body>
</html>
