<?php
function show($show)
{
    $titleOfBackend = namesOfSites($_GET["show"]);
    switch ($show) {
      case 'events':
        events($titleOfBackend);
        break;
      case 'lists':
        lists($titleOfBackend);
        break;
      case 'deleteList':
        deleteList($titleOfBackend);
        break;
      case 'setup':
        setup($titleOfBackend);
        break;
      case 'addFrontendUser':
        addFrontendUser($titleOfBackend);
        break;
      case 'addBackendUser':
        addBackendUser($titleOfBackend);
        break;
      case 'deleteEntry':
        deleteEntry($titleOfBackend);
        break;
      case 'inputconfig':
        inputconfig($titleOfBackend);
        break;
      case 'logOut':
        // code...
        break;
  }
}
function events($titleOfBackend)
{
    require("staticVars.php");
    // Auslesen der aktuellen Events und Events mit <br> trennen um jeweils eine neue Zeile zu haben
    $events ="";
    foreach (fileToArray("events.txt") as $key) {
        $events .= $key."<br>";
    }
    // $_POST["paragraph"] ist nur gesetzt wenn User etwas abgeschickt hat (enthält die (veränderten) Events)
    if (isset($_POST["paragraph"])) {
        // Aufspalten an den Zeilen
        $lines = explode('<br>', $_POST["paragraph"]);
        $content = "";
        // Über Array iterieren
        for ($i=0; $i < sizeof($lines)-1; $i++) {
            // An "-" Spalten
            $name = explode('-', $lines[$i], 2);
            // Alle whitespace-Chars entfernen die im Namen sind
            $lineStriped = str_replace(' ', '', preg_replace('/\s+/', '', $name[0]))."-".$name[1];
            //Zusammenfügen mit oder ohne Zeilensprung
            $content .= (sizeof($lines)-2 > $i) ? $lineStriped."\n" : $lineStriped ;
        }
        // Veränderte Events in events.txt schreiben
        // Alle illegalen Chars, die Probleme verursachen könnten, werden gelöscht
        files("events.txt", str_replace(array('\\','/',':','*','?','"','<','>','|',','), '', $content), 'w');
        // header(); funktioniert auf dem Server nicht, header-Informationen bereits festgelegt sind und das schwerer zu umgehen ist als einfach JS zu nutzen
        //reload Page
        echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
    }
    /*
    Aufbau des Strings:
      -Überschrift
      -Bearbeitbarer Paragraph
      -Unsichtbare FORM, welche von JS versendet wird
      -Button der Js-Funktion triggert
      -JS
        - Funktion
        {
          - Inhalt des Paragraphen in Variable speichern
          - Inhalt des Paragraph-Inputs in der Form mit der Var überschreiben
          - Form absenden
        }
    */
    echo'<h1>'.$titleOfBackend.'</h1>
     <p contenteditable="true" id="paragraph" style="width:70%; margin: 0 15%; border: 2px solid var(--c-h1); border-radius: 24px;"> '.$events.' </p>
     <form class="contentForm" action="" method="post" id="create-content" style="">
       <input type="text" placeholder="paragraph" name="paragraph" id="paragraphForm" required style="display:none;">
       <input type="text" name="action" value="contentPrepare" required style="display:none;">
       <button type="submit" name="events" style="display:none;">x</button>
     </form>
     <span class="contentForm"><button name="button" onclick="getContent();">Abschicken</button></span>

     <script type="text/javascript">
     "use strict";
     function getContent() {
       let paragraph = document.getElementById("paragraph").innerHTML;
       document.getElementById("paragraphForm").value = paragraph;
       document.forms["create-content"].submit();
     }
     </script>';
}
function lists($titleOfBackend)
{
    require("staticVars.php");

    echo "<h1><u>$titleOfBackend</u></h1>";
    if (isset($_POST["send"])) {// Wenn Auswahl der Liste schon getroffen, dann:
        // Den Eventnamen aus Event.txt lesen
        foreach (fileToArray('events.txt') as $key) {
            $t = explode("-", $key, 2);// An "-" Spalten
          if ($t[0] == $_POST["event"]) { //Nur den ausgewählten Namen speichern
              $eventName = $t[1];
          }
        }
        $linkToEvent = $linkToTab.preg_replace('/\s+/', '', $_POST["event"]);
        //Überschrift
        echo "<h1>Teilnehmerliste von $eventName</h1><center>";
        echo '<br><br><a href="index.php?show=lists">Zurück zur Auswahl</a></center>';


        if (file_exists($linkToEvent.".json")) {// Wenn Tabelle existiert, dann
            //download-Link
            $json = json_decode(file_get_contents($linkToEvent.".json"), true);
            $stimmen = array("Sopran","Alt","Tenor","Bass");
            $csv = "";
            foreach ($stimmen as $stimme) {
                $csv .= $stimme."\n";
                foreach ($json[$stimme] as $number =>$array) {
                    foreach ($array as $bezeichnug =>$wert) {
                        $csv .= ",".$wert;
                    }
                    $csv .= "\n";
                }
            }
            files($linkToEvent.".csv", $csv, 'w');
            echo '<center><a href="'.$linkToEvent.'.csv" style="font-size: 0.8em;" download>.CSV-Datei zum download</a></center>';

            // öffnet die .CSV
            // Tabelle mit alle Werten erstellen
            echo '<table border="1" style="width: 60%; margin: 50px 20%;">';
            $stimmen = array("Sopran","Alt","Tenor","Bass");
            foreach ($stimmen as $stimme) {
                echo"<tr><td style='text-align:center'>$stimme:</td></tr>";
                foreach ($json[$stimme] as $number =>$array) {
                    echo"<tr><td></td>";
                    foreach ($array as $bezeichnug =>$wert) {
                        echo"<td style='text-align:center'>$wert</td>";
                    }
                    echo"</tr>";
                }
            }
            echo '</table>';
        } else { // Falls Tabelle nicht existiert:
            echo "<center>Diese Tabelle ($eventName) existiert noch nicht <br>keine Einträge im Ordner $eventName<br><br> <a href='index.php?show=lists'>Zurück zur Listenübersicht</a></center>";
        }
    } else { // Wenn noch keine Auswahl für die anzuzeigende Tabelle gesendet wurde:
        // Form mit select und allen Events aus event.txt
        echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
        // Events aus event.txt lesen!
        $lable = $nr = "";
        foreach (fileToArray("events.txt") as $line) {
            $lable .= ($lable != "") ? "," : "";
            $nr .= ($nr != "") ? "," : "";
            // Lable und Name spliten und in verschiedene Variablen
            $t = explode("-", $line, 2);
            $nr .= $t[0];
            $lable .= $t[1];
        }
        // Option-Tag mit Funktion erstellen
        echo xsvToOption(",", $nr, "", $lable);
        echo '</select><button type="submit" name="send" >Ausgeben</button></form>';
        listAllFilesOf(getcwd().$sep, $linkToTab);
    }
}


