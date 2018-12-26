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
     * Главные константы.
     */
    private const APP_NAME           = 'AddonCraft',
                  APP_GITHUB         = 'https://github.com/TROFIM-YT/AddonCraft/',
                  APP_SERVER         = 'http://addoncraft.xyz/',
                  APP_YOUTUBE        = 'http://bit.ly/TROFIM/',
                  APP_VK_GROUP       = 'https://vk.com/addon.craft/',
                  APP_VK_DEV         = 'https://vk.com/trofim_dm/',
                  APP_KEY            = 'i6LDwKX3IgCXokL79D4CwfLd',
                  APP_VERSION        = '0.2',
                  APP_VERSION_PREFIX = 'beta';
    
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
     * Канал YouTube.
     * 
     * @return string
     */
    static function getAppYouTube () : string
    {
        return self::APP_SERVER;
    }
    
    /**
     * Группа VK.
     * 
     * @return string
     */
    static function getAppGroup () : string
    {
        return self::APP_SERVER;
    }
    
    /**
     * Разработчик VK.
     * 
     * @return string
     */
    static function getAppDev () : string
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
    
}
