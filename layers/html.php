<?php
require_once 'proccess.php';

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

function construct_Input_Form_Json_Input()
{
    $ret = "";
    $jsonInputfelder = file_json_dec(BE."inputfelder.txt");
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
function construct_Input_Form_Json_Input_with_values()
{
    $jsonInputfelder = file_json_dec(BE."inputfelder.txt");
    $ret = "";
    foreach ($jsonInputfelder["input"] as $key => $val) {
        $value = (isset($_POST[$key]))?'value="'.$_POST[$key].'"':" ";
        $ret .= '<input style="display:none;" type="'.$jsonInputfelder["input"][$key]["type"].'" name="'.$key.'" '.$value.' >';
    }
    return $ret;
}
function html_bereits_eingetragen($LTE_JSON)
{
    $ret = "<p>";
    if (calc_slots_used($LTE_JSON) > 0) {
        $json_Content_function = file_json_dec($LTE_JSON);
        $ret .= "Bereits eingetragen:";
        $ret .="<ul style='list-style:none;'>";
        foreach ($json_Content_function[VOICE] as $key =>$name) {
            $ret .= "<li>".$json_Content_function[VOICE][$key]["Vorname"]." ".$json_Content_function[VOICE][$key]["Name"]."</li>";
        }
        $ret .= '</ul>';
    } else {
        $ret .= "Noch niemand eingetragen";
    }
    return $ret .= "</p>";
}

function html_Plätze_übrig(array $t, string $LTE_JSON)
{
    $belegtePlätze = calc_slots_used($LTE_JSON);
    $maxZuschauer = calc_maxZuschauer();
    $ret = "";
    if (check_Event_available($LTE_JSON)) {
        if ($maxZuschauer - $belegtePlätze < DEF_FREIEPLAETZEZEIGENAB) {
            $ret .= ($maxZuschauer - $belegtePlätze == 1) ? "<p>Noch ein Platz frei</p>" : "<p>Noch ". ($maxZuschauer - $belegtePlätze)." Plätze frei</p>" ;
        }
        $ret .= '<button type="submit" name="event" style="border-width: 4px;" value ="'.$t[0].'">bei '.$t[1].' eintragen</button>';
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

function construct_Input_Form_register()
{
    $jsonInputfelder = file_json_dec(BE."inputfelder.txt");
    $ret ='<form class="input-box" action="" method="post">'."\n";
    //Für alle inputs
    $ret .= construct_Input_Form_Json_Input();
    $ret .='<select name="stimme" required>
<option label="Stimme auswählen:"></option>';
    $ret .= xsvToOption(",", "Sopran,Alt,Tenor,Bass");
    $ret .='</select>
  <button type="submit" name="chooseEvent">Weiter</button>
  </form>';
    return $ret;
}
function construct_Input_Form_chooseEvent()
{
    $jsonInputfelder = file_json_dec(BE."inputfelder.txt");

    $ret ='<form class="input-box" action="dataSent.php" method="post">';
    $ret .='<input type="text" name="stimme" style="display:none;" value="'.VOICE.'">';
    $ret .= construct_Input_Form_Json_Input_with_values();
    foreach (file_lines(BE.'events.txt') as $line) {
        $t = str_expl("-", $line, 2);
        global $LTE_JSON;
        $LTE_JSON = TAB.$t[0].".json";
        check_write_new_Event($LTE_JSON);
        $jsonContent = file_json_dec($LTE_JSON);
        $ret .= "<h2 style='font-weight: bold;'>$t[1]</h2>";
        $ret .= html_bereits_eingetragen($LTE_JSON);
        $ret .= html_Plätze_übrig($t, $LTE_JSON);
        $ret .= "<hr/>";
    }
    return $ret .= "</form>\n";
}
function setValueToJSON($event)
{
    if (check_entry_existing()) {
        return DEF_TEXT_ENTRYEXISTED;
    }
    $LTE_JSON = TAB.$event.".json";

    if (!check_Event_available($LTE_JSON)) {
        return DEF_TEXT_FAIL;
    }
    return (update_data_Eventlists($LTE_JSON))?DEF_TEXT_SUCCESS:"Fehler beim schreiben in JSON";
}
