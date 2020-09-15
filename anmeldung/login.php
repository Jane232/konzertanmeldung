<?php
function showLogin($bool)
{
    if ($bool) {
        echo "<center><br><br><br><a href='index.php'>Zurück zum LogIn</a></center>";
    } else {
        echo '<center>
  <br>
  <form class="input-box" action="" method="post">
  <label for="logInUser"> Anmeldung für das Backend</label>
  <input type="text" name="logInUser" placeholder="Username:" required>
  <input type="password" name="logInPassword" placeholder="Passwort:" required>
    <button type="submit" name="passwordAbschicken">LogIn</button>
  </form></center>';
    }
}

$_SESSION["auth"] = (isset($_SESSION["auth"]))?$_SESSION["auth"]:"";
$_SESSION["user"] = (isset($_SESSION["user"]))?$_SESSION["user"]:"";
if (isset($_GET["show"])) {
    if ($_GET["show"] == "logOut") {
        if (isset($_POST["logInPassword"])) {
            unset($_POST["logInPassword"]);
        }
        $_SESSION["auth"] = "";
        $_SESSION["user"] = "";
    }
}
$authed = false;
if ((isset($_POST["logInPassword"])&&isset($_POST["logInUser"]))||$_SESSION["auth"] != "") {
    $pwd = $_SESSION["auth"] = (isset($_POST["logInPassword"])) ? preg_replace('/\s+/', '', $_POST["logInPassword"]) : $_SESSION["auth"];
    $user = $_SESSION["user"] = (isset($_POST["logInUser"])) ? preg_replace('/\s+/', '', $_POST["logInUser"]) : $_SESSION["user"];
    $fh = fopen('backendAccounts', 'r');
    while ($line = fgets($fh)) {
        $expl = explode("::", $line, 2);
        $pwdFromFile = preg_replace('/\s+/', '', $expl[1]);
        $authed = password_verify($pwd, $pwdFromFile);
        if ($authed && $expl[0] == $user) {
            break;
        } else {
            $authed = false;
            session_unset();
        }
    }
    fclose($fh);
}
