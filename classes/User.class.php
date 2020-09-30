<?php
require_once 'data.class.php';
class User
{
    public function __construct($data, $pwd, $user)
    {
        $this->data = $data;
        $this->pwd = $pwd;
        $this->user = $user;
    }
    public function logIn()
    {
        $accounts = $this->data->file->lines("anmeldung".SEP."backendAccounts");
        foreach ($accounts as $account) {
            $expl = $this->data->str->split("::", $account, 2);
            if (password_verify($this->pwd, $this->data->str->stripWSC($expl[1])) && $expl[0] == $this->user) {
                return true;
            } else {
                $authed = false;
                session_unset();
            }
        }
        return false;
    }
}
/*
$pwd = (isset($_POST["logInPassword"])) ? str()->stripWSC($_POST["logInPassword"]) : ((isset($_SESSION["auth"]))?$_SESSION["auth"]:"");
$user = (isset($_POST["logInUser"])) ? str()->stripWSC($_POST["logInUser"]) : ((isset($_SESSION["user"]))?$_SESSION["user"]:"");
$sec = new User($data, $pwd, $user);
$sec->logIn();
*/
