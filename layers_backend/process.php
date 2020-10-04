<?php
require_once(ROOT."layers_backend/data.php");
require_once(ROOT."layers/process.php");
function process_get_validate_event()
{
    return (process_validate_event($_POST["event"])) ? $_POST["event"] : "";
}
function process_validate_event($givenEvent)
{
    foreach (file_lines('events.txt') as $event) {
        $t = str_expl("-", $event, 2);// An "-" Spalten
        if ($t[0] == $givenEvent) { //Nur den ausgewählten Namen speichern
          return true;
        }
    }
    return false;
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
function process_delete_file($link)
{
    try {
        return file_delete($link);
    } catch (\Exception $e) {
        return "Error:".$e->getMessage();
    }
}
