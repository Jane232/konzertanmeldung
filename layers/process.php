<?php
require_once 'data.php';
// RETURN BOOL ################################
// TODO mail()
// TODO mail_to_all()
// TODO mail_after_registration()
// TODO mail_to_list()
function process_reload_page()
{
    echo "<script type='text/javascript'>document.location.href='".URL."';</script>";
}
function define_group(string $group)
{
    return define("GROUP", $group);
}
function define_user()
{
    define("USER", get_user());
    return define("UBE", DIR_USER.get_user().SEP);
}
function check_write_new_Event(string $LTE_JSON, string $name)
{
    if (!file_check($LTE_JSON)) {
        $eventJSON = process_event_get();
        $actualJSONEvent = process_event_get_by_key($eventJSON, $name);
        if ($actualJSONEvent === false) {
            throw new \Exception(" enthält nicht".$name, 1);
            return false;
        }
        $groupsOfActualEvent = process_event_get_groups($actualJSONEvent);
        $newJSON = array();
        foreach ($groupsOfActualEvent as $gruppe) {
            $newJSON[$gruppe] = array();
        }
        file_json_enc($LTE_JSON, $newJSON);
        return true;
    } else {
        return false;
    }
}
function check_Event_available(string $LTE_JSON, string $eventKey)
{
    return calc_slots_used($LTE_JSON) < calc_maxZuschauer($eventKey);
}
function create_md5_token()
{
    $token = "";
    foreach ($_POST as $value) {
        $token .= $value;
    }
    return md5($token);
}
function store_md5_token()
{
    $md5 = create_md5_token();
    return $_SESSION[$md5] = $md5;
}
function check_entry_existing()
{
    if (is_session_started() === false) {
        session_start();
    }
    if (isset($_SESSION[create_md5_token()])) {
        return true;
    } else {
        store_md5_token();
        return false;
    }
}
function check_user_dir()
{
    return dir_check(DIR_USER.get_user());
}
function create_user_dir()
{
    $user = DIR_USER.get_user().SEP;
    if (dir_create($user)) {
        if (
        file_create($user."setup.txt")&&
        file_create($user."event.txt")&&
        file_create($user."inputfelder.txt")&&
        dir_create($user.SUBFOLDER)
        ) {
            foreach (file_lines(BE."setup.txt") as $line) {
                file_handle($user."setup.txt", $line, "a");
            }
            foreach (file_lines(BE."inputfelder.txt") as $line) {
                file_handle($user."inputfelder.txt", $line, "a");
            }
            return true;
        }
    }
    return false;
}
function check_create_user_dir()
{
    if (!check_user_dir()) {
        create_user_dir();
    }
}
function update_data_Eventlists($LTE_JSON)
{
    $allInputs = sanatized_inputs();
    return add_data_Eventlists($LTE_JSON, $allInputs);
}
function add_data_Eventlists($LTE_JSON, $allInputs)
{
    $jsonContent = file_json_dec($LTE_JSON);
    $jsonContent[GROUP][] = $allInputs;
    return (file_json_enc($LTE_JSON, $jsonContent) === false) ?false : true;
}
function user_logout()
{
    if (count($_POST)>0) {
        unset($_POST);
    }
    return session_unset();
}
function user_check_login(string $user, string $pwd)
{
    $accounts = file_lines(BE.'frontendAccounts');
    foreach ($accounts as $account) {
        $expl = explode("::", $account, 2);
        if (password_verify($pwd, str_stripWSC($expl[1])) && $expl[0] == $user) {
            $_SESSION["auth"] = base64_encode($pwd);
            $_SESSION["user"] = $user;
            return true;
        }
    }
    session_unset();
    return false;
}

// RETURN INT ################################
function calc_maxZuschauer($eventKey)
{
    $events=process_event_get();
    $event = $events[$eventKey];
    foreach ($event["gruppen"] as $gruppe) {
        if ($gruppe["name"]==GROUP) {
            return (int) $gruppe["size"];
        }
    }
    $varName = switchGroup(GROUP);
    return (int) (isset($$varName))?$$varName:DEF_MAXZUSCHAUER;
}
function calc_slots_used($LTE_JSON)
{
    $jsonContent = file_json_dec($LTE_JSON);
    return (int) sizeof($jsonContent[GROUP]);
}
function calc_slots_left(string $LTE_JSON, string $eventKey)
{
    return (int) calc_maxZuschauer($eventKey)-calc_slots_used($LTE_JSON);
}

// RETURN STRING ################################
function switchGroup($group)
{
    switch ($group) {
    case 'Sopran':
    return "platzSopran";
    break;
    case 'Alt':
    return "platzAlt";
    break;
    case 'Tenor':
    return "platzTenor";
    break;
    case 'Bass':
    return "platzBass";
    break;
  }
}
function sanatized_inputs()
{
    $jsonInputfelder = file_json_dec(UBE."inputfelder.txt");
    foreach ($jsonInputfelder["input"] as $key => $val) {
        $input_from_user = $_POST[$key];

        $sanKey = str_stripLenght($key);
        $sanVal = str_stripLenght($input_from_user);

        $allInputs[$sanKey] = $sanVal;
    }
    return $allInputs;
}
function get_user()
{
    $user = (isset($_SESSION["user"]))?$_SESSION["user"]:"";
    return $user = (isset($_POST["logInUser"])) ? str_stripWSC($_POST["logInUser"]) : $user;
}
function get_pwd()
{
    $pwd = (isset($_SESSION["auth"]))?base64_decode($_SESSION["auth"]):"";
    return $pwd = (isset($_POST["logInPassword"])) ? str_stripWSC($_POST["logInPassword"]) : $pwd;
}
function process_read_setup()
{
    $explArr = array();
    foreach (file_lines(BE."setup.txt") as $line) {
        $explArr[] = str_expl("--", $line);
    }
    return $explArr;
}



