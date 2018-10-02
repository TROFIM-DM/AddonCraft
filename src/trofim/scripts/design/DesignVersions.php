<?php
namespace trofim\scripts\design;

use std, gui, trofim;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с Design версией.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class DesignVersions 
{
    
    /**
     * Показать информацию о версии.
     * 
     * @param array $objectInfo
     */
    static function showInfo (array $objectInfo)
    {
        (new Thread(function () use ($objectInfo) {
            $GUI = new UXVBox();
            $GUI->classes->add('infoVersion-box');
            
            foreach (['alex.png', 'steve.png'] as $file) {
                if (fs::exists($objectInfo['path']['temp'] . '\\assets\\minecraft\\textures\\entity\\' . $file))
                    $textures[] = $file;
            }
            
            if (isset($textures)) {
                $header = new UXLabel(L::translate('word.textures'));
                $header->classes->add('infoVersion-header');
                
                $hBox = new UXHBox();
                $hBox->alignment = 'TOP_CENTER';
                
                $panel = new UXPanel();
                $panel->borderWidth = 3; 
                $panel->padding = 5;
                $image = new UXImageArea();
                $image->stretch = true;
                $image->size = [64, 64];
                $panel->add($image);
                $hBox->add($panel);
                
                $list = new UXComboBox();
                $list->promptText = L::translate('word.selected');
                $list->items->addAll($textures);
                $list->width = 400;
                $list->classes->add('infoVersion-fileTextures');
                $list->on('action', function (UXEvent $e) use ($textures, $image, $objectInfo) {
                    $image->image = new UXImage($objectInfo['path']['temp'] . '\\assets\\minecraft\\textures\\entity\\' . $textures[$e->sender->selectedIndex]);
                });
                $hBox->add($list);
                
                $box = new UXVBox([$header, $hBox]);
                $box->classes->add('infoVersion-box');
                
                $GUI->add($box);
            }
            
            if (!$GUI->children->count()){
                $placeholder = func::createPlaceholder(L::translate('placeholder.not'));
                $placeholder->size = [515, 515];
                $GUI->add($placeholder);
            }
            
            uiLater(function () use ($GUI) {
                app()->getForm(MainForm)->boxInfoVersion->items->add($GUI);
                if (!app()->getForm(MainForm)->panelInfoVersion->visible)
                    app()->getForm(MainForm)->panelInfoVersion->visible = true;
            });
        }))->start();
    }
    
}