<?php
namespace trofim\scripts\design;

use php\gui\UXRichTextArea;
use std, gui, trofim;

/**
 * Класс для работы с Design карт.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class DesignMaps
{
    
    /**
     * Добавление нового item в список maps.
     * 
     * @param $mapInfo
     */
    public static function addItem ($mapInfo)
    {
        
        $pathIcon = (fs::exists($mapInfo['path']['icon'])) ? $mapInfo['path']['icon'] : 'res://.data/img/map_icon.png';
        $imageIcon = new UXImageArea(new UXImage($pathIcon));
        $imageIcon->stretch = true;
        $imageIcon->size = [96, 96];
        $imageIcon->id = 'imageMaps' . count(AddonCraft::$listMaps);
            
        $boxIcon = new UXVBox([$imageIcon]);
        $boxIcon->classes->add('itemMap-logo');
        
        $labelName = new UXLabel(MojangAPI::replaceColor($mapInfo['info']['LevelName']));
        $labelName->classes->add('itemMap-name');
        
        $pathMap = fs::name($mapInfo['path']['map']);
        $labelLine1 = new UXLabel(((str::length($pathMap) > 30) ? str::sub($pathMap, 0, 30) . '...' : $pathMap)  . '  (' . new Time($mapInfo['info']['LastPlayed'])->toString("dd/MM/yyyy HH:mm") . ')                                                 ');
        $labelLine1->ellipsisString = '';
        $labelLine1->classes->add('itemMap-line1');
        
        $labelLine2 = new UXRichTextArea();
        $labelLine2->enabled = false;
        $labelLine2->classes->addAll(['itemMap-line2', 'scroll-pane']);
        
        if (!$mapInfo['info']['hardcore'])
            $labelLine2->appendText((!$mapInfo['info']['GameType']) ? Language::translate('mainform.maps.item.survival') : Language::translate('mainform.maps.item.creative'), '-fx-fill: "#cccccc";');
        else
            $labelLine2->appendText(Language::translate('mainform.maps.item.hardcore'), '-fx-fill: red; -fx-font-size: 20px;');
            
        if ($mapInfo['info']['allowCommands'])
            $labelLine2->appendText(', ' . Language::translate('word.cheats'), '-fx-fill: "#cccccc";');
            
        $versionText = ($mapInfo['info']['Name']) ? $mapInfo['info']['Name'] : Language::translate('word.unknown');
        $labelLine2->appendText(', ' . Language::translate('word.version') . ': ' . $versionText, '-fx-fill: "#cccccc";');
        
        $labelBox = new UXVBox([$labelName, $labelLine1, $labelLine2]);
        
        $allBox = new UXHBox([$boxIcon, $labelBox]);
        
        app()->form(MainForm)->boxMaps->items->add($allBox);
    }
    
}