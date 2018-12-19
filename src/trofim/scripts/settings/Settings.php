<?php
namespace trofim\scripts\settings;

use trofim, std, framework;

/**
 * Класс для работы с настройками программы.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class Settings 
{
    
    /**
     * @var $iniObject Ини информация о настройках программы.
     */
    private static $iniObject;
    
    /**
     * Вернуть iniObject.
     * 
     * @return IniStorage
     */
    static function getINI () : IniStorage
    {
        return self::$iniObject;
    }
    
    /**
     * Загрузить настройки.
     * 
     * @return bool
     */
    static function load () : bool
    {
        self::$iniObject = new IniStorage();
        self::$iniObject->autoSave = false;
        self::$iniObject->path = Path::getAppPath() . '\\options.ini';
        if (fs::exists(Path::getAppPath() . '\\options.ini'))
            self::$iniObject->load();
    }
    
    /**
     * Сохранить настройки.
     * 
     * @return bool
     */
    static function save () : bool
    {
        self::$iniObject->save();
    }
    
}