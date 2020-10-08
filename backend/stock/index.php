<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
@session_start();
require_once("../../backend/config.php");
require_once("../../layers_backend/html.php");
html_initialize_setup_var();

?>

<!DOCTYPE html>
<html data-theme="hell">
 <head>
   <meta charset="utf-8">
   <title> <?php echo $titleOfBackend = (isset($_GET["show"])) ? html_namesOfSites($_GET["show"]) : "Backend - Übersicht"; ?> </title>
   <link rel="stylesheet" href="../../style.css">
   <link href="/site/templates/images/favicon.ico" rel="shortcut icon">
  </head>
  <body>
    <?php
    echo html_header(); // MENU
    if (isset($_GET["show"])) { // Je nach Menü-Auswahl werden verschiedene Sachen gezeigt
        show($_GET["show"]);
    }

?>
<!--Fixer Zurückknopf-->
<span style="position: fixed; bottom: 0px; margin-bottom: 0px; float:right; margin:0 10% 5vh 75%;"><a href="index.php">Zurück zur Übersicht</a></span>
<br><br>
</body>
</html>
