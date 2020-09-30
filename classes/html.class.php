<?php
require 'data.class.php';
class Html extends Data
{
    public function __construct()
    {
    }
    private function option($value, $label, $selected)
    {
        if ($selected != $value) {
            return "<option value='$value'>$label</option>";
        } else {
            return "<option value='$value' selected>$label</option>";
        }
    }
    //Parses a string seperated by "" into an html-option-tag
    //z.B. xsvToOption(",", "E,TH,S", "E", "Everything,Headlines Only,Quellen");
    public function xsvToOption($seperator, $xsv, $selected ="", $label="")
    {
        $xsvArray = explode($seperator, $xsv);
        if (!empty($label)) {
            $labelArray = explode($seperator, $label);
            if (sizeof($xsvArray)!=sizeof($labelArray)) {
                return "Anzahl der Label und der Eingabe stimmt nicht überein!!";
            }
        }
        for ($i=0; $i < sizeof($xsvArray); $i++) {
            if (!empty($label)) {
                echo (!empty($selected)) ? $this->option($xsvArray[$i], $labelArray[$i], $selected) : "<option value='$xsvArray[$i]'>$labelArray[$i]</option>" ;
            } else {
                echo (!empty($selected)) ? $this->option($xsvArray[$i], $xsvArray[$i], $selected) : "<option value='$xsvArray[$i]'>$xsvArray[$i]</option>" ;
            }
        }
    }


    public static function listAllFilesOf(string $linkToDir)
    {
        $files=Dir::listFiles($linkToDir);
        var_dump($files);
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
                    $t = explode("-", $line, 2);
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
}

Html::listAllFilesOf('C:\xampp\htdocs\dashboard');
var_dump(Dir::listFiles('C:\xampp\htdocs\dashboard'));
