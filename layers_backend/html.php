<?php
require_once(ROOT."layers_backend/process.php");
require_once(ROOT."layers/html.php");

function show($show)
{
    define_once("TITLE_OF_BACKEND", html_namesOfSites($_GET["show"]));
    switch ($show) {
      case 'events':
        html_events();
        break;
      case 'lists':
        html_lists();
        break;
      case 'deleteList':
        html_deleteList();
        break;
      case 'setup':
        html_setup();
        break;
      case 'addFrontendUser':
        html_addFrontendUser();
        break;
      case 'addBackendUser':
        html_addBackendUser();
        break;
      case 'deleteEntry':
        html_deleteEntry();
        break;
      case 'inputconfig':
        html_inputconfig();
        break;
      case 'logOut':
        // code...
        break;
  }
}
function html_namesOfSites($show)
{
    switch ($show) {
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
case 'inputconfig':
  $titleOfBackend = "Backend - Eingabefelder bearbeiten";
  break;
case 'deleteEntry':
  $titleOfBackend = "Backend - Eintrag löschen";
  break;
default:
  $titleOfBackend = "Backend - Übersicht";
  break;
}
    return $titleOfBackend;
}
function html_header()
{
    $link = (file_exists("../../Musik-Stempel rund.png")) ? "../../" : "../" ;
    $name = basename(getcwd());

    return'<style>.nav-div{
    width: 80%;
    display: grid;
    grid-template-columns: auto auto auto auto;
  }
  .nav-div a{
    margin: 0.6em 1em;
  }
  @media screen and (max-width: 900px)
  {
    .nav-div{
      grid-template-columns: auto auto auto;
    }
    .nav-div a{
      margin: 0.5em 0;
    }
  }</style>
<center style="margin: 20px 0 0 0;">
  <!--LOGO--><a href="'.$link.'"><img src="'.$link.'Musik-Stempel rund.png" alt="Logo" ></a>
  <!--NAV--><br><h1>'.$name.'</h1>
  <div class="nav-div">
    <a href="index.php?show=events">Events bearbeiten</a>
    <a href="index.php?show=lists">Listen ausgeben</a>
    <a href="index.php?show=deleteList">Liste löschen</a>
    <a href="index.php?show=setup">Einstellungen</a>
    <a href="index.php?show=addFrontendUser">Nutzer hinzufügen (Frontend)</a>
    <a href="index.php?show=addBackendUser">Nutzer hinzufügen (Backend)</a>
    <a href="index.php?show=deleteEntry">Eintrag Löschen</a>
    <a href="index.php?show=inputconfig">Eingabefelder bearbeiten</a>
    <!--<a href="index.php?show=logOut">LogOut</a>-->
  </div>
</center>';
}
function html_events()
{
    //TODO backend: edit events.

    define_user();
    $events = process_event_get();
    foreach ($events as $filename => $event) {
        html_event_show($event);
    }
    echo "Noch in Bearbeitung";
}

function html_event_show($event)
{
}