function deleteList($titleOfBackend)
{
    require("staticVars.php");

    echo "<h1>$titleOfBackend</h1>";
    if (isset($_POST["delete"])) {// Wenn Auswahl schon getroffen und bestätigt:
        // $feedback wird je nach Ausgang verschieden beschrieben!
        // Check ob Datei(en) überhaupt existieren
      if (file_exists($linkToTab.preg_replace('/\s+/', '', $_POST["datei"]))) {//&& file_exists($linkToTab.$_POST["datei"].".txt")) {
          // Versuch des Löschens der Datei
          if (unlink($linkToTab.preg_replace('/\s+/', '', $_POST["datei"]))) {// && unlink($linkToTab.$_POST["datei"].".txt")) {
              $feedback = "Datei erfolgreich gelöscht!";
          } else {
              $feedback =  "Fehler beim löschen!!";
          }
      } else {
          $feedback = "Dateien haben nicht existiert";
      }
        // Zurück-Link
        echo "<center>".$feedback."<br><br><a href='index.php?show=deleteList'>Zurück zum Listen-löschen</a><br></center>";
    } elseif (isset($_POST["send"])) { // Wenn Auswahl schon getroffen und noch nicht bestätigt:
        // Form mit Bestätigung ob Datei wirklich gelöscht werden so
        echo '<center>Willst du '.$_POST["event"].' wirklich löschen?
            <form action="" class="contentForm" method="post">
             <input type="text" name="datei" value="'.$_POST["event"].'" style="display:none;">
             <button type="submit" name="delete">Löschen bestätigen!</button>
           </form></center>';
    } elseif (isset($files)) {
    } else {
        // Events aus event.txt lesen!
        $lable = $nr = $files = "";
        foreach (fileToArray("events.txt") as $line) {
            $lable .= ($lable != "") ? "," : "";
            $nr .= ($nr != "") ? "," : "";
            // Lable und Name spliten und in verschiedene Variablen
            $t = explode("-", $line, 2);
            $nr .= $t[0];
            $lable .= $t[1];
        }
        // Anzeigen der verbliebenen Dateien
        $myDirectory = opendir($linkToTab);
        // Alle Inhalte des Dirs (/tabellen)
        while ($entryName = readdir($myDirectory)) {
            if (is_file($linkToTab.$entryName)) {
                $files .= (!empty($files)) ? ",".$entryName : $entryName ;
            }
        }
        closedir($myDirectory);
        if (!empty($files)) {
            echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
            echo xsvToOption(",", $files);
            echo '</select><button type="submit" name="send" >Löschen</button></form>';
        } else {
            echo "<center>Keine Dateien mehr verfügbar!</center>";
        }
    }
    listAllFilesOf(getcwd().$sep, $linkToTab);
}

