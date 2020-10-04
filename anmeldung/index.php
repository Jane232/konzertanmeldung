<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
@session_start();

require_once("login.php");

$titleOfBackend = "Backend - LogIn";

if ($authed == true) {
    //Einbindung der Funktionen
    // Variable für HTML-Titel der Seite
    require_once("staticVars.php");

    function namesOfSites($show)
    {
        switch ($show) {
    case 'events':
      $titleOfBackend = "Backend - Events bearbeiten";
      break;
    case 'lists':
      $titleOfBackend = "Backend - Listen ausgeben";
      break;
    case 'deleteList':
      $titleOfBackend = "Backend - Listen löschen";
      break;
    case 'setup':
      $titleOfBackend = "Backend - Einstellungen";
      break;
    case 'addFrontendUser':
      $titleOfBackend = "Backend - Nutzer hinzufügen (Frontend)";
      break;
    case 'addBackendUser':
      $titleOfBackend = "Backend - Nutzer hinzufügen (Backend)";
      break;
    case 'inputconfig':
      $titleOfBackend = "Backend - Eingabefelder bearbeiten";
      break;
    case 'deleteEntry':
      $titleOfBackend = "Backend - Eintrag löschen";
      break;
    default:
      $titleOfBackend = "Backend - Übersicht";
      break;
    }
        return $titleOfBackend;
    }

    $titleOfBackend = (isset($_GET["show"])) ? namesOfSites($_GET["show"]) : "Backend - Übersicht";
}
?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo $titleOfBackend; ?> </title>
   <link rel="stylesheet" href="../style.css">
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>
  <body>
    <?php
if ($authed == true) {
    require_once("backendFunctions.php");
    echo '
    <style>.nav-div{
      width: 80%;
      display: grid;
      grid-template-columns: auto auto auto auto auto;
    }
    .nav-div a{
      margin: 0.6em 1em;
    }
    @media screen and (max-width: 900px)
    {
      .nav-div{
        grid-template-columns: auto auto auto;
      }
      .nav-div a{
        margin: 0.5em 0;
      }
    }</style>
  <center style="margin: 20px 0 0 0;">
    <!--LOGO-->
    <a href="../"><img src="../Musik-Stempel rund.png" alt="Logo" ></a>
    <!--NAV-->
    <div class="nav-div">
      <a href="index.php?show=events">Events bearbeiten</a>
      <a href="index.php?show=lists">Listen ausgeben</a>
      <a href="index.php?show=deleteList">Liste löschen</a>
      <a href="index.php?show=setup">Einstellungen</a>
      <a href="index.php?show=addFrontendUser">Nutzer hinzufügen (Frontend)</a>
      <a href="index.php?show=addBackendUser">Nutzer hinzufügen (Backend)</a>
      <a href="index.php?show=deleteEntry">Eintrag Löschen</a>
      <a href="index.php?show=inputconfig">Eingabefelder bearbeiten</a>
      <a href="index.php?show=logOut">LogOut</a>
    </div>
  </center>
 <div class="pageBody">
';
    if (isset($_GET["show"])) { // Je nach Menü-Auswahl werden verschiedene Sachen gezeigt
        show($_GET["show"]);
    }
} else {
    echo '<center style="margin: 20px 0 0 0;"><a href="../"><img src="../Musik-Stempel rund.png" alt="Logo" ></a></center>';
    showLogin(isset($_GET["show"]));
}
?>
<!--Fixer Zurückknopf-->
<span style="position: fixed; bottom: 0px; margin-bottom: 0px; float:right; margin:0 10% 5vh 75%;"><a href="index.php">Zurück zur Übersicht</a></span>
<br><br>

</div>
</body>
</html>
