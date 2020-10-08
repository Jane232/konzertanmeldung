<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
@session_start();
require_once("config.php");
require_once("../layers_backend/html.php");
html_initialize_setup_var();
?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo $titleOfBackend = (isset($_GET["show"])) ? html_namesOfSites($_GET["show"]) : "Backend - Übersicht"; ?> </title>
   <link rel="stylesheet" href="../style.css">
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>
  <body>
    <?php
    $link = (file_exists("../../Musik-Stempel rund.png")) ? "../../" : "../" ;
    $name = basename(getcwd());

    echo '<style>.nav-div{
    width: 80%;
    display: grid;
    grid-template-columns: auto auto auto auto;
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
  <!--LOGO--><a href="'.$link.'"><img src="'.$link.'Musik-Stempel rund.png" alt="Logo" ></a>
  <!--NAV--><br><h1>'.$name.'</h1>
  <div class="nav-div">
    <a href="index.php?show=setup">Einstellungen</a>
    <a href="index.php?show=addFrontendUser">Nutzer hinzufügen (Frontend)</a>
    <a href="index.php?show=addBackendUser">Nutzer hinzufügen (Backend)</a>
  </div>
</center>';
    if (isset($_GET["show"])) { // Je nach Menü-Auswahl werden verschiedene Sachen gezeigt
        show($_GET["show"]);
    }

?>
<!--Fixer Zurückknopf-->
<span style="position: fixed; bottom: 0px; margin-bottom: 0px; float:right; margin:0 10% 5vh 75%;"><a href="index.php">Zurück zur Übersicht</a></span>
<br><br>

</div>
</body>
</html>
