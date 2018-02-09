<?php
namespace trofim\scripts;

use framework;

class Language 
{
    private static $iniLanguage = false;
    
    public static function getLanguage () {
        $ini = new IniStorage();
        $ini->path = 'res://lang/ru/messages.ini';
        $ini->load();
        self::$iniLanguage = $ini;
    }
    
    public static function translate ($key) {
        return self::$iniLanguage->get($key);
    }
}