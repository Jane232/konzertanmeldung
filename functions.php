
<?php
//returns a html option tag
function option($value, $label, $selected)
{
    if ($selected != $value) {
        return "<option value='$value'>$label</option>";
    } else {
        return "<option value='$value' selected>$label</option>";
    }
}
//Parses a string seperated by "" into an html-option-tag
//z.B. xsvToOption(",", "E,TH,S", "E", "Everything,Headlines Only,Quellen");
function xsvToOption($seperator, $xsv, $selected ="", $label="")
{
    $xsvArray = explode($seperator, $xsv);
    $i = 0;
    $s = sizeof($xsvArray);
    if (!empty($label)) {
        $labelArray = explode($seperator, $label);
        if (sizeof($xsvArray)!=sizeof($labelArray)) {
            echo"Anzahl der Label und der Eingabe stimmt nicht überein!!";
            return;
        }
    }
    while ($i < sizeof($xsvArray)) {
        if (!empty($label)) {
            echo (!empty($selected)) ? option($xsvArray[$i], $labelArray[$i], $selected) : "<option value='$xsvArray[$i]'>$labelArray[$i]</option>" ;
        } else {
            echo (!empty($selected)) ? option($xsvArray[$i], $xsvArray[$i], $selected) : "<option value='$xsvArray[$i]'>$xsvArray[$i]</option>" ;
        }
        $i += 1;
    }
}

// Funktion die die Zeilenanzahl des angegebenen .csv-Files zurückgibt
function getLines($linkToFile)
{
    if (is_file(preg_replace('/\s+/', '', $linkToFile.".csv"))) {
        return (int) $linecount = count(file($linkToFile.".csv"));
    } else {
        return 0;
    }
}

function listAllFilesOf($link, $linkToTab)
{
    // Anzeigen der verbliebenen Dateien
    $myDirectory = opendir($link.$linkToTab);
    // Alle Inhalte des Dirs (/tabellen)
    $files = [];
    while ($entryName = readdir($myDirectory)) {
        $dirArray[] = $entryName;
        // Filtern, dass nur Dateien angezeigt werden (Keine Ordner)
        if (is_file($linkToTab.$entryName)) {
            $files[] = $entryName;
        }
    }
    closedir($myDirectory);
    // Wenn mind. eine Datei existiert, dann wird eine Liste mit den Dateien erstellt und ausgegeben.
    if (sizeof($files)>0) {
        echo "<h1>Folgende Dateien befinden sich im Ornder:</h1><div style='width:50%; margin: 0 25%;'><ul>";
        foreach ($files as $doc) {
            // Eventname des Files suchen
            $name = str_replace(array(".json",".csv"), "", $doc);
            $nameOfFile = "";
            $fh = fopen("events.txt", "r");
            // Über alle Event-Zeilen iterieren
            while ($line = fgets($fh)) {
                $t = explode("-", $line);
                if ($t[0] == $name) {
                    $nameOfFile .= $t[1];
                }
            }
            fclose($fh);
            echo "<li>$doc ($nameOfFile)</li>";
        }
        echo '</ul> </div>';
    }
}

function deleteDir($dirPath)
{
    $it = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($dirPath);
}
function StringLengthStrip($string, $maxInputLenght)
{
    if (strlen($string) > $maxInputLenght) {
        return substr($string, 0, $maxInputLenght);
    } else {
        return $string;
    }
}
function fileToArray($link)
{
    $fh = fopen($link, 'r');
    while ($line = fgets($fh)) {
        $array[] = $line;
    }
    fclose($fh);
    return $array;
}
function files($link, $string, $mode)
{
    $file = fopen($link, $mode);
    fwrite($file, $string);
    fclose($file);
}
