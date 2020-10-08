<?php
require_once 'data.php';
// RETURN BOOL ################################
// TODO mail_to_all()
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
        file_create($user."setup.json")&&
        file_create($user."events.json")&&
        file_create($user."inputfelder.json")&&
        file_create($user."index.php")&&
        dir_create($user.SUBFOLDER)
        ) {
            $setup = file_read(STOCK."setup.json");
            file_handle($user."setup.json", $setup);

            $ipf = file_read(STOCK."inputfelder.json");
            file_handle($user."inputfelder.json", $ipf);

            $index = file_read(STOCK."index.php");
            file_handle($user."index.php", $index);

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
    $allInputs = process_sanatized_inputs();
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
    $accounts = file_lines(ACCOUNTS.'frontendAccounts');
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

// RETURN INT ###############################################################
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

// RETURN STRING ###############################################################
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
function process_sanatized_inputs()
{
    $jsonInputfelder = file_json_dec(UBE."inputfelder.json");
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
    $ret = array();
    $link = getcwd().SEP."setup.json";
    $link = (file_check($link)) ? $link : DIR_USER.get_user().SEP."setup.json" ;
    $link = (file_check($link)) ? $link : STOCK."setup.json" ;
    foreach (file_json_dec($link) as $line) {
        $ret[] = array($line["name"], $line["type"],$line["value"],$line["lable"]);
    }
    return $ret;
}



# NEW EVENT FUNCTIONS:#############################################################################################
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
function process_event_filter_date(array $json, string $von, string $bis)
{
    $json2 = array();
    foreach ($json as $eventKey => $eventArray) {
        $check = process_event_get_param($eventArray, "beginn");
        if (process_event_between_date($von, $bis, $check)) {
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
        return false;
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
    if ($json === null) {
        return false;
    }
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
    if (($temp = process_event_import_csv_groups_ret($line[6], "Sopran")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[7], "Alt")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[8], "Tenor")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[9], "Bass")) !== false) {
        $json[] = $temp;
    }
    if (($temp = process_event_import_csv_groups_ret($line[10], "Stimmbildung")) !== false) {
        $json[] = $temp;
    }
    return $json;
}
function process_event_import_create_csv_filename($line)
{
    $filename = $line[1].$line[2]."(".$line[3].")";
    $filename = str_replace(":", ".", $filename);
    $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
    return $filename = str_stripWSC($filename);
}
function process_event_import_init_csv_open($line)
{
    $dateFromCSV = $line[2]." ".$line[3];

    $date= date_create_from_format("d.m.Y H:i", $dateFromCSV);
    $ab = date_sub($date, date_interval_create_from_date_string(DEF_FREISCHALTEN_AB));
    $date=date_create_from_format("d.m.Y H:i", $dateFromCSV);
    $bis = date_sub($date, date_interval_create_from_date_string(DEF_FREISCHALTEN_BIS));

    return array($ab, $bis);
}
function process_event_check_if_open($event)
{
    $now = date("Y-m-d H:i", time());
    $overParam = process_event_get_param($event, "beginn");
    $over = date("Y-m-d H:i", strtotime($overParam));
    if ($now >= $over) {
        return false;
    }
    $ab = process_event_get_param($event, "freigeschaltet-ab");
    $bis = process_event_get_param($event, "freigeschaltet-bis");
    return process_event_between_date($ab["date"], $bis["date"]);
}
function process_event_between_date($ab, $bis, $between = "now")
{
    $ab = date("Y-m-d H:i", strtotime($ab));
    $bis = date("Y-m-d H:i", strtotime($bis));
    $between = date("Y-m-d H:i", (($between === "now")?time():strtotime($between)));
    if (($between >= $ab) && ($between <= $bis)) {
        return true;
    } else {
        return false;
    }
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
        $dateiname = process_event_import_create_csv_filename($line);
        $tempJson = array();
        $tempJson["titel"]=$line[0];
        $tempJson["dateiname"]=$dateiname;
        $tempJson["art"]=$line[1];
        $tempJson["beginn"]=$line[2]." ".$line[3];
        $tempJson["dauer"]=$line[4];
        $tempJson["ort"]=$line[5];
        $tempJson["gruppen"]=process_event_import_init_csv_groups($line);
        $open = process_event_import_init_csv_open($line);
        $tempJson["freigeschaltet-ab"]= $open[0];
        $tempJson["freigeschaltet-bis"]= $open[1];
        $json[$dateiname] = $tempJson;
    }
    return $json;
}
function process_calc_timestamp($time)
{
    return strtotime($time);
}
function process_calc_timestamp_now()
{
    return strtotime("now");
}
function process_event_import_csv(string $linkToEvent, string $linkToCsV, bool $add = false)
{
    $json = process_event_create_csv($linkToCsV);
    if ($add) {
        $altesJSON = file_json_dec($linkToEvent);
        $json = array_merge($altesJSON, $json);
    }
    return file_json_enc($linkToEvent, $json);
}
// ##################################################################################

function process_mail_send($to, $subject, $message, $from = "webseite@musik.stadtkirche-pforzheim.de")
{
    $message = wordwrap($message, 70, "\r\n");
    $header = "From: Webseite Musik.stadtkirche-pforzheim<{$from}>\r\n";
    $header .= "Reply-To: {$from}\r\n";
    $header .= "Content-Type: text/html\r\n";
    return mail($to, $subject, $message, $header);
}
