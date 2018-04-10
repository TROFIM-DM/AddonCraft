<?php
namespace trofim\modules;

use std, gui, framework, trofim;

/**
 * Главный модуль AddonCraft.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class AddonCraft extends AbstractModule
{
    
    /**
     * Загрузка компонента.
     * 
     * @event construct 
     */
    function doConstruct (ScriptEvent $e = null)
    {    
        /*if (count($GLOBALS['argv']) > 1) {
            $args = $GLOBALS['argv'];
            if ($args[1] == '--newversion' && isset($args[2])) {

            }
        }*/
    }
    
    /**
     * Название программы.
     * 
     * @return string
     */
    static function getAppName () : string
    {
        return self::APP_NAME;
    }
    
    /**
     * Сайт программы.
     * 
     * @return string
     */
    static function getAppSite () : string
    {
        return self::APP_SITE;
    }
    
    /**
     * Репозиторий программы на GitHub.
     * 
     * @return string
     */
    static function getAppGitHub () : string
    {
        return self::APP_GITHUB;
    }
    
    /**
     * Сервер программы.
     * 
     * @return string
     */
    static function getAppServer () : string
    {
        return self::APP_SERVER;
    }
    
    /**
     * Секретный ключ программы.
     * 
     * @return string
     */
    static function getAppKey () : string
    {
        return self::APP_KEY;
    }
    
    /**
     * Версия программы.
     * 
     * @return string
     */
    static function getAppVersion () : string
    {
        return self::APP_VERSION;
    }
    
    /**
     * Префикс версии программы.
     * 
     * @return string
     */
    static function getAppVersionPrefix () : string
    {
        return self::APP_VERSION_PREFIX;
    }
    
    /**
     * Очистить значения класса.
     * 
     * @param string $value.
     */
    static function clearValue (string $value)
    {
        self::{$value} = false;
    }
    
}