function html_lists()
{
    echo "<h1><u>".TITLE_OF_BACKEND."</u></h1>";
    if (isset($_POST["send"])) {// Wenn Auswahl der Liste schon getroffen, dann:
        // Den Eventnamen aus Event.txt lesen
        $eventName = process_get_validate_event();
        $linkToEvent = ROOT.DIR_USER.get_user().SEP.SUBFOLDER.str_stripWSC($_POST["event"]);
        //Überschrift
        echo "<h1>Teilnehmerliste von {$_POST["event"]}</h1><center>";
        echo '<br><br><a href="index.php?show=lists">Zurück zur Auswahl</a></center>';

        echo html_show_table($linkToEvent);
    } else { // Wenn noch keine Auswahl für die anzuzeigende Tabelle gesendet wurde:
        // Form mit select und allen Events aus event.txt
        echo '<form action="" method="post">
        <select name="event" required>
        <option label="Konzerte:"></option>';
        // Events aus event.json lesen!
        // Option-Tag mit Funktion erstellen
        $csv = process_csv_from_events();
        //var_dump($csv);
        echo xsvToOption(",", $csv["name"], "", $csv["lable"]);
        echo '</select><button type="submit" name="send" >Ausgeben</button></form>';
        echo html_listFiles(SUBFOLDER);
    }
}
function process_csv_from_events()
{
    $lable = $name = "";
    foreach (file_json_dec(ROOT.DIR_USER.get_user().SEP."events.json") as $key => $tempExpl) {
        // Lable und Name spliten und in verschiedene Variablen
        $name .= $key.",";
        $lable .= $tempExpl["titel"]." ( ".$key." ),";
    }
    rtrim($name, ",");
    rtrim($lable, ",");
    return array("name"=>$name,"lable"=>$lable);
}
function html_listFiles($link)
{
    $ret = "<h1>Liste aller Dateien:</h1>";
    $ret .= "<ul>";
    foreach (dir_listFiles($link) as $key) {
        $ret .= "<li>$key</li>";
    }
    return $ret .= "</ul>";
}
function html_deleteList()
{
    echo "<h1>".TITLE_OF_BACKEND."</h1>";
    if (isset($_POST["delete"])) {// Wenn Auswahl schon getroffen und bestätigt:
        if (process_delete_file(SUBFOLDER.str_stripWSC($_POST["datei"]))) {
            $feedback = "Datei erforgreich gelöscht";
        } else {
            $feedback = "Fehler beim löschen!";
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
        // Anzeigen der verbliebenen Dateien
        $filesInDir = dir_listFiles(SUBFOLDER);
        $files = "";
        foreach ($filesInDir as $file) {
            $files .= (!empty($files)) ? ",".$file : $file ;
        }
        if (!empty($files)) {
            echo '<form action="" method="post"><select name="event" required><option label="Konzerte:"></option>';
            echo xsvToOption(",", $files);
            echo '</select><button type="submit" name="send" >Löschen</button></form>';
        } else {
            echo "<center>Keine Dateien mehr verfügbar!</center>";
        }
    }
    echo html_listFiles(SUBFOLDER);
}

function html_setup()
{
    echo "<h1>".TITLE_OF_BACKEND."</h1><br>";
    if (isset($_POST["setupUpdate"])) {
        $settings = process_read_setup(true);
        $setup = array();
        foreach ($settings as $line) {
            $label = $line[0];
            $varKind = $line[1];
            // Zeilenbrüche durch <br> ersetzten
            //$content = rtrim(preg_replace("/\r\n|\r|\n/", '<br>', $_POST[$line[0]]), "<br>");
            $content = $_POST[$line[0]];
            $lable = $line[3];
            $setup[] = array("name" =>$label,"type" => $varKind,"value" =>$content,"lable" => $lable);
        }
        file_json_enc("setup.json", $setup);
        echo "<script type='text/javascript'>document.location.href='".URL."';</script>";
    } else {
        $form = '<center><form action="" method="post">';
        $settings = process_read_setup(true);
        foreach ($settings as $line) {
            //<br> zurück zu Zeilensprüngen
            $line[2] = str_replace('<br>', "\n", $line[2]);
            switch ($line[1]) {
          case 'int':
            $form .= "<label for=".$line[0]." style='align:center;'>".$line[3]." (".$line[0].")</label> <input type='number' name=".$line[0]." value=".$line[2].">";
            break;
          case 'text':
            // Berechnung für die Höhe der textarea
            //temp ist ca. die Zeilenanzahl gemessen an der Zeichenanzahl
            $temp = ceil(strlen($line[2])/70)."em";
            // Wird dann als Style in den String geschrieben
            $height = "height: calc($temp + 50px + 1em);";
            $form .= "<label for=".$line[0]." style='align:center;'>".$line[3]." (".$line[0].")</label><div id='tArea'> <textarea class='auto-resize' style='$height'  name=$line[0]>$line[2]</textarea></div>";
            break;
          default:
            $form .= "<label for=".$line[0]." style='align:center;'>".$line[3]." (".$line[0].")</label><input type='text' name=".$line[0]." value='".$line[2]."'>";
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

function html_addBackendUser()
{
    echo "<h1>".TITLE_OF_BACKEND."</h1><br>";
    $link = (file_exists("../../Musik-Stempel rund.png")) ? "../../" : "../" ;
    if (isset($_POST["add"])) {
        // an das existierende htpasswd den User-Input appenden
        file_handle(ACCOUNTS.'.htpasswd', $_POST["user"], 'a');
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
    foreach (file_lines(ACCOUNTS.".htpasswd") as $line) {
        // An ":" spalten und nur namen ausgeben
        $t = str_expl(":", $line);
        echo "<li>".$t[0]."</li>";
    }
    echo"</ul></div>";
}
function html_addFrontendUser()
{
    echo "<h1>".TITLE_OF_BACKEND."</h1><br>";
    if (isset($_POST["add"])) {
        // an das existierende htpasswd den User-Input appenden
        $writeString = str_stripLenght($_POST["user"])."::".password_hash(str_stripLenght($_POST["password"]), PASSWORD_DEFAULT)."\n";
        file_handle(ACCOUNTS.'frontendAccounts', $writeString, 'a');
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
    foreach (file_lines(ACCOUNTS."frontendAccounts") as $line) {
        // An ":" spalten und nur namen ausgeben
        $t = str_expl("::", $line);
        echo "<li>".$t[0]."</li>";
    }
    echo"</ul></div>";
}

function html_deleteEntry()
{
    echo "<h1><u>".TITLE_OF_BACKEND."</u></h1>";
    if (isset($_POST["stimme"])) {
        $json = file_json_dec($_POST["event"].".json");
        if (isset($json[$_POST["stimme"]][$_POST["Name"]])) {
            unset($json[$_POST["stimme"]][$_POST["Name"]]);
            $feedback = "Eintrag (".$_POST['stimme'].") aus ".$_POST['event'].".json  erfolgreich gelöscht";
        } else {
            $feedback = "Beim löschen des Eintrags (".$_POST['stimme'].") aus ".$_POST['event'].".json gab es einen Fehler!";
        }
        file_handle($_POST["event"].".json", json_encode($json));
    }
    if (isset($_POST["send"])) {// Wenn Auswahl der Liste schon getroffen, dann:

        // Den Eventnamen aus Event.txt lesen
        $events = file_json_dec("events.json");
        if (($array = process_event_get_by_key($events, $_POST["event"])) !== false) {
            $eventName = process_event_get_param($array, "titel");
        }
        $linkToEvent = SUBFOLDER.str_stripWSC($_POST["event"]);

        //Überschrift
        echo "<h1>Teilnehmerliste von $eventName</h1><center>";
        echo '<br><br><a href="index.php?show=deleteEntry">Zurück zur Auswahl</a><br><br></center>';
        if (file_exists($linkToEvent.".json")) {// Wenn Tabelle existiert, dann
            $json = file_json_dec($linkToEvent.".json");
            // Tabelle mit alle Werten erstellen
            echo '<center>';
            $gruppen = process_event_get_groups_of_json(file_json_dec("events.json"));
            foreach ($gruppen as $gruppe) {
                echo '<form action="" method="post"><table style="width:60%;">
              <input type="text" name="event" style="display:none;" value="'.$linkToEvent.'">
              <input type="text" name="stimme" style="display:none;" value="'.$gruppe.'">';
                echo"<tr><td style='text-align:center'>$gruppe:</td></tr>";
                foreach ($json[$gruppe] as $number =>$array) {
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
        $csv = process_csv_from_events();
        echo xsvToOption(",", $csv["name"], "", $csv["lable"]);
        echo '</select><button type="submit" name="send" >Ausgeben</button></form>';
    }
}
function html_inputconfig()
{
    if (isset($_POST["deleteField"])) {
        $name = $_POST["deleteField"];
        $nameWithUnderscore = str_replace(' ', '_', $_POST["deleteField"]);
        if (isset($_POST[$nameWithUnderscore.":deleteVerify"])) {
            if ($_POST[$nameWithUnderscore.":deleteVerify"] == $_POST["deleteField"]) {
                $json = file_json_dec("inputfelder.json");
                if ($_POST[$nameWithUnderscore.":kindOfField"] == "input") {
                    unset($json["input"][$name]);
                    $feedback = '"'.$name.'" wurde erforgreich gelöscht';
                }
                file_handle("inputfelder.json", json_encode($json));
            }
        } else {
            $feedback = 'Löschen von "'.$_POST["deleteField"].'" wurde nicht bestätigt!'; // FEEDBACK
        }
        echo "<h1>$feedback</h1>";
    }
    if (isset($_POST["send"])) {
        $json = array();
        foreach ($_POST as $name => $value) {
            $split = str_expl(":", $name);
            if (arr_count($split)>1) {
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
        file_handle("inputfelder.json", json_encode($json));
    }
    if (isset($_POST["addInput"])) {
        if (!empty($_POST["newInputTilte"])||!empty($_POST["newInputTilte1"])) {
            $title = (!empty($_POST["newInputTilte"]))?$_POST["newInputTilte"]:$_POST["newInputTilte1"];
            $json = file_json_dec("inputfelder.json");
            $json["input"][$title] = array('type'=>'text','required'=>'false','label'=>'');
            file_handle("inputfelder.json", json_encode($json));
        }
    }
    if (!isset($_POST["send"])) {
        $json = file_json_dec("inputfelder.json");
        //Für alle inputs
        echo'<center><h1>'.TITLE_OF_BACKEND.'</h1><form action="" method="post"><button type="submit" name="send" >Ändern</button>';
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





function html_show_table($link)
{
    if (!file_check($link.".json")) {// Wenn Tabelle existiert, dann
        return "<center>Diese Tabelle ({$_POST['event']}) existiert noch nicht <br>keine Einträge im Ordner {$_POST['event']}<br><br> <a href='index.php?show=lists'>Zurück zur Listenübersicht</a></center>";
    }
    //download-Link
    $json = file_json_dec($link.".json");
    process_write_csv_file($json, $link);
    $ret = '<center><a href="'.$link.'.csv" style="font-size: 0.8em;" download>.CSV-Datei zum download</a></center>';

    // öffnet die .CSV
    // Tabelle mit alle Werten erstellen
    $ret .= '<table border="1" style="width: 60%; margin: 50px 20%;">';
    foreach (process_get_json_keys($json) as $key) {
        $ret .="<tr><td style='text-align:center'>$key:</td></tr>";
        foreach ($json[$key] as $number =>$array) {
            $ret .="<tr><td></td>";
            foreach ($array as $bezeichnug =>$wert) {
                $ret .="<td style='text-align:center'>$wert</td>";
            }
            $ret .="</tr>";
        }
    }
    return $ret .= '</table>';
}
