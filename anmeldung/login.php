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
if (isset($_GET["show"])) {
    if ($_GET["show"] == "logOut") {
        if (count($_POST)>0) {
            unset($_POST);
        }
        session_unset();
    }
}

$authed = false;
$pwd = (isset($_POST["logInPassword"])) ? preg_replace('/\s+/', '', $_POST["logInPassword"]) : ((isset($_SESSION["auth"]))?$_SESSION["auth"]:"");
$user = (isset($_POST["logInUser"])) ? preg_replace('/\s+/', '', $_POST["logInUser"]) : ((isset($_SESSION["user"]))?$_SESSION["user"]:"");

$fh = fopen('backendAccounts', 'r');
while ($line = fgets($fh)) {
    $expl = explode("::", $line, 2);
    if (password_verify($pwd, preg_replace('/\s+/', '', $expl[1])) && $expl[0] == $user) {
        $_SESSION["auth"] = $pwd;
        $_SESSION["user"] = $user;
        $authed = true;
        break;
    } else {
        $authed = false;
        session_unset();
    }
}
fclose($fh);