function setup($titleOfBackend)
{
    require("staticVars.php");

    echo "<h1>$titleOfBackend</h1><br>";
    if (isset($_POST["setupUpdate"])) {
        foreach (fileToArray("setup.txt") as $line) {
            $t = explode("--", $line);
            $label[] = $t[0];
            $varKind[] = $t[1];
            // Zeilenbrüche durch <br> ersetzten
            $content[] = rtrim(preg_replace("/\r\n|\r|\n/", '<br>', $_POST[$t[0]]), "<br>");
        }
        $contentString = "";
        for ($i=0; $i < sizeof($label); $i++) {
            $contentString .= ($i < sizeof($label)-1) ? $label[$i]."--".$varKind[$i]."--".$content[$i]."\n" : $label[$i]."--".$varKind[$i]."--".$content[$i];
        }
        files("setup.txt", $contentString, 'w');
        echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
    } else {
        $form = '<center><form action="" method="post">';
        foreach (fileToArray("setup.txt") as $line) {
            $t = explode("--", $line);
            //<br> zurück zu Zeilensprüngen
            $t[2] = str_replace('<br>', "\n", $t[2]);
            switch ($t[1]) {
          case 'int':
            $form .= "<label for=".$t[0]." style='align:center;'>".$t[0]."</label> <input type='number' name=".$t[0]." value=".$t[2].">";
            break;
          case 'text':
            // Berechnung für die Höhe der textarea
            //temp ist ca. die Zeilenanzahl gemessen an der Zeichenanzahl
            $temp = ceil(strlen($t[2])/70)."em";
            // Wird dann als Style in den String geschrieben
            $height = "height: calc($temp + 50px + 1em);";
            $form .= "<label for=".$t[0]." style='align:center;'>$t[0]</label><div id='tArea'> <textarea class='auto-resize' style='$height'  name=$t[0]>$t[2]</textarea></div>";
            break;
          default:
            $form .= "<label for=".$t[0]." style='align:center;'>".$t[0]."</label><input type='text' name=".$t[0]." value='".$t[2]."'>";
            break;
        }
        }
        if (isset($height)) {
            // JS script, welches die TAs automatisch vergrößert wenn mehr Text dazu-kommt
            $form .= "<script>let multipleFields=document.querySelectorAll('.auto-resize');
                  for(var i=0; i<multipleFields.length; i++){
                  multipleFields[i].addEventListener('input',autoResizeHeight,0);
                  }
                  function autoResizeHeight(){
                    this.style.height='auto';
                    this.style.height= this.scrollHeight+'px';
                    this.style.borderColor='green';
                  }</script>";
        }
        echo $form .= '<button type="submit" name="setupUpdate">Absenden!</button></form></center>';
    }
}

