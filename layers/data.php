<?php
require_once 'anmeldung'.DIRECTORY_SEPARATOR.'staticVars.php';

function dir_delete(string $dirPath)
{
    if (!(dir_check($dirPath))) {
        throw new \Exception("Dir not existing!", 1);
        return false;
    }
    if (dir_check($dirPath)) {
        try {
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
        } catch (\Exception $e) {
            throw new Exception("Error while deleting Dir!");
            return false;
        }
    } else {
        throw new Exception("Dir was not existing!");
    }
    return true;
}

function dir_create(string $dirPath)
{
    if (!dir_check($dirPath)) {
        if (!mkdir($dirPath, 0777, true)) {
            throw new Exception('Ordner konnte nicht erstellt werden!');
        }
    } else {
        throw new Exception("Folder was allready existing");
    }
    return true;
}

function dir_check(string $dirPath)
{
    return is_dir($dirPath);
}

function dir_listFiles(string $dirPath)
{
    if (!(dir_check($dirPath))) {
        throw new \Exception("Dir not existing!", 1);
        return false;
    }
    $dirHandle = opendir($dirPath);
    $files = [];
    while ($objektName = readdir($dirHandle)) {
        if (is_file($dirPath) && $objektName !== "." && $objektName !== "..") {
            $files[] = $objektName;
        }
    }
    closedir($dirHandle);
    return $files;
}
function dir_listAll(string $dirPath)
{
    if (!(dir_check($dirPath))) {
        throw new \Exception("Dir not existing!", 1);
        return false;
    }
    $dirHandle = opendir($dirPath);
    $files = [];
    while ($objektName = readdir($dirHandle)) {
        if ($objektName !== "." && $objektName !== "..") {
            $dirArray[] = $objektName;
        }
    }
    closedir($dirHandle);
    return $dirArray;
}

function file_create(string $link)
{
    if ((file_check($link))) {
        throw new \Exception("File is allready existing!", 1);
        return false;
    }
    $file = fopen($link, "w");
    if (fopen($link, "w") === false) {
        return false;
    }
    fwrite($file, "");
    fclose($file);
    return true;
}
function file_check(string $link)
{
    return is_file($link);
}
function file_handle(string $link, string $string, string $mode = "w")
{
    if (!file_check($link)) {
        throw new \Exception("File not existing!", 1);
        return false;
    }
    if (!is_string($string)) {
        throw new \Exception("String not properly formated or existing!", 1);
        return false;
    }
    $file = fopen($link, $mode);
    fwrite($file, $string);
    fclose($file);
    return true;
}

function file_delete(string $link)
{
    if (!file_check($link)) {
        throw new \Exception("File not existing!", 1);
        return false;
    }
    return (unlink($link))?true:false;
}
function file_read(string $link)
{
    if (!file_check($link)) {
        throw new \Exception("File ($link) not existing!", 1);
        return false;
    }
    return file_get_contents($link);
}
function file_lines(string $link)
{
    if (!file_check($link)) {
        //throw new \Exception("File not existing!", 1);
        return $array = array();
    }
    $fh = fopen($link, 'r');
    while ($line = fgets($fh)) {
        $array[] = $line;
    }
    fclose($fh);
    return $array;
}

function file_lineCount(string $link)
{
    return count(file_lines($link));
}

function str_check(string $string)
{
    return is_string($string);
}
function str_expl(string $string, string $exploder, int $limit = -1)
{
    if (!str_check($string) && !str_check($exploder)) {
        throw new \Exception("String not formated properly!", 1);
        return false;
    }
    if ($limit > 0) {
        return explode($string, $exploder);
    } else {
        return explode($string, $exploder, $limit);
    }
}
function str_stripWSC(string $string)
{
    if (!str_check($string)) {
        throw new \Exception("String not  formated properly!", 1);
        return false;
    }
    return preg_replace('/\s+/', '', $string);
}
function str_stripLenght(string $string)
{
    return (strlen($string) > DEF_MAXINPUTLENGHT) ? substr($string, 0, DEF_MAXINPUTLENGHT): $string;
}

function file_json_dec(string $link)
{
    return str_json_dec(file_read($link));
}
function file_json_enc(string $link, array $jsonArray)
{
    return file_handle($link, str_json_enc($jsonArray), "w");
}
function str_json_dec(string $string)
{
    return json_decode($string, true);
}
function str_json_enc(array $jsonArray)
{
    return json_encode($jsonArray, true);
}
function int_count($int)
{
    try {
        return count((int) $int);
    } catch (\Exception $e) {
        echo"Int not formated propery";
        return false;
    }
}
function dump($val)
{
    var_dump($val);
    echo"<br>";
}
function is_session_started()
{
    if (php_sapi_name() !== 'cli') {
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            return session_id() === '' ? false : true;
        }
    }
    return false;
}
