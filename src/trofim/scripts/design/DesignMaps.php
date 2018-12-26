<?php
namespace trofim\scripts\design;

use php\gui\UXRichTextArea;
use std, gui, trofim;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с Design карт.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class DesignMaps
{
    
    /**
     * Добавить новый item в список maps.
     * 
     * @param array $objectInfo
     */
    static function addItem (array $objectInfo)
    {    
        $pathIcon = (fs::exists($objectInfo['path']['icon'])) ? $objectInfo['path']['icon'] : 'res://.data/img/map_icon.png';
        $imageIcon = new UXImageArea(new UXImage($pathIcon));
        $imageIcon->stretch = true;
        $imageIcon->size = [96, 96];
        $imageIcon->id = 'imageMaps' . count(ApiMaps::getObjects());
            
        $boxIcon = new UXVBox([$imageIcon]);
        $boxIcon->classes->add('itemMap-logo');
        
        $labelName = new UXLabel(MojangAPI::replaceColor($objectInfo['info']['LevelName']));
        $labelName->classes->add('itemMap-name');
        
        $pathMap = fs::name($objectInfo['path']['map']);
        $labelLine1 = new UXLabel(((str::length($pathMap) > 30) ? str::sub($pathMap, 0, 30) . '...' : $pathMap)  . '  (' . new Time($objectInfo['info']['LastPlayed'])->toString("dd/MM/yyyy HH:mm") . ')                                                 ');
        $labelLine1->ellipsisString = '';
        $labelLine1->classes->add('itemMap-line1');
        
        $labelLine2 = new UXRichTextArea();
        $labelLine2->enabled = false;
        $labelLine2->classes->addAll(['itemMap-line2', 'scroll-pane']);
        
        if (!$objectInfo['info']['hardcore'])
            $labelLine2->appendText((!$objectInfo['info']['GameType']) ? L::translate('mainform.maps.item.survival') : L::translate('mainform.maps.item.creative'), '-fx-fill: "#cccccc";');
        else
            $labelLine2->appendText(L::translate('mainform.maps.item.hardcore'), '-fx-fill: red; -fx-font-size: 20px;');
            
        if ($objectInfo['info']['allowCommands'])
            $labelLine2->appendText(', ' . L::translate('word.cheats'), '-fx-fill: "#cccccc";');
            
        $versionText = ($objectInfo['info']['Name']) ? $objectInfo['info']['Name'] : L::translate('word.unknown');
        $labelLine2->appendText(', ' . L::translate('word.version') . ': ' . $versionText, '-fx-fill: "#cccccc";');

        $labelBox = new UXVBox([$labelName, $labelLine1, $labelLine2]);

        $GUI = new UXHBox([$boxIcon, $labelBox]);

        app()->getForm(MainForm)->boxMaps->items->add($GUI);
    }
    
}