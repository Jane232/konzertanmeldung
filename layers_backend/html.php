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
// EVENTS ######################################################################
function html_events()
{
    if (!isset($_GET["subshow"])) {
        echo "<center><br><br><a href='index.php?show=events&subshow=change'>Events bearbeiten / löschen</a> <br>";
        echo "<a href='index.php?show=events&subshow=order'>Events sortieren </a> <br>";
        echo "<a href='index.php?show=events&subshow=singleEntryAdd'>einzelne Events hinzufügen</a> <br>";
        echo "<a href='index.php?show=events&subshow=import'>Events Importieren </a> <br>";
        echo "<a href='index.php?show=events&subshow=deleteAllEvents'>ALLE Events Löschen </a></center> <br>";
        return;
    }

    echo "<br><center>";
    if ($_GET["subshow"]=="change") {
        echo html_event_change();
    } elseif ($_GET["subshow"]=="order") {
        echo html_event_drag_and_drop();
    } elseif ($_GET["subshow"]=="singleEntryAdd") {
        echo html_event_single_entry_add();
    } elseif ($_GET["subshow"]=="import") {
        echo html_event_import();
    } elseif ($_GET["subshow"]=="deleteAllEvents") {
        echo html_event_delete_all();
    }
    echo "<br><a href='index.php?show=events'> Zurück zur Auswahl</a></center>";
}
function html_event_single_entry_add()
{
    if (isset($_POST["add"])) {
        $newEvent = array();
        $newEvent["titel"]=$_POST["titel"];
        $seperatedDate = str_expl(" ", $_POST["beginn"]);
        $filename = $_POST["art"].$seperatedDate[0]."(".$seperatedDate[1].")";
        $newEvent["dateiname"]= str_stripWSC(mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', str_replace(":", ".", $filename)));
        $newEvent["art"]=$_POST["art"];
        $newEvent["beginn"]=$_POST["beginn"];
        $newEvent["dauer"]=(int) $_POST["dauer"];
        $newEvent["ort"]=$_POST["ort"];
        $newEvent["gruppen"]=array();
        $gruppenArray = str_expl(",", $_POST["gruppen"]);
        foreach ($gruppenArray as $key) {
            $gruppe = str_expl(":", $key, 2);
            $newEvent["gruppen"][] = array('name' => $gruppe[0],'size' => (int) $gruppe[1]);
        }
        if (!isset($_POST["freigeschaltet_ab"])) {
            $ab = date_sub(date_create_from_format("d#m#Y H#i", $_POST["beginn"]), date_interval_create_from_date_string(DEF_FREISCHALTEN_AB));
        } else {
            $ab = date_create_from_format("Y-m-d H:i", $_POST["freigeschaltet_ab"]." 08:00");
        }
        $newEvent["freigeschaltet-ab"] = $ab;
        $newEvent["freigeschaltet-bis"] = date_sub(date_create_from_format("d#m#Y H#i", $_POST["beginn"]), date_interval_create_from_date_string(DEF_FREISCHALTEN_BIS));
        $eventJson = file_json_dec("events.json");
        $eventJson[$newEvent["dateiname"]] = $newEvent;
        return (file_json_enc("events.json", $eventJson))?"Event erfolgreich hinzugefügt":"Fehler beim Hinzufügen des Events";
    } else {
        $ret  = 'Um ein neues Event hinzuzufügen, bitte alle Felder ausfüllen:';
        $ret .= '<form action="" method="post">';
        $ret .= '<lable for="title">Titel des Events</lable>';
        $ret .= '<input type="text" name="titel" placeholder="1. Probe zur kleinen Bachstunde" required>';
        $ret .= '<lable for="art">Art des Events (Probe / Stimmbildung / etc.)</lable>';
        $ret .= '<input type="text" name="art" placeholder="Probe" required>';
        $ret .= '<lable for="beginn">Veranstaltungsbeginn</lable>';
        $ret .= '<input type="text" name="beginn" placeholder="13.10.2020 17:00" required>';
        $ret .= '<lable for="dauer">Veranstaltungsdauer</lable>';
        $ret .= '<input type="number" name="dauer" placeholder="45" required>';
        $ret .= '<lable for="ort">Veranstaltungsort</lable>';
        $ret .= '<input type="text" name="ort" placeholder="Lutherhaus" required>';
        $ret .= '<lable for="gruppen">Gruppen des Events (im Format: Gruppe1:Teilnehmerzahl1,Gruppe2:Teilnehmerzahl2,Gruppe3:Teilnehmerzahl3)</lable>';
        $ret .= '<input type="text" name="gruppen" placeholder="Sopran:6,Alt:3,Tenor:10,Bass:8" required>';
        $ret .= '<lable for="freigeschaltet_ab">freigeschaltet ab (optional):</lable>';
        $ret .= '<input type="date" name="freigeschaltet_ab">';
        $ret .= '<button type="submit" name="add" > Neues Event erstellen </button>';
        $ret .= '</form>';
        return $ret;
    }
}
function html_event_delete_all()
{
    if (isset($_POST["stage_two"])) {
        $ret  = 'Letzte Warnung: Die Events werde dabei unwiderruflich gelöscht!';
        $ret .= '<script>alert("Achtung: Die Events sind nicht wiederherzustellen!")</script>';
        $ret .= '<form action="" method="post">';
        $ret .= '<button type="submit" name="cancel" > Löschen abbrechen </button>';
        $ret .= '<button type="submit" name="deleteAllEvents" > alle Events löschen </button>';
        return $ret .= '</form>';
    } elseif (isset($_POST["deleteAllEvents"])) {
        return (file_json_enc("events.json", array()))?" Alle Events wurden erfolgreich gelöscht": "Es gab ein Problem beim Löschen ";
    } elseif (isset($_POST["cancel"])) {
        return "Der Löschvorgang wurde abgebrochen";
    } else {
        $ret  = 'Wollen Sie wirklich alle Events unwiderruflich löschen?';
        $ret .= '<form action="" method="post">';
        $ret .= '<button type="submit" name="cancel" > Löschen abbrechen </button>';
        $ret .= '<button type="submit" name="stage_two" > alle Events löschen </button>';
        $ret .= '</form>';
        return $ret;
    }
}
function html_event_change()
{
    $events = process_backend_event_get();
    if (isset($_POST["change"])) {
        echo process_event_change($events);
        $events = process_backend_event_get();
    }
    if (isset($_POST["deleteEvent"])) {
        echo process_event_delete($events);
    }
    define_user();
    $ret = "";
    $events = process_backend_event_get();
    if (!is_array($events)) {
        return "Keine Events vorhanden!";
    }
    if (arr_count($events) == 0) {
        return "Keine Events vorhanden!";
    }
    foreach ($events as $filename => $event) {
        $ret.= html_event_show($event);
    }
    return $ret.= '
    <script type="text/javascript">
  function show(id) {
    let form = document.getElementById(id);
    let button = document.getElementById(id+"_button");
    let buttonContent = button.innerHTML;
    if (form.style.display === "none") {
      form.style.display = "block";
      button.innerHTML = buttonContent.replace("einblenden", "ausblenden");
    } else {
      form.style.display = "none";
      button.innerHTML = buttonContent.replace("ausblenden", "einblenden");
    }
  }
</script>';
}
function html_event_import()
{
    if (isset($_FILES["importSelector"])) {
        $linkToTempCSV = "temp.csv";
        move_uploaded_file($_FILES['importSelector']['tmp_name'], $linkToTempCSV);
        $gruppen = csv_to_array($_POST["gruppen"]);
        process_event_import_csv("events.json", $linkToTempCSV, $gruppen, true);
        process_delete_file($linkToTempCSV);
        echo '<br><center>Events erfolgreich importiert<br></center>';
    } else {
        $ret  = 'Die Events werden mit den standard-Einstellungen importiert! <br> Die Standards können in "Einstellungen" verändert werden. <br> Alle Parameter sind später editierbar<br><br>';
        $ret .= '<form method="post" enctype="multipart/form-data" >';
        $ret .= '<lable for="gruppen">Namen der Gruppen mit Komma getrennt:</lable><br>';
        $ret .= '<input type="text" name="gruppen" placeholder="Sopran,Alt,Tenor,Bass" required>';
        $ret .= '<lable for="importSelector">Bitte betreffende .csv Datei auswählen</lable><br>';
        $ret .= '<input type="file" name="importSelector" accept=".csv" required>';
        $ret .= '<button type="submit" name="change" value=""> Importieren </button>';
        $ret .= '</form>';
        $ret .= '<br><br><br>';
        $ret .= '<a href="../../backend/stock/vorlage4stimmen.csv" download> Tabellenvorlage mit vier Stimmen (download)</a><br>';
        $ret .= '<a href="../../backend/stock/vorlageStimmbildung.csv" download> Tabellenvorlage Stimmbildung (download)</a><br>';
        return $ret;
    }
}

function html_event_drag_and_drop()
{
    $events = process_backend_event_get();
    if (isset($_POST["eventsReinfolgeInJson"])) {
        $newOrderOfEvents = json_decode($_POST["eventsReinfolgeInJson"], true);
        $events = process_array_assoc_sort($events, $newOrderOfEvents);
        file_json_enc("events.json", $events);
    }

    $events = process_backend_event_get();
    if (arr_count($events) < 2) {
        return "zu wenige Events vorhanden!";
    }
    $ret = '<ul id="dragAndDropEventListe">';
    foreach ($events as $key => $value) {
        $ret .= '<li class="draggable" draggable="true">'.$key.'</li>';
    }
    $ret .= '</ul>';
    $ret .= '<script src="../../backend/stock/functions.js"></script>';
    $ret .= '<button onclick= getNewListAndSend()> Reihnfolge ändern</button>';
    return $ret .= '<form action="" method="post" style="display:none;" id="dragAndDropEventForm"><input type="text" name="eventsReinfolgeInJson"/></form>';
}
function html_event_show($event)
{
    $dateiname = process_event_get_param($event, "dateiname");
    $ret = '<button onclick=show("'.$dateiname.'") id="'.$dateiname.'_button">'.process_event_get_param($event, "titel").' ( '.$dateiname.' )  einblenden </button>';
    $ret .= '<form class="input-box" id="'.$dateiname.'" method="post" style="display:none; margin: 0 auto; color: white;">';
    foreach ($event as $key => $v) {
        $value = process_event_get_param($event, $key);
        if (!is_array($value)) {
            $ret .= '<lable for="'.$key.'">'.$key.'</lable>';
            $ret .= '<input type="text" name="'.$key.'" value="'.$value.'"/>';
        } else {
            $ret .= $key."<br>";
            switch ($key) {
              case 'gruppen':
                foreach ($value as $key2 => $value2) {
                    $ret .= '<div class="backendListGruppe">';
                    $ret .= '<h1>'.$value2["name"].'</h1>';
                    $ret .= '<lable for="name_'.$key2.'">Name</lable>';
                    $ret .= '<input type="text" name="name_'.$key2.'" value="'.$value2["name"].'"/>';
                    $ret .= '<lable for="size_'.$key2.'">Gruppengröße</lable>';
                    $ret .= '<input type="number" name="size_'.$key2.'" value="'.$value2["size"].'"/>';
                    $ret .= '</div>';
                }
                break;

              default:
                $ret .= '<div class="backendListGruppe">';
                foreach ($value as $key2 => $value2) {
                    $ret .= '<lable for="'.$key2."_".$key.'">'.$key2.'</lable>';
                    $ret .= '<input type="text" name="'.$key2."_".$key.'" value="'.$event[$key][$key2].'"/>';
                }
                $ret .= '</div>';

                break;
            }
        }
    }
    $ret .= '<button type="submit" name="change" value="'.$dateiname.'"> '.process_event_get_param($event, "titel").' ( '.$dateiname.' ) ändern </button>';
    $ret .= '<div style="margin: 0 5% 1em 5%; padding: 0 1em; border: 3px solid rgb(156, 30, 30); border-radius: 24px;"> <u>'.$dateiname.' löschen?</u><br>';
    $ret .= '<input type="checkbox" name="deleteVerify" value="'.$dateiname.'"><label for="deleteVerify"> "'.$dateiname.'" wirklich löschen?</label><br>';
    $ret .= '<button type="submit" name="deleteEvent" value="'.$dateiname.'" style="margin:1em auto;border: 2px solid rgb(156, 30, 30);">"'.$dateiname.'" löschen</button>';
    $ret .= '</div>';
    return $ret .= '</form>';
}
// LISTS #######################################################################

function html_lists()
{
    echo "<h1><u>".TITLE_OF_BACKEND."</u></h1>";
    if (isset($_POST["send"])) {// Wenn Auswahl der Liste schon getroffen, dann:
        // Den Eventnamen aus Event.txt lesen
        $eventName = process_get_validate_event();
        $name = basename(getcwd());
        $linkToEvent = getcwd().SEP.SUBFOLDER.$eventName;
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
        //$csv = process_csv_from_events();
        $csv = process_csv_list_of_Files(SUBFOLDER);
        echo xsvToOption(",", $csv["name"], "", $csv["lable"]);
        echo '</select>';
        echo '<button type="submit" name="send" >Anzeigen</button>';
        echo '</form>';
        echo html_listFiles(SUBFOLDER);
    }
}

function html_show_table($link)
{
    $link = rtrim($link, ".json");

    if (!file_check($link.".json")) {// Wenn Tabelle existiert, dann
        return "<center>Diese Tabelle ({$_POST['event']}) existiert noch nicht <br>keine Einträge im Ordner {$_POST['event']}<br><br> <a href='index.php?show=lists'>Zurück zur Listenübersicht</a></center>";
    }
    //download-Link
    $json = file_json_dec($link.".json");
    process_write_csv_file($json, $link);
    $ret = '<center><a href="'.SUBFOLDER.rtrim($_POST['event'], ".json").'.csv" style="font-size: 0.8em;" download>.CSV-Datei zum download</a></center>';

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
function html_listFiles($link)
{
    $ret = "<h1>Liste aller Dateien:</h1>";
    $ret .= "<ul>";
    foreach (dir_listFiles($link) as $key) {
        $ret .= "<li>$key</li>";
    }
    return $ret .= "</ul>";
}
// DELETE-LIST #################################################################

function html_deleteList()
{
    echo "<h1>".TITLE_OF_BACKEND."</h1>";
    if (!isset($_GET["subshow"])) {
        echo "<center><br><br><a href='index.php?show=deleteList&subshow=singleEntry'>einzelne Dateien löschen</a> <br>";
        echo "<a href='index.php?show=deleteList&subshow=deleteAll'>alle Dateien löschen</a></center> <br>";
        return;
    }
    if ($_GET["subshow"] == "singleEntry") {
        html_delete_single_entry();
    } elseif ($_GET["subshow"] == "deleteAll") {
        html_delete_all_entrys();
    }
    echo "<center><br><a href='index.php?show=deleteList'>zurück zur Auswahl</a> <br></center>";
    echo html_listFiles(SUBFOLDER);
}
function html_delete_all_entrys()
{
    if (isset($_POST["delete"])) {// Wenn Auswahl schon getroffen und bestätigt:
        $filesInDir = dir_listFiles(SUBFOLDER);
        $ret = true;
        foreach ($filesInDir as $key) {
            if (!process_delete_file(SUBFOLDER.str_stripWSC($key))) {
                $ret = false;
                break;
            }
        }
        if ($ret) {
            $feedback = "alle Dateien erforgreich gelöscht";
        } else {
            $feedback = "Fehler beim löschen!";
        }
        // Zurück-Link
        echo "<center>".$feedback."<br><br><a href='index.php?show=deleteList'>Zurück zum Listen-löschen</a><br></center>";
    } elseif (isset($_POST["send"])) { // Wenn Auswahl schon getroffen und noch nicht bestätigt:
        // Form mit Bestätigung ob Datei wirklich gelöscht werden so
        echo '<center>Willst du wirklich ALLE Dateien unwiderruflich löschen?
        <form action="" class="contentForm" method="post">
          <button type="submit" name="cancel">Löschen abbrechen!</button>
         <button type="submit" name="delete">Löschen bestätigen!</button>
       </form></center>';
    } elseif (isset($_POST["cancel"])) { // Wenn Auswahl schon getroffen und noch nicht bestätigt:
        // Form mit Bestätigung ob Datei wirklich gelöscht werden so
        echo '<center>Löschen Abgrbrochen <br>
            </center>';
    } else {
        // Anzeigen der verbliebenen Dateien
        $filesInDir = dir_listFiles(SUBFOLDER);
        if (arr_count($filesInDir)>0) {
            echo '<form action="" method="post"><button type="submit" name="send" >Alle Tabellen dieses Nutzers unwiderruflich löschen?</button></form>';
        } else {
            echo "<center>Keine Dateien mehr verfügbar!</center>";
        }
    }
}
function html_delete_single_entry()
{
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
}

// SETUP #######################################################################


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

// USER #######################################################################

function html_addBackendUser()
{
    echo "<h1>".TITLE_OF_BACKEND."</h1><br>";
    $link = (file_exists("../../Musik-Stempel rund.png")) ? "../../" : "../" ;
    if (isset($_POST["add"])) {
        // an das existierende htpasswd den User-Input appenden
        file_handle(ACCOUNTS.'.htpasswd', $_POST["user"]."\n", 'a');
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
// deleteEntry #######################################################################

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
        $dateiname = rtrim($_POST["event"], ".json");
        if (($array = process_event_get_by_key($events, $dateiname)) !== false) {
            $eventName = process_event_get_param($array, "titel");
        }
        $linkToEvent = SUBFOLDER.str_stripWSC($dateiname);

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
        $csv = process_csv_list_of_Files(SUBFOLDER);
        echo xsvToOption(",", $csv["name"], "", $csv["lable"]);
        echo '</select><button type="submit" name="send" >Anzeigen</button></form>';
    }
}

// inputconfig #######################################################################


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
