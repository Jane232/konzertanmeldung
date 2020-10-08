<?php
require_once 'backend/config.php';
class Dir
{
    /*
    Funktionen:
    delete
    create
    check
    listFiles
    */
    public function delete(string $dirPath)
    {
        if (!(self::check($dirPath))) {
            throw new \Exception("Dir not existing!", 1);
            return false;
        }
        if (self::check($dirPath)) {
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

    public function create(string $dirPath)
    {
        if (!self::check($dirPath)) {
            if (!mkdir($dirPath, 0777, true)) {
                throw new Exception('Ordner konnte nicht erstellt werden!');
            }
        } else {
            throw new Exception("Folder was allready existing");
        }
        return true;
    }

    public function check(string $dirPath)
    {
        return is_dir($dirPath);
    }

    public static function listFiles(string $dirPath)
    {
        if (!(self::check($dirPath))) {
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
    public function listAll(string $dirPath)
    {
        if (!(self::check($dirPath))) {
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
}
class File
{
    /*
    Funktionen:
    create
    check
    handle
    delete

    lines
    lineCount
    */
    public function create(string $link)
    {
        if ((self::check($link))) {
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
    public function check(string $link)
    {
        return is_file($link);
    }
    public function handle(string $link, string $string, string $mode = "w")
    {
        if (!(self::check($link))) {
            throw new \Exception("File not existing!", 1);
            return false;
        }
        if (is_string($string)) {
            throw new \Exception("String not properly formated or existing!", 1);
            return false;
        }
        $file = fopen($link, $mode);
        fwrite($file, $string);
        fclose($file);
        return true;
    }

    public function delete(string $link)
    {
        if (!self::check($link)) {
            throw new \Exception("File not existing!", 1);
            return false;
        }
        return (unlink($link))?true:false;
    }

    public function lines(string $link)
    {
        if (!self::check($link)) {
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

    public function lineCount(string $link)
    {
        return count(self::lines($link));
    }
}

class Str
{
    public function check(string $string)
    {
        return is_string($string);
    }
    public function split(string $string, string $exploder, int $limit = -1)
    {
        if (!self::check($string) && !self::check($exploder)) {
            throw new \Exception("String not formated properly!", 1);
            return false;
        }
        if ($limit > 0) {
            return explode($string, $exploder);
        } else {
            return explode($string, $exploder, $limit);
        }
    }
    public function stripWSC(string $string)
    {
        if (!self::check($string)) {
            throw new \Exception("String not  formated properly!", 1);
            return false;
        }
        return preg_replace('/\s+/', '', $string);
    }
    public function stripLenght(string $string, int $maxInputLenght)
    {
        return (strlen($string) > $maxInputLenght) ? substr($string, 0, $maxInputLenght): $string;
    }
}

class Data
{
    public function __construct($dir, $file, $str)
    {
        $this->dir = $dir;
        $this->file = $file;
        $this->str = $str;
    }
    /*
    public static function dir()
    {
        return $dir = new Dir;
    }
    public static function file()
    {
        return $file = new file;
    }
    public static function str()
    {
        return $str = new str;
    }*/
}
global $data;
$data = new Data(new Dir, new File, new Str);
//var_dump($data->dir->listFiles("tasdest"));
var_dump($data->dir::listFiles('C:\xampp\htdocs\dashboard'));