# NEW EVENT FUNCTIONS:
//Checks if wanted groups match with given groups
function process_event_check_groups(array $eventArray, array $groups)
{
    $names = array();
    foreach ($eventArray["gruppen"] as $gruppe) {
        $names[] = $gruppe["name"];
    }
    $names = process_event_get_groups($eventArray);
    $intersections = array_intersect($names, $groups);
    if (arr_count($intersections) == arr_count($groups)) {
        return true;
    }
    return false;
}
function process_event_filter_type($value='')
{
    // code...
}
// Filters all given Events for those with wanted groups
function process_event_filter_groups(array $json, $groups)
{
    $json2 = array();
    if (!is_array($groups)) {
        $groups = array($groups);
    }
    foreach ($json as $eventKey => $eventArray) {
        if (process_event_check_groups($eventArray, $groups)) {
            $json2[$eventKey]=$eventArray;
        }
    }

    return $json2;
}
function process_event_check_attr(array $event, string $attr, string $type)
{
    return ($event[$attr]==$type)? true:false;
}
function process_event_filter(array $json, string $attr, string $type)
{
    $events= array();
    foreach ($json as $eventKey => $eventArray) {
        if (process_event_check_attr($eventArray, $attr, $type)) {
            $events[$eventKey] = $eventArray;
        }
    }
    return $events;
}

function process_event_get_by_key(array $json, string $key)
{
    if (!isset($json[$key])) {
        foreach ($json as $nr => $arr) {
            if (isset($arr[$key])) {
                dump($arr[$key]);
            }
        }
        return $arr;
    } else {
        return $json[$key];
    }
}

function process_event_get_all_events(array $json)
{
    $events= array();
    foreach ($json as $eventKey => $eventArray) {
        $events[] = $eventArray;
    }
    return $events;
}
function process_event_get_all_eventnames(array $json)
{
    $eventnames= array();
    foreach ($json as $eventKey => $eventArray) {
        $eventnames[] = $eventKey;
    }
    return $eventnames;
}
function process_event_get_groups_of_json($json)
{
    $groups = $ret = array();
    foreach ($json as $key => $value) {
        foreach (process_event_get_groups($value) as $groupName) {
            $groups[$groupName] = "";
        }
    }
    foreach ($groups as $name => $empty) {
        $ret[] = $name;
    }
    return $ret;
}
function process_event_get_groups(array $jsonOfEvent)
{
    $names = array();
    foreach ($jsonOfEvent["gruppen"] as $gruppe) {
        $names[] = $gruppe["name"];
    }
    return $names;
}
function process_event_get()
{
    return file_json_dec(UBE."events.json");
}
//returns array of the Attribute "Art";
function process_event_get_availabble_eventart_for_user()
{
    $json = process_event_get();
    $artOfEvents = $arts = array();
    foreach ($json as $key => $array) {
        $artOfEvents[$array["art"]]=true;
    }
    foreach ($artOfEvents as $key => $value) {
        $arts[] = $key;
    }
    return $arts;
}


function process_event_get_param($jsonOfEvent, $param)
{
    return (isset($jsonOfEvent[$param])) ? $jsonOfEvent[$param] : "NaN" ;
}
function process_event_check_if_key_Val($json)
{
    return (array_key_exists("titel", $json))?false:true;
}



function process_event_import_csv_groups_ret($line, $name)
{
    if (str_count($line)<1) {
        return false;
    } else {
        $line = (int) $line;
    }
    if (is_int($line)) {
        return array("name"=>$name,"size"=>$line);
    }
}
function process_event_import_init_csv_groups($line)
{
    $json = array();
    if (($temp = process_event_import_csv_groups_ret($line[7], "Sopran")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[8], "Alt")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[9], "Tenor")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[10], "Bass")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[11], "Stimmbildung")) !== false) {
        $json[] = $temp;
    }
    return $json;
}
function process_event_create_csv(string $linkToCsV)
{
    $dataArray = file_csv_array($linkToCsV);
    //löschen der 1. 2 zeilen
    unset($dataArray[0]);
    unset($dataArray[1]);
    $dataArray = array_values($dataArray);
    $json = array();
    foreach ($dataArray as $line) {
        $tempJson = array();
        $tempJson["titel"]=$line[0];
        $tempJson["dateiname"]=$line[1];
        $tempJson["art"]=$line[2];
        $tempJson["beginn"]=$line[3]."_".$line[4];
        $tempJson["dauer"]=$line[5];
        $tempJson["ort"]=$line[6];
        $tempJson["gruppen"]=process_event_import_init_csv_groups($line);
        $json[$line[1]] = $tempJson;
    }
    return $json;
}
function process_event_import_csv(string $linkToEvent, string $linkToCsV)
{
    $json = process_event_create_csv($linkToCsV);
    return file_json_enc($linkToEvent, $json);
}
