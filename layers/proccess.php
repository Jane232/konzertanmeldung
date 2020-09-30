<?php
require_once 'data.php';
// RETURN BOOL ################################
function define_voice(string $voice)
{
    return define("VOICE", $voice);
}
function check_write_new_Event($LTE_JSON)
{
    if (!file_check($LTE_JSON)) {
        $jsonContent = '{"Sopran": {},"Alt": {},"Tenor": {},"Bass": {}}';
        file_handle($LTE_JSON, $jsonContent);
        return true;
    } else {
        return false;
    }
}
function check_Event_available($LTE_JSON)
{
    return calc_slots_used($LTE_JSON) < calc_maxZuschauer();
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
function update_data_Eventlists($LTE_JSON)
{
    $allInputs = sanatized_inputs();
    return add_data_Eventlists($LTE_JSON, $allInputs);
}
function add_data_Eventlists($LTE_JSON, $allInputs)
{
    $jsonContent = file_json_dec($LTE_JSON);
    $jsonContent[VOICE][] = $allInputs;
    return (file_json_enc($LTE_JSON, $jsonContent) === false) ?false : true;
}
// RETURN INT ################################
function calc_maxZuschauer()
{
    $varName = switchVoice(VOICE);
    return (int) (isset($$varName))?$$varName:DEF_MAXZUSCHAUER;
}
function calc_slots_used($LTE_JSON)
{
    $jsonContent = file_json_dec($LTE_JSON);
    return (int) sizeof($jsonContent[VOICE]);
}
function calc_slots_left($LTE_JSON)
{
    return (int) calc_maxZuschauer()-calc_slots_used($LTE_JSON);
}

// RETURN STRING ################################
function switchVoice($voice)
{
    switch ($voice) {
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
    $jsonInputfelder = file_json_dec(BE."inputfelder.txt");
    foreach ($jsonInputfelder["input"] as $key => $val) {
        $input_from_user = $_POST[$key];

        $sanKey = str_stripLenght($key);
        $sanVal = str_stripLenght($input_from_user);

        $allInputs[$sanKey] = $sanVal;
    }
    return $allInputs;
}
