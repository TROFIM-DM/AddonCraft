<?php
namespace trofim\scripts\nbt;

use std, trofim, framework;
use php\compress\ZipFile;

/**
 * Класс для работы с NBTUtil.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class NBT
{
    
    /**
     * Путь к обрабатываемому файлу.
     * 
     * @var string
     */
    private static $pathFile;
    
    /**
     * Путь к NBTUtil.exe.
     * 
     * @var string
     */
    private static $pathNBT;
    
    /**
     * Конструктор.
     * 
     * @param string $pathFile Путь к обрабатываемому файлу.
     * @param string $pathNBT Путь к NBTUtil.exe.
     */
    function __construct (string $pathFile, string $pathNBT = null)
    {
        self::$pathFile = $pathFile;
        self::$pathNBT = ($pathNBT == null) ? Path::getAppPath() . '\\nbt\\NBTUtil.exe' : $pathNBT;
    }
    
    /**
     * Получить значение по указанному пути.
     * 
     * @param string $pathData Путь в файле.
     * @return array - Возврашает массив (ключ => значение).
     */
    function getValue (string $pathData = null) : array
    {
        $process = self::createProcess('print', $pathData);
        $item = explode(': ', explode("\n", $process->start()->getInput()->readFully())[0]);
        return [$item[0] => trim($item[1])];
    }
    
    /**
     * Получить массив значений по указанному пути.
     * 
     * @param string $pathData Путь в файле.
     * @return array - Возвращает массив (ключ => значение).
     */
    function getList (string $pathData = null) : array
    {
        $process = self::createProcess('printtree', $pathData);
        $item = $process->start()->getInput()->readFully();
        $arrItem = str_replace(['+', '|'], '', explode("\n", str::decode($item, 'cp866')));
        foreach ($arrItem as $item) {
            if (!str::contains($item, 'entries') && !str::contains($item, 'entry') && str::contains($item, ':')) {
                $explode = explode(':', $item);
                $return[trim($explode[0])] = trim($explode[1]);
            }
        }
        return $return;
    }
    
    /**
     * Изменить значение.
     * 
     * @param string $pathData Путь в файле.
     * @param string $value Значение для изменения.
     */
    /*public static function setValue (string $pathData, string $value)
    {
        $process = new Process([AddonCraft::getAppPath() . '\\nbt\\NBTUtil.exe', '--path', $pathData, '--setvalue', $value]);
        $process->start();
    }*/
    
    /*public static function getJSON (string $pathData, string $pathJSON) : array
    {
        $file = $pathJSON . '\\' . 'data.json';
        $process = new Process([AddonCraft::getAppPath() . '\\nbt\\NBTUtil.exe', '--path', $pathData, '--json', $file]);
        $process->start();
        if (fs::makeDir($pathJSON) && fs::exists($file) && $data = Json::decode(Stream::getContents($file))) 
            return $data;
    }*/
    
    /**
     * Создать процесс.
     * 
     * @param string $method Метод для работы с NBT.
     * @param string $pathData Путь в файле.
     * @return Process - возращает процесс для дальнейшней работы с NBT.
     */
    private static function createProcess (string $method, string $pathData = null) : Process
    {
        $process = [self::$pathNBT, '--path', self::$pathFile];
        if ($pathData)
            $process[] = '\\' . $pathData;
        $process[] = '--' . $method;
        return new Process($process);
    }
    
    /**
     * Проверить наличие NBTUtil.exe.
     * 
     * @return bool - да, если все выполнено успешно!
     */
    static function exists () : bool
    {
        if (!fs::exists(Path::getAppPath() . '\\nbt\\NBT.zip')) {
            fs::makeDir(Path::getAppPath() . '\\nbt\\');
            fs::copy('res://assets/nbt/NBT.zip', Path::getAppTemp() . '\\NBT.zip');
            
            $zip = new ZipFile(Path::getAppTemp() . '\\NBT.zip');
            $zip->unpack(Path::getAppPath() . '\\nbt\\');
            
            return true;
        }
        return false;
    }
}