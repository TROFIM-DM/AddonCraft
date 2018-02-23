<?php
namespace trofim\modules;

use facade\Json;
use bundle\windows\Windows;
use std, gui, framework, trofim;

/**
 * Главный модуль AddonCraft.
 */
class AddonCraft extends AbstractModule
{
    /**
     * Папки необходимые для работы.
     */
    public static $appPath           = ['\\disabled\\', '\\disabled\\mods\\'];
    
    /**
     * Необходимые списки объектов.
     */
    public static $listMods          = false,
                  $listTextures      = false,
                  $listShaders       = ['list' => [['name' => 'OFF'], ['name' => '(internal)']]];
    
    /**
     * Список привязанных файлов к программе.
     */
    public static $fileStream        = false;
    
    /**
     * Загрузка компонента.
     * 
     * @event construct 
     */
    function doConstruct (ScriptEvent $e = null) {    
        /*if (count($GLOBALS['argv']) > 1) {
            $args = $GLOBALS['argv'];
            if ($args[1] == 'pre' && isset($args[2])) {
                pre($args[2]);
            }
        }*/
    }
    
    /**
     * Название программы.
     */
    static function getAppName () {
        return self::APP_NAME;
    }
    
    /**
     * Сервер программы.
     */
    static function getAppServer () {
        return self::APP_SERVER;
    }
    
    /**
     * Секретный ключ программы.
     */
    static function getAppKey () {
        return self::APP_KEY;
    }
    
    /**
     * Сайт программы.
     */
    static function getAppSite () {
        return self::APP_SITE;
    }
    
    /**
     * Версия программы.
     */
    static function getAppVersion () {
        return self::APP_VERSION;
    }
    
    /**
     * Префикс версии программы.
     */
    static function getAppVersionPrefix () {
        return self::APP_VERSION_PREFIX;
    }
    
    /**
     * Путь к папки для временных файлов.
     */
    static function getTemp () {
        return Windows::expandEnv('%TEMP%');
    }
    
    /**
     * Путь к временным файлам программы.
     */
    static function getAppTemp () {
        return Windows::expandEnv('%TEMP%').'\\AddonCraft';
    }
    
    /**
     * Путь к папке с данными пользователя.
     */
    static function getAppData () {
        return Windows::expandEnv('%APPDATA%');
    }
    
    /**
     * Путь к главной папке программы.
     */
    static function getAppPath () {
        return Windows::expandEnv('%APPDATA%').'\\.AddonCraft';
    }
    
    /**
     * Путь к папке с Minecraft.
     */
    static function getPathMinecraft () {
        return Windows::expandEnv('%APPDATA%').'\\.minecraft';
    }
    
    /**
     * Очистка значений класса.
     */
    static function clearValue ($value) {
        self::{$value} = false;
    }
    
    /**
     * Получение настроек Minecraft.
     */
    static function getMinecraftOptions () {
        if (fs::exists(self::getPathMinecraft() . '\\options.txt'))
            $pathOptions = self::getPathMinecraft() . '\\options.txt';
        else $pathOptions = 'res://files/options.txt';
        
        $fileOptions = file($pathOptions);
        if ($fileOptions) {
            foreach ($fileOptions as $option) {
                $explode = explode(':', $option);
                $options[$explode[0]] = $explode[1];
            }
            if (isset($options) && app()->form(MainForm)->iniOptions->put($options)) return true;
        }
        return false;
    }
    
    /**
     * Изменение настроек Minecraft.
     */
    static function setMinecraftOptions ($options) {
        if (fs::exists(self::getPathMinecraft())) {
            foreach ($options[''] as $key => $option) 
                if (isset($key) && isset($option))
                    $fileOptions[] = $key . ':' . $option;
            if (Stream::putContents(self::getPathMinecraft() . '\\options.txt', implode("\n", $fileOptions))) return true;
        }
        return false;
    }
    
    /**
     * Привязать файл к программе.
     */
    static function registerFile ($path, $return = false) {
        if (Stream::exists($path) && !self::$fileStream[fs::name($path)]) {
            self::$fileStream[fs::name($path)] = ResourceStream::of($path);
            return ($return) ? $file->getPath() : true;
        }
        return (self::$fileStream[fs::name($path)]) ? true : false;
    }
    
    /**
     * Отвязать файл от программы.
     */
    static function unRegisterFile ($path, $return = false) {
        if (Stream::exists($path) && self::$fileStream[fs::name($path)]) {
            self::$fileStream[fs::name($path)]->close();
            unset(self::$fileStream[fs::name($path)]);
            return ($return) ? $file->getPath() : true;
        }
        return (!self::$fileStream[fs::name($path)]) ? true : false;
    }
    
    /**
     * Создание Placeholder.
     */
    static function createPlaceholder ($text = "Список\nпуст") {
        $placeholder = new UXLabelEx($text);
        $placeholder->classes->add('list-placeholder');
        return $placeholder;
    }
}
