<?php
require_once 'process.php';
// TODO html_mail_text()
// TODO html_mail_interface()
function list_all_constants()
{
    foreach (get_defined_constants() as $key => $v) {
        echo $key."-".$v."<br>";
    }
}
function display_div_none(string $string)
{
    return '<div style="display:none;">'.$string.'</div>';
}
function html_define_group()
{
    if (isset($_SESSION["EVENT_ART"])) {
        if ($_SESSION["EVENT_ART"] == "Stimmbildung") {
            define_group("Stimmbildung");
            return true;
        }
    }
    if (isset($_POST["stimme"])) {
        define_group($_POST["stimme"]);
        return true;
    } else {
        return false;
    }
}
function html_define_event_art()
{
    if (!defined("EVENT_ART")) {
        if (isset($_POST['auswahl_art_event'])) {
            define("EVENT_ART", $_POST['auswahl_art_event']);
            $_SESSION["EVENT_ART"]=$_POST['auswahl_art_event'];
            process_reload_page();
        } else {
            html_select_option();
        }
    }
}
function construct_Input_Form_Json_Input()
{
    $ret = "";
    $jsonInputfelder = file_json_dec(UBE."inputfelder.txt");
    foreach ($jsonInputfelder["input"] as $key => $val) {
        // Wenn Label vorhanden
        if (!empty($jsonInputfelder["input"][$key]["label"])) {
            $ret .= '<label for="'.$key.'">'.$jsonInputfelder["input"][$key]["label"].'</label>';
        }
        $temp = (filter_var($jsonInputfelder["input"][$key]["required"], FILTER_VALIDATE_BOOLEAN))?' ':' ';
        $ret .= '<input type="'.$jsonInputfelder["input"][$key]["type"].'" name="'.$key.'" placeholder="'.$key.'" '.$temp.'>';
    }
    return $ret;
}
function html_form_input_hidden_with_values()
{
    $jsonInputfelder = file_json_dec(UBE."inputfelder.txt");
    $ret = "";
    foreach ($jsonInputfelder["input"] as $key => $val) {
        $value = (isset($_POST[$key]))?'value="'.$_POST[$key].'"':" ";
        $ret .= '<input style="display:none;" type="'.$jsonInputfelder["input"][$key]["type"].'" name="'.$key.'" '.$value.' >';
    }
    return $ret;
}
function html_bereits_eingetragen(string $LTE_JSON)
{
    $ret = "<p>";
    if (calc_slots_used($LTE_JSON) > 0) {
        $json_table = file_json_dec($LTE_JSON);
        $ret .= "Bereits eingetragen:";
        $ret .="<ul style='list-style:none;'>";
        foreach ($json_table[GROUP] as $key =>$name) {
            $ret .= "<li>".$json_table[GROUP][$key]["Vorname"]." ".$json_table[GROUP][$key]["Name"]."</li>";
        }
        $ret .= '</ul>';
    } else {
        $ret .= "Noch niemand eingetragen";
    }
    return $ret .= "</p>";
}

