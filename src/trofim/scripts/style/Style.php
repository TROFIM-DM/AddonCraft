<?php
namespace trofim\scripts\style;

use std, framework;
use trofim\scripts\lang\Language as L;

class Style 
{    

    /**
     * Файл стиля.
     * 
     * @var string
     */
    private static $style;

    /**
     * Загрузить стиль.
     * 
     * @return bool
     */
    static function load () : bool
    {
        self::$style = '/.theme/styles/' . L::getLocale() . '.fx.css';
        if (ResourceStream::exists('res://' . self::$style)) {
            foreach (['StartForm', 'MainForm'] as $form)
                app()->getForm($form)->addStylesheet(self::$style);
            return true;
        }
        return false;
    }
    
}