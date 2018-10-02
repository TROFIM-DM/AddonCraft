<?php
namespace trofim\scripts;

use trofim, gui;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с набором функций.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class func 
{
    
    /**
     * Создать Placeholder.
     * 
     * @param string $text.
     * @return UXLabelEx
     */
    static function createPlaceholder (string $text = null) : UXLabelEx
    {
        $placeholder = new UXLabelEx((!$text) ? L::translate('placeholder.null') : $text);
        $placeholder->classes->add('placeholder-list');
        return $placeholder;
    }
    
}