function addFrontendUser($titleOfBackend)
{
    require("staticVars.php");
    echo "<h1>$titleOfBackend</h1><br>";
    if (isset($_POST["add"])) {
        // an das existierende htpasswd den User-Input appenden
        files("..".$sep.'.htpasswd', $_POST["user"], 'a');
        echo '<center>User erfolgreich hinzugefügt!</center>';
    } else {
        // Form mit einem input
        echo '<center>User-Passwort-Paar mit <a href="https://htpasswdgenerator.de/" target=_blank>htpasswdgenerator.de</a> erstellen. <br>
    Paar kopieren in untenliegendes Feld eintragen und absenden.</center>
    <form action="" method="post">
    <input type="text" name="user" placeholder="user:passwort"required>
    <button type="submit" name="add">Hinzufügen</button></form>';
    }
    echo'<div style="margin: 3em 20%;"> Folgende Nutzer sind registriert:<ul style="margin: 0.8em 1em;">';
    // Alle existierenden User Ausgeben (aus Htpasswd lesen)
    foreach (fileToArray("..".$sep.".htpasswd") as $line) {
        // An ":" spalten und nur namen ausgeben
        $t = explode(":", $line);
        echo "<li>".$t[0]."</li>";
    }
    echo"</ul></div>";
}
function addBackendUser($titleOfBackend)
{
    require("staticVars.php");
    echo "<h1>$titleOfBackend</h1><br>";
    if (isset($_POST["add"])) {
        // an das existierende htpasswd den User-Input appenden
        $writeString = StringLengthStrip($_POST["user"], 512)."::".password_hash(StringLengthStrip($_POST["password"], 512), PASSWORD_DEFAULT)."\n";
        files('backendAccounts', $writeString, 'a');
        echo '<center>User erfolgreich hinzugefügt!</center>';
    } else {
        // Form mit einem input
        echo '<center><form action="" class="input-box" method="post">
    <input type="text" name="user" placeholder="Username" required>
    <input type="password" name="password" placeholder="Passwort" required>
    <button type="submit" name="add" >Hinzufügen</button></form></center>';
    }
    echo'<div style="margin: 3em 20%;"> Folgende Nutzer sind registriert:<ul style="margin: 0.8em 1em;">';
    // Alle existierenden User Ausgeben (aus Htpasswd lesen)
    foreach (fileToArray("backendAccounts") as $line) {
        // An ":" spalten und nur namen ausgeben
        $t = explode("::", $line);
        echo "<li>".$t[0]."</li>";
    }
    echo"</ul></div>";
}

