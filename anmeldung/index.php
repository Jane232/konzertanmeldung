<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//Einbindung der Funktionen
require_once("../functions.php");
// Variable für HTML-Titel der Seite
$titleOfBackend = "Backend - Übersicht";
if (isset($_GET["show"])) {
    switch ($_GET["show"]) {
      case 'events':
        $titleOfBackend = "Backend - Events bearbeiten";
        break;
      case 'lists':
        $titleOfBackend = "Backend - Listen ausgeben";
        break;
      case 'deleteList':
        $titleOfBackend = "Backend - Listen löschen";
        break;
      case 'setup':
        $titleOfBackend = "Backend - Einstellungen";
        break;
        case 'user':
          $titleOfBackend = "Backend - Nutzer hinzufügen";
          break;
        case 'inputconfig':
          $titleOfBackend = "Backend - Eingabefelder bearbeiten";
          break;
    }
}

// korekte URL
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
$url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

// '/' oder '\'
$sep = DIRECTORY_SEPARATOR;
// Name des Sub-Ordners
$subfolder = "tabellen";
// Link zu Sub-Ornder
$linkToTab = $subfolder.$sep;
?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo $titleOfBackend; ?> </title>
   <link rel="stylesheet" href="../style.css">
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>
  <body>
    <!--Etwas CSS für das NAV-->
    <style media="screen">
      .nav-div{
        width: 80%;
        display: grid;
        grid-template-columns: auto auto auto auto;
      }
      .nav-div a{
        margin: 1em 0;
      }
      @media screen and (max-width: 900px)
      {
        .nav-div{
          grid-template-columns: auto auto ;
        }
        .nav-div a{
          margin: 0.5em 0;
        }
      }
    </style>
    <center style="margin: 20px 0 0 0;">
      <!--LOGO-->
      <a href="../"><img src="../Musik-Stempel rund.png" alt="Logo" ></a>
      <!--NAV-->
      <div class="nav-div">
        <a href="index.php?show=events">Events bearbeiten</a>
        <a href="index.php?show=lists">Listen ausgeben</a>
        <a href="index.php?show=deleteList">Liste löschen</a>
        <a href="index.php?show=setup">Einstellungen</a>
        <a href="index.php?show=user">Nutzer hinzufügen</a>
        <a href="index.php?show=inputconfig">Eingabefelder bearbeiten</a>
      </div>
    </center>
   <div class="pageBody">
