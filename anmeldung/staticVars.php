<?php
    declare(strict_types = 1);

    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    define("URL", $url);

    $sep = DIRECTORY_SEPARATOR;
    //define("SEP", DIRECTORY_SEPARATOR);
    define("SEP", "/");

    $docRoot = $_SERVER["DOCUMENT_ROOT"];
    $project = "dashboard".SEP."choranmeldung";
    $backend = "anmeldung";
    $subfolder = "tabellen";

    $linkToRoot = $docRoot.SEP.$project.SEP;
    $linkToBE = $docRoot.SEP.$project.SEP.$backend.SEP;
    $linkToTab = $docRoot.SEP.$project.SEP.$backend.SEP.$subfolder.SEP;
    define("ROOT", $linkToRoot);
    define("BE", $linkToBE);
    define("TAB", $linkToTab);
