<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once("login.php");

$titleOfBackend = "Backend - LogIn";

if ($authed == true) {
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
        case 'addFrontendUser':
          $titleOfBackend = "Backend - Nutzer hinzufügen (Frontend)";
          break;
        case 'addBackendUser':
          $titleOfBackend = "Backend - Nutzer hinzufügen (Backend)";
          break;
        case 'deleteEntry':
          $titleOfBackend = "Backend - Eintrag löschen";
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
}
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
    <?php
if ($authed == true) {
    //require_once("backendFunctions.php");
    echo '
    <style>.nav-div{
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
    }</style>
  <center style="margin: 20px 0 0 0;">
    <!--LOGO-->
    <a href="../"><img src="../Musik-Stempel rund.png" alt="Logo" ></a>
    <!--NAV-->
    <div class="nav-div">
      <a href="index.php?show=events">Events bearbeiten</a>
      <a href="index.php?show=lists">Listen ausgeben</a>
      <a href="index.php?show=deleteList">Liste löschen</a>
      <a href="index.php?show=setup">Einstellungen</a>
      <a href="index.php?show=addFrontendUser">Nutzer hinzufügen (Frontend)</a>
      <a href="index.php?show=addBackendUser">Nutzer hinzufügen (Backend)</a>
      <a href="index.php?show=deleteEntry">Eintrag Löschen</a>
      <a href="index.php?show=logOut">LogOut</a>
    </div>
  </center>
 <div class="pageBody">
';
    if (isset($_GET["show"])) { // Je nach Menü-Auswahl werden verschiedene Sachen gezeigt
  if ($_GET["show"] == "events") { // Events bearbeiten
<<<<<<< HEAD
    //events($_POST);
=======
    events($_POST);
>>>>>>> 6c95afbc97eeb11a6ee44503397a711dcbbf2262
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
          echo '<br><br><a href="index.php?show=lists">Zurück zur Auswahl</a></center>';


          if (file_exists($linkToEvent.".json")) {// Wenn Tabelle existiert, dann
              //download-Link
              $json = json_decode(file_get_contents($linkToEvent.".json"), true);
              $stimmen = array("Sopran","Alt","Tenor","Bass");
              $csv = "";
              foreach ($stimmen as $stimme) {
                  $csv .= $stimme."\n";
                  foreach ($json[$stimme] as $key =>$name) {
                      $csv .= ",".$key.",".$name."\n";
                  }
              }
              $csvNew = fopen($linkToEvent.".csv", 'w');
              fwrite($csvNew, $csv);
              fclose($csvNew);

              echo '<center><a href="'.$linkToEvent.'.csv" style="font-size: 0.8em;" download>.CSV-Datei zum download</a></center>';

              // öffnet die .CSV
              $json = json_decode(file_get_contents($linkToEvent.".json"), true);
              // Tabelle mit alle Werten erstellen
              echo '<table border="1" style="width: 60%; margin: 50px 20%;">';
              $stimmen = array("Sopran","Alt","Tenor","Bass");
              foreach ($stimmen as $stimme) {
                  echo"<tr><td style='text-align:center'>$stimme:</td></tr>";
                  foreach ($json[$stimme] as $key =>$name) {
                      echo"<tr><td></td><td style='text-align:center'>$key</td><td style='text-align:center'>$name</td></tr>";
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
          // Anzeigen der verbliebenen Dateien
          $myDirectory = opendir($linkToTab);
          // Alle Inhalte des Dirs (/tabellen)
          $files = "";
          while ($entryName = readdir($myDirectory)) {
              if (is_file($linkToTab.$entryName)) {
                  if (!empty($files)) {
                      $files .= ",".$entryName;
                  } else {
                      $files = $entryName;
                  }
              }
          }
          if (!empty($files)) {
              echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
              echo xsvToOption(",", $files);
              echo '</select><button type="submit" name="send" >Löschen</button></form>';
          } else {
              echo "<center>Keine Dateien mehr verfügbar!</center>";
          }
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
  } elseif ($_GET["show"] == "addFrontendUser") { // Nutzer Hinzufügen
      echo "<h1>Nutzer hinzufügen (Frontend)</h1><br>";
      if (isset($_POST["add"])) {
          // an das existierende htpasswd den User-Input appenden
          $fh = fopen("..".$sep.'.htpasswd', 'a');
          fwrite($fh, $_POST["user"]);
          fclose($fh);
          echo '<center>User erfolgreich hinzugefügt!</center><div style="margin: 3em 20%;"> Folgende Nutzer sind registriert:<ul style="margin: 0.8em 1em;">';
          // Alle existierenden User Ausgeben
          // aus Htpasswd lesen
          $fh = fopen("..".$sep.".htpasswd", "r");
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
        <button type="submit" name="add">Hinzufügen</button></form>';
      }
  } elseif ($_GET["show"] == "addBackendUser") { // Nutzer Hinzufügen
      echo "<h1>Nutzer hinzufügen (Backend)</h1><br>";
      if (isset($_POST["add"])) {
          // an das existierende htpasswd den User-Input appenden
          $writeString = $_POST["user"]."::".$_POST["password"];
          $fh = fopen('backendAccounts', 'a');
          fwrite($fh, $writeString);
          fclose($fh);
          echo '<center>User erfolgreich hinzugefügt!</center><div style="margin: 3em 20%;"> Folgende Nutzer sind registriert:<ul style="margin: 0.8em 1em;">';
          // Alle existierenden User Ausgeben
          // aus Htpasswd lesen
          $fh = fopen("backendAccounts", "r");
          while ($line = fgets($fh)) {
              // An ":" spalten und nur namen ausgeben
              $t = explode("::", $line);
              echo "<li>".$t[0]."</li>";
          }
          echo"</ul></div>";
          fclose($fh);
      } else {
          // Form mit einem input
          echo '<center><form action="" class="input-box" method="post">
        <input type="text" name="user" placeholder="Username" required>
        <input type="password" name="password" placeholder="Passwort" required>
        <button type="submit" name="add" >Hinzufügen</button></form></center>';
      }
  } elseif ($_GET["show"]=="deleteEntry") {
      echo "<h1><u>$titleOfBackend</u></h1>";
      if (isset($_POST["stimme"])) {
          $json = json_decode(file_get_contents($_POST["event"].".json"), true);
          if (isset($json[$_POST["stimme"]][$_POST["Name"]])) {
              unset($json[$_POST["stimme"]][$_POST["Name"]]);
              $feedback = $_POST['Name']." (".$_POST['stimme'].") aus ".$_POST['event'].".json  erfolgreich gelöscht";
          } else {
              $feedback = "Beim löschen von ".$_POST['Name']." (".$_POST['stimme'].") aus ".$_POST['event'].".json gab es einen Fehler!";
          }
          $csvNew = fopen($_POST["event"].".json", 'w');
          fwrite($csvNew, json_encode($json));
          fclose($csvNew);
      }
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
                  foreach ($json[$stimme] as $key =>$name) {
                      echo"<tr><td></td><td style='text-align:center'>$key</td><td style='text-align:center'>$name</td><td style='text-align:center'><button type='submit' name='Name' value='$key'>$key löschen</button></td></tr>";
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
      }
  }
    }
} else {
    showLogin(isset($_GET["show"]));
}
?>
<!--Fixer Zurückknopf-->
<span style="position: fixed; bottom: 0px; margin-bottom: 0px; float:right; margin:0 10% 5vh 75%;"><a href="index.php">Zurück zur Übersicht</a></span>
<br><br>

</div>
</body>
</html>
