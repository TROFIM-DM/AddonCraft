<?php
namespace trofim\scripts\system;

use std, trofim;

/**
 * Класс для работы с файлами.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class FileSystem
{
    
    /**
     * Список привязанных файлов к программе.
     * 
     * @var string
     */
    private static $fileStream = [];
    
    /**
     * Привязать файл к программе.
     * 
     * @param string $path
     * @return bool
     */
    static function registerFile (string $path) : bool
    {
        if (Stream::exists($path) && !self::$fileStream[fs::name($path)]) 
            self::$fileStream[fs::name($path)] = ResourceStream::of($path);
        return (self::$fileStream[fs::name($path)]) ? true : false;
    }
    
    /**
     * Отвязать файл от программы.
     * 
     * @param string $path
     * @return bool
     */
    static function unRegisterFile (string $path) : bool
    {
        if (Stream::exists($path) && self::$fileStream[fs::name($path)]) {
            self::$fileStream[fs::name($path)]->close();
            unset(self::$fileStream[fs::name($path)]);
        }
        return (!self::$fileStream[fs::name($path)]) ? true : false;
    }
    
}