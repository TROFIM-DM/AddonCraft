<?php
namespace trofim\scripts\lang;

use facade\Json;
use std, framework, trofim, gui;

/**
 * Класс для работы с переводом программы.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class Language
{

    /**
     * @var $iniObject Ини информация о переводе.
     * @var $listObject Информация об объектах.
     */
    private static $iniObject,
                   $listObject;
         
    /**
     * @var $locale Язык.
     */
    private static $locale;
    
    /**
     * @var $list Список доступных языков.
     */
    private static $list;
    
    /**
     * @var $default Язык по умолчанию.
     */
    private static $default = 'en';
    
    /**
     * Получить выбранный язык.
     * 
     * @return string
     */
    static function getLocale () : string
    {
        return self::$locale;
    }
    
    /**
     * Получить список доступных языков.
     * 
     * @return array
     */
    static function getList () : array
    {
        return self::$list;
    }
    
    /**
     * Получить язык по умолчанию.
     * 
     * @return string
     */
    static function getDefault () : string
    {
        return self::$default;
    }
    
    /**
     * Получить и применить язык.
     * 
     * @param string $locale
     */
    static function load (string $locale = null)
    {
        $file = explode(',', Stream::getContents('res://.lang/list'));

        foreach ($file as $lang) {
            self::$list[$lang] = Json::decode(Stream::getContents('res://.lang/' . $lang . '/information.json'));
            app()->form(SettingsForm)->listLanguage->items->add(self::$list[$lang]['name']);
        }

        self::$iniObject = new IniStorage();
        self::$locale = $locale;
        
        if (!$locale) {
            $locale = Settings::getINI()->get('language');
            self::$locale = (Settings::getINI()->get('language')) ? $locale : Locale::getDefault()->getLanguage();
            if (!in_array(self::$locale, array_keys(self::$list)))
                self::$locale = self::getDefault();
            Settings::getINI()->set('language', self::$locale);
            Settings::save();
            
        }
        
        self::$iniObject->path = 'res://.lang/' . self::$locale . '/messages.ini';
        self::$iniObject->load();
        self::translateApp();
    }
    
    /**
     * Перевести текст.
     * 
     * @param string $key
     * @param array $args
     */
    static function translate (string $key, ...$args) : string
    {
        $string = self::$iniObject->get($key);
        foreach ($args as $arg) 
            $string = str::format($string, $arg);
        return $string;
    }
    
    /**
     * Перевести всю программу.
     */
    private static function translateApp ()
    {
        self::$listObject = Json::decode(Stream::getContents('res://.lang/object.lang'));
        foreach (self::$listObject as $form => $objects) {
            foreach ($objects as $object => $value) {
                switch ($object) {
                    case "tabPane":
                        foreach ($value as $index => $text) 
                            app()->getForm($form)->{$object}->tabs[$index]->text = self::translate($text);
                    break;
                    case "listValue":
                    case "selectedValue":
                        app()->getForm($form)->{$object}->items->addAll(explode(',', self::translate($value['list'])));
                    break;
                    default:
                        app()->getForm($form)->{$object}->text = ($value['text']) ? self::translate($value['text']) : app()->getForm(MainForm)->{$object}->text;
                        if (isset($value['tooltip'])) {
                            if ($value['tooltip'])
                                app()->getForm($form)->{$object}->tooltipText = self::translate($value['tooltip']);
                            app()->getForm($form)->{$object}->classes->add('tip');
                        }
                    break;
                } 
            }
        }
    }
    
    /**
     * Перевести заданную форму.
     * 
     * @param $form
     */
    static function translateForm ($form)
    {
        foreach (self::$listObject[$form] as $object => $value) {
            switch ($object) {
                case "tabPane":
                    foreach ($value as $index => $text) 
                        app()->getForm($form)->{$object}->tabs[$index]->text = self::translate($text);
                break;
                default:
                    app()->getForm($form)->{$object}->text = ($value['text']) ? self::translate($value['text']) : app()->getForm(MainForm)->{$object}->text;
                    if ($value['tooltip']) {
                        app()->getForm($form)->{$object}->tooltipText = self::translate($value['tooltip']);
                        app()->getForm($form)->{$object}->classes->add('help-tooltip');
                    }
                break;
            } 
        }
    }
    
}