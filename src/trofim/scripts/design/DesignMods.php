<?php
namespace trofim\scripts\design;

use std, gui, trofim;

/**
 * Класс для работы с Design модов.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class DesignMods 
{

    /**
     * Добавление нового item в список mods.
     * 
     * @param $modInfo
     */
    public static function addItem ($modInfo) {
                       
        $box = new UXPanel();
        $box->classes->add('itemMod-box');
        
        $labelName = new UXLabel($modInfo['name']);
        if ($modInfo['mode'] == 'disabled') $labelName->style = '-fx-text-fill: red;';
        $labelName->classes->add('itemMod-name');
        $labelName->wrapText = true;
        
        $labelVersion = new UXLabel($modInfo['version']);
        $labelVersion->classes->add('itemMod-version');
        
        $vBox = new UXVBox([$labelName, $labelVersion]);
        $box->add($vBox);
        
        app()->form(MainForm)->boxMods->items->add($box);
    }
    
    /**
     * Показ информации о моде.
     * 
     * @param $modInfo
     */
    public static function showInfo ($modInfo) {
        
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
            
            $labelVersion = new UXLabel(Language::translate('mainform.mods.info.version') . ' ' . $modInfo['version']);
            $labelVersion->wrapText = true;
            $labelVersion->classes->add('infoMod-version');
            $infoMod[] = $labelVersion;
            
            $labelMCVersion = new UXLabel(Language::translate('mainform.mods.info.mcversion') . ' ' . $modInfo['mcversion']);
            $labelMCVersion->wrapText = true;
            $labelMCVersion->classes->add('infoMod-mcversion');
            $infoMod[] = $labelMCVersion;
            
            $labelModID = new UXLabel(Language::translate('mainform.mods.info.id') . ' ' . $modInfo['modid']);
            $labelModID->wrapText = true;
            $labelModID->classes->add('infoMod-modid');
            $infoMod[] = $labelModID;
            
            if ($modInfo['authorList']) {
                $labelAuthor = new UXLabelEx(Language::translate('mainform.mods.info.author') . ' ' . implode(', ', $modInfo['authorList']));
                $labelAuthor->wrapText = true;
                $labelAuthor->classes->add('infoMod-author');
                $infoMod[] = $labelAuthor;
            }
            
            if ($modInfo['url']) {
                $labelTextUrl = new UXLabel('Url: ');
                $labelTextUrl->classes->add('infoMod-textUrl');
                
                $labelURL = new UXHyperlink(parse_url($modInfo['url'])['host']);
                $labelURL->classes->add('infoMod-url');
                $labelURL->tooltipText = $modInfo['url'];
                $labelURL->tooltip->style = "-fx-font-size: 12px; -fx-font-family: 'System';";
                $labelURL->on('action', function () use ($modInfo) {
                    $alert = new UXAlert('INFORMATION');
                    $alert->title = app()->getName();
                    $alert->headerText = Language::translate('mainform.message.mods.url.header');
                    $alert->contentText = Language::translate('mainform.message.mods.url.content');
                    $alert->setButtonTypes([Language::translate('word.yes'), Language::translate('word.copy'), Language::translate('word.no')]);
                    $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/link_alert-24.png'));
                    
                    $textUrl = new UXLabelEx($modInfo['url']);
                    $textUrl->style = '-fx-font-family: "System"; -fx-font-size: 14px; -fx-text-alignment: CENTER; -fx-alignment: CENTER; -fx-padding: 0 0 7 0;';
                    
                    $textWarning = new UXLabelEx(Language::translate('mainform.message.mods.url.content.label.warning'));
                    $textWarning->style = '-fx-font-family: "Minecraft Rus"; -fx-font-size: 12px; -fx-text-fill: red; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
                    
                    $box = new UXVBox([$textUrl, $textWarning]);
                    $box->style = '-fx-alignment: CENTER;';
                    
                    $alert->expandableContent = $box;
                    $alert->expanded = true;
                    
                    switch ($alert->showAndWait()) {
                        case Language::translate('word.yes'):
                            open($modInfo['url']);
                        break;
                        case Language::translate('word.copy'):
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