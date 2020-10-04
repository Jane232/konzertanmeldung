<?php
    declare(strict_types = 1);
    if (!function_exists("define_once")) {
        function define_once(string $name, $val)
        {
            if (!defined($name)) {
                define($name, $val);
            }
        }
    }

    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    define_once("URL", $url);

    $sep = DIRECTORY_SEPARATOR;
    //define("SEP", DIRECTORY_SEPARATOR);
    define_once("SEP", "/");

    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $project = "dashboard".SEP."choranmeldung";
    $backend = "anmeldung";
    $subfolder = "tabellen";

    $linkToRoot = $docRoot.SEP.$project.SEP;
    $linkToBE = $docRoot.SEP.$project.SEP.$backend.SEP;
    $linkToTab = $docRoot.SEP.$project.SEP.$backend.SEP.$subfolder.SEP;

    define_once("SUBFOLDER", $subfolder.SEP);
    define_once("ROOT", $linkToRoot);
    define_once("BE", $linkToBE);
    define_once("TAB", $linkToTab);
    define_once("DIR_USER", "users".SEP);
