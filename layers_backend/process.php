<?php
require_once(ROOT."layers_backend/data.php");
require_once(ROOT."layers/process.php");
function process_delete_file($link)
{
    try {
        return file_delete($link);
    } catch (\Exception $e) {
        return "Error:".$e->getMessage();
    }
}
function process_get_json_keys($json)
{
    foreach ($json as $key => $value) {
        $keyArray[]=$key;
    }
    if (empty($keyArray)) {
        return false;
    }
    return $keyArray;
}

// EVENTS ######################################################################################

function process_event_delete($events)
{
    $datenameToDelete = $_POST["deleteEvent"];
    if (!isset($_POST["deleteVerify"])) {
        return "Das Löschen von ".$datenameToDelete." wurde nicht bestätigt!";
    }
    if (!isset($events[$datenameToDelete])) {
        return "Das zu löschende Event existiert nicht oder wurde umbenannt!";
    }
    unset($events[$datenameToDelete]);
    return (file_json_enc("events.json", $events))?$datenameToDelete." wurde erfolgreich gelöscht": "Es gab ein Problem beim Löschen von ".$datenameToDelete;
}
function process_event_change($events)
{
    $dateinameToChange = $_POST["change"];
    if (($eventToChange = process_event_get_by_key($events, $dateinameToChange))=== false) {
        return "Das zuverändernde Event existiert nicht!";
    }
    $function = function ($key, $value, $parentKey= "") {
        if (isset($_POST[$key."_".$parentKey])) {
            $temp = $_POST[$key."_".$parentKey];
            return (is_numeric($temp)) ? (int) $temp : $temp ;
        }
        $temp = (isset($_POST[$key])) ? $_POST[$key] : $value;
        return (is_numeric($temp)) ? (int) $temp : $temp ;
    };

    $newJson = process_array_iterate_over_n_dim($eventToChange, $function);
    if ($dateinameToChange == $newJson["dateiname"]) {
        $events[$dateinameToChange]=$newJson;
    } else {
        unset($events[$dateinameToChange]);
        $events[$newJson["dateiname"]] = $newJson;
    }
    return (file_json_enc("events.json", $events))?"<center><br>".$dateinameToChange." wurde erfolgreich geändert</center>": "<center>Es gab ein Problem beim Ändern von ".$dateinameToChange."</center>";
}
function process_backend_event_get()
{
    return file_json_dec("events.json");
}
function process_get_validate_event()
{
    return (process_validate_event($_POST["event"])) ? $_POST["event"] : "";
}
function process_validate_event($givenEvent)
{
    return ((process_event_get_by_key(process_backend_event_get(), rtrim($givenEvent, ".json")))=== false) ? false : true ;
}

// ARRAY #######################################################################

function process_array_assoc_sort($array, $oderedKeys)
{
    $ret = array();
    foreach ($oderedKeys as $k) {
        if (isset($array[$k])) {
            $ret[$k] = $array[$k];
        }
    }
    return $ret;
}
function process_array_iterate_over_n_dim($array, $function, $parentKey="")
{
    $ret = array();
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $ret[$key] = process_array_iterate_over_n_dim($value, $function, $key);
        } else {
            $ret[$key] = (isset($parentKey)) ? $function($key, $value, $parentKey) : $function($key, $value) ;
        }
    }
    return $ret;
}

// CSV #########################################################################

function process_csv_from_events()
{
    $lable = $name = "";
    $dirname = basename(getcwd());
    foreach (file_json_dec(ROOT.DIR_USER.$dirname.SEP."events.json") as $key => $tempExpl) {
        // Lable und Name spliten und in verschiedene Variablen
        $name .= $key.",";
        $lable .= $tempExpl["titel"]." ( ".$key." ),";
    }
    $name = rtrim($name, ",");
    $lable = rtrim($lable, ",");
    return array("name"=>$name,"lable"=>$lable);
}
function process_csv_list_of_Files($link)
{
    $lable = $name = "";
    $dirname = basename(getcwd());
    $jsonEvents = file_json_dec(ROOT.DIR_USER.$dirname.SEP."events.json");
    foreach (dir_listFiles($link) as $key) {
        if (strpos($key, ".csv") === false) {
            $name .= $key.",";
            $key = rtrim($key, ".json");
            $lable .= $jsonEvents[$key]["titel"]." ( ".$key." ),";
        }
    }
    $name = rtrim($name, ",");
    $lable = rtrim($lable, ",");
    return array("name"=>$name,"lable"=>$lable);
}
function process_write_csv_file($json, $link)
{
    //return bool
    $keyArray = process_get_json_keys($json);
    if ($keyArray !== false) {
        $csv = process_create_csv_from_json_over_array($json, $keyArray);
        if (!file_check($link.".csv")) {
            file_create($link.".csv");
        }
        return file_handle($link.".csv", $csv);
    }
    return false;
}

function process_create_csv_from_json_over_array(array $json, array $array)
{
    $csv = "";
    foreach ($array as $segment) {
        //$csv .= $segment."\n";
        foreach ($json[$segment] as $number =>$array) {
            $csv .= $segment;
            foreach ($array as $bezeichnug =>$wert) {
                $csv .= ",".$wert;
            }
            $csv .= "\n";
        }
    }
    return $csv;
}