<?php
if (isset($_GET["show"])) { // Je nach Menü-Auswahl werden verschiedene Sachen gezeigt
  if ($_GET["show"] == "events") { // Events bearbeiten
    // Auslesen der aktuellen Events und Events mit <br> trennen um jeweils eine neue Zeile zu haben
    $fh = fopen('events.txt', 'r');
      $events ="";
      while ($line = fgets($fh)) {
          $t = explode("-", $line);
          $events .= $line."<br>";
      }
      fclose($fh);
      // $_POST["paragraph"] ist nur gesetzt wenn User etwas abgeschickt hat (enthält die (veränderten) Events)
      if (isset($_POST["paragraph"])) {
          // Aufspalten an den Zeilen
          $lines = explode('<br>', $_POST["paragraph"]);
          $content = "";
          // Über Array iterieren
          for ($i=0; $i < sizeof($lines)-1; $i++) {
              // An "-" Spalten
              $name = explode('-', $lines[$i]);
              // Alle whitespace-Chars entfernen die im Namen sind
              $lineStriped = str_replace(' ', '', preg_replace('/\s+/', '', $name[0]))."-".$name[1];
              //Zusammenfügen mit oder ohne Zeilensprung
              if (sizeof($lines)-1 > $i + 1) {
                  $content .= $lineStriped."\n";
              } else {
                  $content .= $lineStriped;
              }
          }
          // Veränderte Events in events.txt schreiben
          $fp = fopen("events.txt", 'w');
          // Alle illegalen Chars, die Probleme verursachen könnten, werden gelöscht
          fwrite($fp, str_replace(array('\\','/',':','*','?','"','<','>','|',','), '', $content));
          fclose($fp);
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
      echo'<h1>Events zum bearbeiten</h1>
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
  } elseif ($_GET["show"] == "lists") { //Liste ausgeben
      echo "<h1><u>Listen ausgeben</u></h1>";
      if (isset($_POST["send"])) {// Wenn Auswahl der Liste schon getroffen, dann:
          // Den Eventnamen aus Event.txt lesen
          $fh = fopen('events.txt', 'r');
          $events ="";
          // Über alle Zeilen iterieren
          while ($line = fgets($fh)) {
              $t = explode("-", $line);// An "-" Spalten
              if ($t[0] == $_POST["event"]) { //Nur den ausgewählten Namen speichern
                  $eventName = $t[1];
              }
          }
          fclose($fh);
          $linkToEvent = $linkToTab.preg_replace('/\s+/', '', $_POST["event"]);

          //Überschrift
          echo "<h1>Teilnehmerliste von $eventName</h1><center>";
          $FeedbackOfEntries = getLines($linkToEvent);
          echo $res = ($FeedbackOfEntries>1) ? "(Insgesamt $FeedbackOfEntries Einträge)" : (($FeedbackOfEntries == 1)?"($FeedbackOfEntries Eintrag)":"");
          echo '<br><br><a href="index.php?show=lists">Zurück zur Auswahl</a></center>';

          // Wenn ein Dir zu dem Event extistiert
          if (is_dir($linkToEvent)) {
              $myDirectory = opendir($linkToEvent);
              // Alle Inhalte des Dirs (/tabellen)
              $files = [];
              while ($entryName = readdir($myDirectory)) {
                  // Filtern, dass nur Dateien angezeigt werden (Keine Ordner)
                  if (is_file($linkToEvent.$sep.$entryName)) {
                      $files[] = $entryName;
                  }
              }
              closedir($myDirectory);
              // Zeilen des Normalen .csv docs zum Vergleich
              $linesOfEvent = getLines($linkToEvent);
              // Feedback zum Ausgeben
              $feedback = (sizeof($files) == $linesOfEvent) ? "Gleiche Zeilenanzahl" : "Andere Zahlenanzahl (".sizeof($files)." - $linesOfEvent)";

              //mögliches löschen einer alten Version des (viaSingleFileRead).csv
              $fileDelete = fopen($linkToEvent."(viaSingleFileRead).csv", 'w');
              fwrite($fileDelete, "");
              fclose($fileDelete);

              //Erneutes öffnen des (viaSingleFileRead).csv
              $fileAppend = fopen($linkToEvent."(viaSingleFileRead).csv", 'a');
              // Über alle Dateien iterieren und alle Zeilen (sollten nur eine sein) in (viaSingleFileRead).csv eintragen
              foreach ($files as $file) {
                  fwrite($fileAppend, file_get_contents($linkToEvent.$sep.$file));
              }
              fclose($fileAppend);
          } else {
              $feedback = "Ordner existiert noch nicht!";
          }

          if (file_exists($linkToEvent.".csv")) {// Wenn Tabelle existiert, dann
              //download-Link
              echo '<center><a href="'.$linkToEvent.'.csv" style="font-size: 0.8em;" download>.CSV-Datei zum download</a></center>';
              echo ($feedback != "Ordner existiert noch nicht!")?'<center><a href="'.$linkToEvent.'(viaSingleFileRead).csv" style="font-size: 0.8em;" download> BackUp-CSV-Datei zum download</a> ('.$feedback.')</center>' : "<center>$feedback</center>";

              // öffnet die .CSV
              if (($handle = fopen($linkToEvent.".csv", "r")) !== false) {
                  // Tabelle mit alle Werten erstellen
                  echo '<table border="1" style="width: 60%; margin: 50px 20%;">';
                  while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                      $num = count($data);
                      echo '<tr>';
                      for ($c=0; $c < $num; $c++) {
                          if (empty($data[$c])) {
                              $value = "&nbsp;";
                          } else {
                              $value = $data[$c];
                          }
                          echo '<td style="text-align:center">'.$value.'</td>';
                      }
                      echo '</tr>';
                  }
                  echo '</table>';
                  fclose($handle);
              }
          } else { // Falls Tabelle nicht existiert:
              echo "<center>Diese Tabelle ($eventName) existiert noch nicht <br>keine Einträge im Ordner $eventName<br><br> <a href='index.php?show=lists'>Zurück zur Listenübersicht</a></center>";
          }
      } else { // Wenn noch keine Auswahl für die anzuzeigende Tabelle gesendet wurde:
          // Form mit select und allen Events aus event.txt
          echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
          // Events aus event.txt lesen!
          $lable = $nr = "";
          $fh = fopen("events.txt", "r");
          // Über alle Event-Zeilen iterieren
          while ($line = fgets($fh)) {
              // für richtige Komma-Setzung
              if ($lable != "" ||$nr != "") {
                  $lable .= ",";
                  $nr .= ",";
              }
              // Lable und Name spliten und in verschiedene Variablen
              $t = explode("-", $line);
              $nr .= $t[0];
              $lable .= $t[1];
          }
          fclose($fh);
          // Option-Tag mit Funktion erstellen
          echo xsvToOption(",", $nr, "", $lable);
          echo '</select><button type="submit" name="send" >Ausgeben</button></form>';
          listAllFilesOf(getcwd().$sep, $linkToTab);
      }
  } elseif ($_GET["show"] == "deleteList") {// Liste Löschen
      echo "<h1>Listen löschen</h1>";
      if (isset($_POST["delete"])) {// Wenn Auswahl schon getroffen und bestätigt:
          // $feedback wird je nach Ausgang verschieden beschrieben!

          // Check ob Datei(en) überhaupt existieren
          if (file_exists($linkToTab.preg_replace('/\s+/', '', $_POST["datei"]).".csv")) {//&& file_exists($linkToTab.$_POST["datei"].".txt")) {
              // Versuch des Löschens der Datei
              if (unlink($linkToTab.preg_replace('/\s+/', '', $_POST["datei"]).".csv")) {// && unlink($linkToTab.$_POST["datei"].".txt")) {
                  $feedback = "Datei erfolgreich gelöscht!";
              } else {
                  $feedback =  "Fehler beim löschen!!";
              }
          } else {
              $feedback = "Dateien haben nicht existiert";
          }
          if (is_dir($linkToTab.preg_replace('/\s+/', '', $_POST["datei"]))) {
              deleteDir($linkToTab.preg_replace('/\s+/', '', $_POST["datei"]));
          }
          if (file_exists($linkToTab.preg_replace('/\s+/', '', $_POST["datei"])."(viaSingleFileRead).csv")) {
              unlink($linkToTab.preg_replace('/\s+/', '', $_POST["datei"])."(viaSingleFileRead).csv");
          }
          // Zurück-Link
          echo "<center>".$feedback."<br><br><a href='index.php?show=deleteList'>Zurück zum Listen-löschen</a><br></center>";
      } elseif (isset($_POST["send"])) { // Wenn Auswahl schon getroffen und noch nicht bestätigt:
          // Dateinamen aus events.txt lesen
          $fh = fopen('events.txt', 'r');
          $events ="";
          while ($line = fgets($fh)) {
              $t = explode("-", $line);
              if ($t[0] == $_POST["event"]) {
                  $eventName = $t[1];
              }
          }
          fclose($fh);
          // Form mit Bestätigung ob Datei wirklich gelöscht werden so
          echo '<center>Willst du '.$eventName.' wirklich löschen?
                <form action="" class="contentForm" method="post">
                 <input type="text" name="datei" value="'.$_POST["event"].'" style="display:none;">
                 <button type="submit" name="delete">Löschen bestätigen!</button>
               </form></center>';
      } elseif (isset($files)) {
      } else {
          echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
          // Events aus event.txt lesen!
          $lable = $nr = "";
          $fh = fopen("events.txt", "r");
          // Über alle Event-Zeilen iterieren
          while ($line = fgets($fh)) {
              // für richtige Komma-Setzung
              if ($lable != "" ||$nr != "") {
                  $lable .= ",";
                  $nr .= ",";
              }
              // Lable und Name spliten und in verschiedene Variablen
              $t = explode("-", $line);
              $nr .= $t[0];
              $lable .= $t[1];
          }
          fclose($fh);
          echo xsvToOption(",", $nr, "", $lable);
          echo '</select><button type="submit" name="send" >Löschen</button></form>';
      }
      listAllFilesOf(getcwd().$sep, $linkToTab);
  } elseif ($_GET["show"]=="setup") { // Einstellungen
      echo "<h1>Einstellungen</h1><br>";
      if (isset($_POST["setupUpdate"])) {
          $fh = fopen("setup.txt", "r");
          while ($line = fgets($fh)) {
              $t = explode("--", $line);
              $label[] = $t[0];
              $varKind[] = $t[1];
              // Zeilenbrüche durch <br> ersetzten
              $content[] = rtrim(preg_replace("/\r\n|\r|\n/", '<br>', $_POST[$t[0]]), "<br>");
          }
          fclose($fh);
          $contentString = "";
          for ($i=0; $i < sizeof($label); $i++) {
              if ($i < sizeof($label)-1) {
                  $contentString .= $label[$i]."--".$varKind[$i]."--".$content[$i]."\n";
              } else {
                  $contentString .= $label[$i]."--".$varKind[$i]."--".$content[$i];
              }
          }
          $fp = fopen("setup.txt", 'w');
          fwrite($fp, $contentString);
          fclose($fp);
          echo "<script type='text/javascript'>document.location.href='{$url}';</script>";
      } else {
          $fh = fopen("setup.txt", "r");
          $form = '<center><form action="" method="post">';
          while ($line = fgets($fh)) {
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
          fclose($fh);
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
  } elseif ($_GET["show"] == "user") { // Nutzer Hinzufügen
      echo "<h1>Nutzer hinzufügen</h1><br>";
      if (isset($_POST["add"])) {
          // an das existierende htpasswd den User-Input appenden
          $fh = fopen('.htpasswd', 'a');
          fwrite($fh, $_POST["user"]);
          fclose($fh);
          echo '<center>User erfolgreich hinzugefügt!</center><div style="margin: 3em 20%;"> Folgende Nutzer sind registriert:<ul style="margin: 0.8em 1em;">';
          // Alle existierenden User Ausgeben
          // aus Htpasswd lesen
          $fh = fopen(".htpasswd", "r");
          while ($line = fgets($fh)) {
              // An ":" spalten und nur namen ausgeben
              $t = explode(":", $line);
              echo "<li>".$t[0]."</li>";
          }
          echo"</ul></div>";
          fclose($fh);
      } else {
          // Form mit einem input
          echo '<center>User-Passwort-Paar mit <a href="https://htpasswdgenerator.de/" target=_blank>htpasswdgenerator.de</a> erstellen. <br>
        Paar kopieren in untenliegendes Feld eintragen und absenden.</center>
        <form action="" method="post">
        <input type="text" name="user" placeholder="user:passwort">
        <button type="submit" name="add" >Hinzufügen</button></form>';
      }
  } elseif ($_GET["show"] == "inputconfig") {
      if (isset($_POST["deleteField"])) {
          $name = $_POST["deleteField"];
          $nameWithUnderscore = str_replace(' ', '_', $_POST["deleteField"]);
          if (isset($_POST[$nameWithUnderscore.":deleteVerify"])) {
              if ($_POST[$nameWithUnderscore.":deleteVerify"] == $_POST["deleteField"]) {
                  $json = json_decode(file_get_contents("inputfelder.txt"), true);
                  if ($_POST[$nameWithUnderscore.":kindOfField"] == "input") {
                      unset($json["input"][$name]);
                      $feedback = '"'.$name.'" wurde erforgreich gelöscht';
                  } else {
                      // Option oder ähnliche
                  }
                  $fp = fopen("inputfelder.txt", 'w');
                  fwrite($fp, json_encode($json));
                  fclose($fp);
              }
          } else {
              $feedback = 'Löschen von "'.$_POST["deleteField"].'" wurde nicht bestätigt!'; // FEEDBACK
          }
          echo "<h1>$feedback</h1>";
      }
      if (isset($_POST["send"])) {
          //var_dump($_POST);
          $json = array();
          foreach ($_POST as $name => $value) {
              if ($name != "send") {
                  $split = explode(":", $name);
                  $name = str_replace('_', ' ', $split[0]);
                  if (!isset($$name)) {
                      $$name = array();
                      $newName = $value;
                  } elseif ($split[1] == "kindOfField") {
                      $json[$value][$newName] = $$name;
                  } else {
                      if (isset($split[1]) && $split[1] != "deleteVerify") {
                          $$name[$split[1]] = $value;
                      }
                  }
              }
          }
          $fp = fopen("inputfelder.txt", 'w');
          fwrite($fp, json_encode($json));
          fclose($fp);
      }
      if (isset($_POST["addInput"])) {
          if (!empty($_POST["newInputTilte"])||!empty($_POST["newInputTilte1"])) {
              $title = (!empty($_POST["newInputTilte"]))?$_POST["newInputTilte"]:$_POST["newInputTilte1"];
              $json = json_decode(file_get_contents("inputfelder.txt"), true);
              $json["input"][$title] = array('type'=>'text','required'=>'false','label'=>'');
              $fp = fopen("inputfelder.txt", 'w');
              fwrite($fp, json_encode($json));
              fclose($fp);
          }
      }
      if (!isset($_POST["send"])) {
          $json = json_decode(file_get_contents("inputfelder.txt"), true);
          //Für alle inputs
          echo'<center><h1>Eingabefelder bearbeiten</h1><form action="" method="post"><button type="submit" name="send" >Ändern</button>';
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
}

?>
<!--Fixer Zurückknopf-->
<span style="position: fixed; bottom: 0px; margin-bottom: 0px; float:right; margin:0 10% 5vh 75%;"><a href="index.php">Zurück zur Übersicht</a></span>

<br><br>
  </div>
</body>
</html>
