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
    define_once("SEP", DIRECTORY_SEPARATOR);

    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $project = "dashboard".SEP."chor".SEP."anmeldung";
    $backend = "backend";
    $subfolder = "tabellen";
    $stock = "stock";
    $accounts = "accounts";

    $linkToRoot = $docRoot.SEP.$project.SEP;
    $linkToBE = $docRoot.SEP.$project.SEP.$backend.SEP;
    $linkToStock = $docRoot.SEP.$project.SEP.$backend.SEP.$stock.SEP;
    $linkToAcc = $docRoot.SEP.$project.SEP.$backend.SEP.$accounts.SEP;

    define_once("ROOT", $linkToRoot);
    define_once("BE", $linkToBE);
    define_once("STOCK", $linkToStock);
    define_once("ACCOUNTS", $linkToAcc);


    define_once("DIR_USER", "users".SEP);
    define_once("SUBFOLDER", $subfolder.SEP);