function deleteEntry($titleOfBackend)
{
    require("staticVars.php");
    echo "<h1><u>$titleOfBackend</u></h1>";
    if (isset($_POST["stimme"])) {
        $json = json_decode(file_get_contents($_POST["event"].".json"), true);
        if (isset($json[$_POST["stimme"]][$_POST["Name"]])) {
            unset($json[$_POST["stimme"]][$_POST["Name"]]);
            $feedback = "Eintrag (".$_POST['stimme'].") aus ".$_POST['event'].".json  erfolgreich gelöscht";
        } else {
            $feedback = "Beim löschen des Eintrags (".$_POST['stimme'].") aus ".$_POST['event'].".json gab es einen Fehler!";
        }
        files($_POST["event"].".json", json_encode($json), 'w');
    }
    if (isset($_POST["send"])) {// Wenn Auswahl der Liste schon getroffen, dann:

        // Den Eventnamen aus Event.txt lesen
        foreach (fileToArray('events.txt') as $line) {
            $t = explode("-", $line, 2);// An "-" Spalten
            if ($t[0] == $_POST["event"]) { //Nur den ausgewählten Namen speichern
                $eventName = $t[1];
            }
        }
        $linkToEvent = $linkToTab.preg_replace('/\s+/', '', $_POST["event"]);

        //Überschrift
        echo "<h1>Teilnehmerliste von $eventName</h1><center>";
        echo '<br><br><a href="index.php?show=deleteEntry">Zurück zur Auswahl</a><br><br></center>';
        if (file_exists($linkToEvent.".json")) {// Wenn Tabelle existiert, dann
            $json = json_decode(file_get_contents($linkToEvent.".json"), true);
            // Tabelle mit alle Werten erstellen
            echo '<center>';
            $stimmen = array("Sopran","Alt","Tenor","Bass");
            foreach ($stimmen as $stimme) {
                echo '<form action="" method="post"><table style="width:60%;">
              <input type="text" name="event" style="display:none;" value="'.$linkToEvent.'">
              <input type="text" name="stimme" style="display:none;" value="'.$stimme.'">';
                echo"<tr><td style='text-align:center'>$stimme:</td></tr>";
                foreach ($json[$stimme] as $number =>$array) {
                    echo"<tr><td></td>";
                    foreach ($array as $bezeichnug =>$wert) {
                        echo"<td style='text-align:center'>$wert</td>";
                    }
                    echo"<td style='text-align:center'><button type='submit' name='Name' value='$number'>löschen</button></td></tr>";
                }
                echo "</table></form>";
            }
            echo '</center>';
        } else { // Falls Tabelle nicht existiert:
            echo "<center>Diese Tabelle ($eventName) existiert noch nicht <br>keine Einträge im Ordner $eventName<br><br> <a href='index.php?show=lists'>Zurück zur Listenübersicht</a></center>";
        }
    } else { // Wenn noch keine Auswahl für die anzuzeigende Tabelle gesendet wurde:
        // Form mit select und allen Events aus event.txt
        echo (isset($feedback))?"<br><h1>".$feedback."</h1><br>":"";
        echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
        // Events aus event.txt lesen!
        $lable = $nr = "";
        foreach (fileToArray("events.txt") as $line) {
            // für richtige Komma-Setzung
            $lable .= ($lable != "") ? "," : "";
            $nr .= ($nr != "") ? "," : "";
            // Lable und Name spliten und in verschiedene Variablen
            $t = explode("-", $line, 2);
            $nr .= $t[0];
            $lable .= $t[1];
        }
        // Option-Tag mit Funktion erstellen
        echo xsvToOption(",", $nr, "", $lable);
        echo '</select><button type="submit" name="send" >Ausgeben</button></form>';
    }
}
function inputconfig($titleOfBackend)
{
    require("staticVars.php");

    if (isset($_POST["deleteField"])) {
        $name = $_POST["deleteField"];
        $nameWithUnderscore = str_replace(' ', '_', $_POST["deleteField"]);
        if (isset($_POST[$nameWithUnderscore.":deleteVerify"])) {
            if ($_POST[$nameWithUnderscore.":deleteVerify"] == $_POST["deleteField"]) {
                $json = json_decode(file_get_contents("inputfelder.txt"), true);
                if ($_POST[$nameWithUnderscore.":kindOfField"] == "input") {
                    unset($json["input"][$name]);
                    $feedback = '"'.$name.'" wurde erforgreich gelöscht';
                }
                files("inputfelder.txt", json_encode($json), 'w');
            }
        } else {
            $feedback = 'Löschen von "'.$_POST["deleteField"].'" wurde nicht bestätigt!'; // FEEDBACK
        }
        echo "<h1>$feedback</h1>";
    }
    if (isset($_POST["send"])) {
        $json = array();
        foreach ($_POST as $name => $value) {
            $split = explode(":", $name);
            if (count($split)>1) {
                $name = str_replace('_', ' ', $split[0]);
                $param = $split[1];
                if (!isset($tempArray)) {
                    $tempArray = array();
                    $newName = $value;
                } elseif ($param == "kindOfField") {
                    $json[$value][$newName] = $tempArray;
                    unset($tempArray);
                } elseif ($param != "deleteVerify") {
                    $tempArray[$param] = $value;
                }
            }
        }
        files("inputfelder.txt", json_encode($json), 'w');
    }
    if (isset($_POST["addInput"])) {
        if (!empty($_POST["newInputTilte"])||!empty($_POST["newInputTilte1"])) {
            $title = (!empty($_POST["newInputTilte"]))?$_POST["newInputTilte"]:$_POST["newInputTilte1"];
            $json = json_decode(file_get_contents("inputfelder.txt"), true);
            $json["input"][$title] = array('type'=>'text','required'=>'false','label'=>'');
            files("inputfelder.txt", json_encode($json), 'w');
        }
    }
    if (!isset($_POST["send"])) {
        $json = json_decode(file_get_contents("inputfelder.txt"), true);
        //Für alle inputs
        echo'<center><h1>'.$titleOfBackend.'</h1><form action="" method="post"><button type="submit" name="send" >Ändern</button>';
        echo "<div style='width: 60%; border: 3px solid var(--c-link); border-radius: 24px; margin: 2em; padding: 1em;' ><h1>Neues Feld hinzufügen</h1>";
        echo '<input type="text" name="newInputTilte" placeholder="Name des Neuen Felds" >';
        echo '<button type="submit" name="addInput">Neues Feld hinzufügen</button></div>';
        foreach ($json["input"] as $key => $val) {
            echo "<div style='width: 60%; border: 3px solid var(--c-link); border-radius: 24px; margin: 2em; padding: 1em;' ><b><u>$key</u></b> <br>";

            echo '<label for="'.$key.':name">Name des Felds</label>';
            echo '<input type="text" name="'.$key.':name" placeholder="'.$key.':name" required '.((!empty($json["input"][$key]))?'value="'.$key.'"':'').'>'; //Label

            echo '<label for="'.$key.':type">Art des Felds</label>';
            echo '<select name="'.$key.':type" required>';
            $inputTypes = "text,tel,email,number,password,button,checkbox,color,date,file,hidden,image,month,radio,range,reset,search,submit,time,url,week";
            $inputTypesLabels = "Text,Telefonnummer,E-Mail,Zahl,Passwort,Knopf,Checkbox,Farbe,Datum,Datei,Versteckt,Bild,Monat,Radio-Checkbox,Slider,Zurücksetzen,Suchen,Absenden,Zeit,URL,Woche";
            echo (!empty($json["input"][$key]["type"])) ? xsvToOption(",", $inputTypes, $json["input"][$key]["type"], $inputTypesLabels): xsvToOption(",", $inputTypes, "", $inputTypesLabels);
            echo'</select>';

            echo '<label for="'.$key.':required">Feld benötigt / freiwillig</label>';
            echo '<select name="'.$key.':required" required>';
            echo (filter_var($json["input"][$key]["required"], FILTER_VALIDATE_BOOLEAN))? xsvToOption(",", "true,false", "true", "benötigt,freiwillig"): xsvToOption(",", "true,false", "false", "benötigt,freiwillig");
            echo'</select>';

            echo '<label for="'.$key.':label">Label</label>';
            echo '<input type="text" name="'.$key.':label" placeholder="'.$key.':label" '.((!empty($json["input"][$key]["label"]))?'value="'.$json["input"][$key]["label"].'"':'').'>'; //Label

            echo"<div style='margin: 0 10% 1em 10%; border: 3px solid rgb(156, 30, 30); border-radius: 24px;'> <u>$key löschen?</u><br>";
            echo '<input type="checkbox" name="'.$key.':deleteVerify" value="'.$key.'"><label for="'.$key.':deleteVerify"> "'.$key.'" wirklich löschen?</label><br>';
            echo '<input type="text" name="'.$key.':kindOfField" value="input" style="display:none;">';
            echo '<button type="submit" name="deleteField" value="'.$key.'" style="border: 2px solid rgb(156, 30, 30);">"'.$key.'" löschen</button>';
            echo"</div></div>";
        }
        echo "<div style='width: 60%; border: 3px solid var(--c-link); border-radius: 24px; margin: 2em; padding: 1em;' ><h1>Neues Feld hinzufügen</h1>";
        echo '<input type="text" name="newInputTilte1" placeholder="Name des Neuen Felds" >';
        echo '<button type="submit" name="addInput">Neues Feld hinzufügen</button></div>';

        echo '<button type="submit" name="send" >Ändern</button></form></center>';
    } else {
        echo "<center>erfolgreich geändert <br> <a href='index.php?show=inputconfig'>Zurück zum Seite</a></center>";
    }
}