function html_Plätze_übrig(string $LTE_JSON, array $jsonEvent)
{
    $belegtePlätze = calc_slots_used($LTE_JSON);
    $dateiName = process_event_get_param($jsonEvent, "dateiname");
    $maxZuschauer = calc_maxZuschauer($dateiName);
    $ret = "";
    if (check_Event_available($LTE_JSON, $dateiName)) {
        if ($maxZuschauer - $belegtePlätze < DEF_FREIEPLAETZEZEIGENAB) {
            $ret .= ($maxZuschauer - $belegtePlätze == 1) ? "<p>Noch ein Platz frei</p>" : "<p>Noch ". ($maxZuschauer - $belegtePlätze)." Plätze frei</p>" ;
        }
        $ret .= '<button type="submit" name="event" style="border-width: 4px;" value ="'.$dateiName.'">bei '.process_event_get_param($jsonEvent, "titel").' eintragen</button>';
    } else {
        $ret .= '<p>Leider sind alle Plätze dieser Probe belegt!</p>';
    }
    return $ret;
}
//returns a html option tag
function option($value, $label, $selected)
{
    if ($selected != $value) {
        return "<option value='$value'>$label</option>";
    } else {
        return "<option value='$value' selected>$label</option>";
    }
}
//Parses a string seperated by "" into an html-option-tag
//z.B. xsvToOption(",", "E,TH,S", "E", "Everything,Headlines Only,Quellen");
function xsvToOption($seperator, $xsv, $selected ="", $label="")
{
    $xsvArray = explode($seperator, $xsv);
    $i = 0;
    $s = sizeof($xsvArray);
    if (!empty($label)) {
        $labelArray = explode($seperator, $label);
        if (sizeof($xsvArray)!=sizeof($labelArray)) {
            echo"Anzahl der Label und der Eingabe stimmt nicht überein!!";
            return;
        }
    }
    $ret = "";
    while ($i < sizeof($xsvArray)) {
        if (!empty($label)) {
            $ret .= (!empty($selected)) ? option($xsvArray[$i], $labelArray[$i], $selected) : "<option value='$xsvArray[$i]'>$labelArray[$i]</option>" ;
        } else {
            $ret .= (!empty($selected)) ? option($xsvArray[$i], $xsvArray[$i], $selected) : "<option value='$xsvArray[$i]'>$xsvArray[$i]</option>" ;
        }
        $i += 1;
    }
    return $ret;
}

function html_construct_Input_Form_register()
{
    $jsonInputfelder = file_json_dec(UBE."inputfelder.txt");
    $ret ='<form class="input-box" action="" method="post">';
    //Für alle inputs
    $ret .= construct_Input_Form_Json_Input();
    $json = process_event_get();
    $filteredJSON= process_event_filter($json, "art", $_SESSION["EVENT_ART"]);
    $groups = process_event_get_groups_of_json($filteredJSON);
    if (arr_count($groups)>1) {
        $groupsCSV = arr_to_csv_line($groups);
        $ret .='<select name="stimme" required>
              <option label="Stimme auswählen:"></option>';
        $ret .= xsvToOption(",", $groupsCSV);
        $ret .='</select>';
    }

    $ret .='<button type="submit" name="chooseEvent">Weiter</button>
            </form>';
    return $ret;
}
function construct_Input_Form_chooseEvent()
{
    $jsonInputfelder = file_json_dec(UBE."inputfelder.txt");

    $ret ='<form class="input-box" action="dataSent.php" method="post">';
    $ret .=(defined("GROUP")) ?'<input type="text" name="stimme" style="display:none;" value="'.GROUP.'">':'';
    $ret .= html_form_input_hidden_with_values();
    // TODO:
    $json = process_event_get();
    $filteredJSON= process_event_filter($json, "art", $_SESSION["EVENT_ART"]);
    //dump($filteredJSON);
    foreach ($filteredJSON as $key) {
        //dump($key);
        $ret .= html_event_list_given($key);
    }
    return $ret .= "</form>\n";
}
function setValueToJSON($event)
{
    if (check_entry_existing()) {
        return DEF_TEXT_ENTRYEXISTED;
    }
    $LTE_JSON = UBE.SUBFOLDER.$event.".json";

    if (!check_Event_available($LTE_JSON, $event)) {
        return DEF_TEXT_FAIL;
    }
    return (update_data_Eventlists($LTE_JSON))?DEF_TEXT_SUCCESS:"Fehler beim schreiben in JSON";
}

