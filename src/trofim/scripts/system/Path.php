<?php
namespace trofim\scripts\system;

use windows;

/**
 * Класс для работы с данными путями.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class Path 
{
    
    /**
     * Папки необходимые для работы программы.
     * 
     * @const array
     */
    private const APP_FOLDER = 'const';
    
    /**
     * Папки необходимые для работы программы.
     * 
     * @return array
     */
    static function getAppFolder () : array
    {
        return ['\\disabled\\', '\\disabled\\mods\\', '\\nbt\\'];
    }
    
    /**
     * Путь к папки для временных файлов.
     * 
     * @return string
     */
    static function getTemp () : string
    {
        return Windows::expandEnv('%TEMP%').'\\';
    }
    
    /**
     * Путь к временным файлам программы.
     * 
     * @return string
     */
    static function getAppTemp () : string
    {
        return Windows::expandEnv('%TEMP%').'\\AddonCraft\\';
    }
    
    /**
     * Путь к папке с данными пользователя.
     * 
     * @return string
     */
    static function getAppData () : string
    {
        return Windows::expandEnv('%APPDATA%').'\\';
    }
    
    /**
     * Путь к главной папке программы.
     * 
     * @return string
     */
    static function getAppPath () : string
    {
        return Windows::expandEnv('%APPDATA%').'\\.AddonCraft\\';
    }
    
    /**
     * Путь к папке с Minecraft.
     * 
     * @return string
     */
    static function getPathMinecraft () : string
    {
        return Windows::expandEnv('%APPDATA%').'\\.minecraft\\';
    }
    
}