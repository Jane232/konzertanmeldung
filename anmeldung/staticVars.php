<?php     // korekte URL
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https://" : "http://";
    $url .= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    // '/' oder '\'
    $sep = DIRECTORY_SEPARATOR;
    // Name des Sub-Ordners
    $subfolder = "tabellen";
    // Link zu Sub-Ornder
    $linkToTab = $subfolder.$sep;
