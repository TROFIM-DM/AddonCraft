<?php
namespace trofim\scripts\design;

use framework;
use trofim;
use gui;
use std;

/**
 * Класс для работы с Design модов.
 */
class DesignMods 
{

    /**
     * Добавление нового item в список mods.
     * 
     * @param $modInfo
     */
    public static function addItemMod ($modInfo) {
                       
        $box = new UXPanel();
        $box->classes->add('listMod-box');
        
        $labelName = new UXLabel($modInfo['name']);
        if ($modInfo['mode'] == 'disabled') $labelName->style = '-fx-text-fill: red;';
        $labelName->classes->add('listMod-name');
        $labelName->wrapText = true;
        
        $labelVersion = new UXLabel($modInfo['version']);
        $labelVersion->classes->add('listMod-version');
        
        $vBox = new UXVBox([$labelName, $labelVersion]);
        $box->add($vBox);
        
        app()->form(MainForm)->boxMods->items->add($box);
    }
    
    /**
     * Показ информации о моде.
     * 
     * @param $modInfo
     */
    public static function showInfoMod ($modInfo) {
        
        $thread = new Thread(function () use ($modInfo) {
            
            if ($modInfo['logoFile'] && $modInfo['path']['logo']) {
                $imageLogo = new UXImageArea(new UXImage($modInfo['path']['logo']));
                $imageLogo->size = [400, 140];
                $imageLogo->stretch = true;
                $imageLogo->style = '-fx-effect: dropshadow(one-pass-box, rgba(0, 0, 0, 0.9), 10, 0.0, 0, 0);';
                
                $boxLogo = new UXVBox([$imageLogo]);
                $boxLogo->classes->add('infoMod-logo');
                $infoMod[] = $boxLogo;
            }
            
            $labelName = new UXLabelEx($modInfo['name']);
            if ($modInfo['mode'] == 'disabled') $labelName->style = '-fx-text-fill: red;';
            $labelName->wrapText = true;
            $labelName->id = 'infoMod_name';
            $labelName->classes->add('infoMod-name');
            $infoMod[] = $labelName;
            
            $labelVersion = new UXLabel('Версия мода: ' . $modInfo['version']);
            $labelVersion->wrapText = true;
            $labelVersion->classes->add('infoMod-version');
            $infoMod[] = $labelVersion;
            
            $labelMCVersion = new UXLabel('Версия Minecraft: ' . $modInfo['mcversion']);
            $labelMCVersion->wrapText = true;
            $labelMCVersion->classes->add('infoMod-mcversion');
            $infoMod[] = $labelMCVersion;
            
            $labelModID = new UXLabel('Мод ID: ' . $modInfo['modid']);
            $labelModID->wrapText = true;
            $labelModID->classes->add('infoMod-modid');
            $infoMod[] = $labelModID;
            
            $labelAuthor = new UXLabel('Авторы: ' . implode(', ', $modInfo['authorList']));
            $labelAuthor->wrapText = true;
            $labelAuthor->classes->add('infoMod-author');
            $infoMod[] = $labelAuthor;
            
            if ($modInfo['url']) {
                $labelTextUrl = new UXLabel('Url: ');
                $labelTextUrl->classes->add('infoMod-textUrl');
                
                $labelURL = new UXHyperlink(parse_url($modInfo['url'])['host']);
                $labelURL->classes->add('infoMod-url');
                $labelURL->tooltipText = $modInfo['url'];
                $labelURL->tooltip->style = "-fx-font-size: 12px; -fx-font-family: 'System';";
                $labelURL->on('action', function () use ($modInfo) {
                    $alert = new UXAlert('INFORMATION');
                    $alert->title = 'AddonCraft';
                    $alert->headerText = 'Переход по ссылке...';
                    $alert->contentText = 'Вы действительно хотите перейти по ссылке?';
                    $alert->setButtonTypes(['Да', 'Копировать', 'Нет']);
                    $alert->graphic = new UXImageView(new UXImage('res://.data/img/link_alert.png'));
                    
                    $textUrl = new UXLabelEx($modInfo['url']);
                    $textUrl->style = '-fx-font-family: "System"; -fx-font-size: 14px; -fx-text-alignment: CENTER; -fx-alignment: CENTER; -fx-padding: 0 0 7 0;';
                    $textWarning = new UXLabelEx('Не переходите по ссылкам от людей, которым не доверяете!');
                    $textWarning->style = '-fx-font-family: "Minecraft Rus"; -fx-font-size: 12px; -fx-text-fill: red; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
                    $box = new UXVBox([$textUrl, $textWarning]);
                    $box->style = '-fx-alignment: CENTER;';
                    
                    $alert->expandableContent = $box;
                    $alert->expanded = true;
                    
                    switch ($alert->showAndWait()) {
                        case 'Да':
                            open($modInfo['url']);
                        break;
                        case 'Копировать':
                            UXClipboard::setText($modInfo['url']);
                        break;
                    }
                });
                
                $boxURL = new UXHBox([$labelTextUrl, $labelURL]);
                $infoMod[] = $boxURL;
            }
            
            if ($modInfo['description']) {
                $labelDescription = new UXLabelEx($modInfo['description']);
                $labelDescription->wrapText = true;
                $labelDescription->classes->add('infoMod-description');
                $infoMod[] = $labelDescription;
            }
            
            $mode = $modInfo['mode'];
            
            uiLater(function () use ($infoMod, $mode) {
                if ($mode == 'enabled') app()->form(MainForm)->setModeMod(true);
                else if ($mode == 'disabled') app()->form(MainForm)->setModeMod(false);
                foreach ($infoMod as $info) 
                    app()->form(MainForm)->boxInfoMod->items->add($info);
            });
        });
        $thread->start();
        
    }
    
}