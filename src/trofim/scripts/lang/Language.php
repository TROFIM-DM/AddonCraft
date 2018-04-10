<?php
namespace trofim\scripts\lang;

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
     * @var $iniLang Ини информация о переводе.
     * @var $objectLang Информация об объектах.
     */
    private static $iniLang,
                   $objectLang;
    
    /**
     * Получить и применить язык.
     * 
     * @param string $locale
     */
    static function getLanguage (string $locale = null)
    {
        self::$iniLang = new IniStorage();
        $locale = (!$locale) ? Locale::getDefault()->getCountry() : $locale;
        self::$iniLang->path = (fs::exists('res://.lang/' . $locale . '/messages.ini')) ? 'res://.lang/' . $locale . '/messages.ini' : 'res://.lang/en/messages.ini';
        self::$iniLang->load();
        self::translateApp();
    }
    
    /**
     * Перевести текст.
     * 
     * @param string $key
     */
    static function translate (string $key) : string
    {
        return self::$iniLang->get($key);
    }
    
    /**
     * Перевести всю программу.
     */
    private static function translateApp ()
    {
        self::$objectLang = Json::decode(Stream::getContents('res://.lang/object.lang'));
        foreach (self::$objectLang as $form => $objects) {
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
                            if ($value['tooltip']) app()->getForm($form)->{$object}->tooltipText = self::translate($value['tooltip']);
                            app()->getForm($form)->{$object}->classes->add('help-tooltip');
                        }
                    break;
                } 
            }
        }
    }
    
    /**
     * Перевести заданную форму.
     * 
     * @param $form.
     */
    static function translateForm ($form)
    {
        foreach (self::$objectLang[$form] as $object => $value) {
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