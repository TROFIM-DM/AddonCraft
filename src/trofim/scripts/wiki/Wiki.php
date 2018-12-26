<?php
namespace trofim\scripts\wiki;

use trofim, std;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с википедией программы.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class Wiki 
{
    
    /**
     * @var $index Индекс типа.
     * @var $switchType Массив типов.
     */
    private static $index,
                   $switchType = ['Mods', 'Textures', 'Shaders', 'Maps', 'Versions'];
    
    /**
     * Выбрать тип Wiki.
     * 
     * @param int $index.
     */
    static function switch (int $index)
    {
        app()->getForm(MainForm)->buttonWiki->tooltipText = L::translate('mainform.tooltip.wiki') . ' ' . L::translate('word.' . str::lower(self::$switchType[$index]));
        self::$index = $index;
    }
    
    /**
     * Произвести редирект на указанный тип.
     */
    static function open ()
    {
        if (self::$index != 4) {
            app()->getForm(MainForm)->toast(L::translate('mainform.toast.wiki'));
            open(AddonCraft::getAppGitHub() . 'blob/master/wiki/' . self::$switchType[self::$index] . '.md');
        } else app()->getForm(MainForm)->toast(L::translate('mainform.toast.wiki.' . str::lower(self::$switchType[self::$index])));
    }
    
}