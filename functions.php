
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
    $linecount = 0;
    $handle = @fopen(preg_replace('/\s+/', '', $linkToFile).".csv", "r");
    if ($handle != false) {
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }
    } else {
        $linecount = 1;
    }
    @fclose($handle);
    return (int)$linecount;
}