function user_html_login_show(bool $logout = false)
{
    if ($logout) {
        return "<center><br><br><br><a href='index.php'>Zurück zum LogIn</a></center>";
    } else {
        return '<center>
  <br>
  <form class="input-box" action="index.php" method="post">
  <label for="logInUser"> Anmeldung für '.DEF_TITLE.'</label>
  <input type="text" name="logInUser" placeholder="Username:" required>
  <input type="password" name="logInPassword" placeholder="Passwort:" required>
    <button type="submit" name="passwordAbschicken">LogIn</button>
  </form></center>';
    }
}
function user_html_login()
{
    //Ret true / string
    return user_check_login(get_user(), get_pwd())?true:user_html_login_show(false);
}
function check_user_login()
{
    //Ret bool
    return user_check_login(get_user(), get_pwd())?true:false;
}
function user_html_logout()
{
    return user_logout()?"Erfolgreich ausgeloggt":"Fehler beim Ausloggen!";
}
function html_user_authentication()
{
    $loginState=user_html_login();
    if ($loginState !== true) {
        echo $loginState;

        if (isset($_POST["logInPassword"])) {
            if (user_check_login(get_user(), get_pwd()) === true) {
                define_user();
                check_create_user_dir();
            } else {
                exit();
            }
        }
    } else {
        define_user();
    }
    if (isset($_GET["show"])) {
        if ($_GET["show"] == "logOut") {
            echo "<br>".user_html_logout();
            echo '<br><a href="index.php">Zurück zur Anmeldung</a>';
        } elseif ($_GET["show"] == "auswahl") {
            unset($_SESSION["EVENT_ART"]);
            echo "<script type='text/javascript'>document.location.href='index.php';</script>";
        }
    }
}
function html_show_site()
{
    check_create_user_dir();
    if (!(isset($_SESSION["EVENT_ART"])||defined("EVENT_ART"))) {
        html_define_event_art();
        echo '<br><a href="index.php?show=logOut"><LogOut></a>';
        return;
    }
    if (isset($_POST["chooseEvent"])) {
        // EVENTS
        echo '<h1>'.DEF_TITLE.'</h1>';
        echo (isset($_POST["stimme"]))?'<h1>'.$_POST["stimme"].':</h1>':'';
        echo' <br><div style="color:white;">';
        echo construct_Input_Form_chooseEvent();
        echo'</div>';
    } else {
        // ANMELDUNG
        echo '<p style="width: 70%;">'.DEF_TEXT_OBEN.'</p><br><h1>'.DEF_TITLE.'</h1><h1>'.get_user().'</h1><br>';
        echo html_construct_Input_Form_register();
    }
    echo '<br><a href="index.php?show=auswahl">< Zurück zur Auswahl ></a>';
    echo '<br><a href="index.php">< Zurück zur Anmeldung ></a>';
    echo '<br><br><a href="index.php?show=logOut">< LogOut ></a>';
}
function html_select_option()
{
    $groupsAvailableForUser = process_event_get_availabble_eventart_for_user();
    $csv = arr_to_csv_line($groupsAvailableForUser);
    echo '<form action="" method="post">';
    echo '<select name="auswahl_art_event" required>';
    echo '<option label="Auswahl"></option>';
    echo xsvToOption(",", $csv);
    echo '</select>';
    echo '<button type="submit" name="submit">anzeigen</button>';
    echo '</form>';
}
function html_initialize_setup_var()
{
    foreach (process_read_setup() as $line) {
        $name = $line[0];
        //Var-Name = $line[0]
        //Switch über 2. Arrayeintrag (Var-Type)
        //Var-Wert = $line[2]
        switch ($line[1]) {
          case 'int':
            $$name = (int) preg_replace('/\s+/', '', $line[2]);
            break;
          case 'bool':
            $$name = filter_var($t[2], FILTER_VALIDATE_BOOLEAN);
            break;
          default:
            $$name = $line[2];
            break;
        }
        define("DEF_".strtoupper($name), $$name);
    }
}
// TODO:
function html_event_list_given(array $json)
{
    global $LTE_JSON;
    $eventKey = process_event_get_param($json, "dateiname");

    $LTE_JSON = UBE.SUBFOLDER.$eventKey.".json";
    check_write_new_Event($LTE_JSON, $eventKey);
    $jsonEvents = process_event_get();
    $jsonEvent = $jsonEvents[$eventKey];
    $ret = "";
    $ret .= "<h2 style='font-weight: bold;'>".process_event_get_param($jsonEvent, "titel")."</h2>";
    $ret .= html_bereits_eingetragen($LTE_JSON);
    $ret .= html_Plätze_übrig($LTE_JSON, $jsonEvent);
    $ret .= "<hr/>";
    return $ret;
}
