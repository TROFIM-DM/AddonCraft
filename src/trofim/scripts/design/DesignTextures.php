<?php
namespace trofim\scripts\design;

use std;
use trofim;
use gui;

/**
 * Класс для работы с Design текстур-паков.
 */
class DesignTextures 
{

    /**
     * Добавление нового item в список textures.
     * 
     * @param $textureInfo
     */
    public static function addItemTexture ($textureInfo) {
        
        $box = new UXPanel();
        $box->classes->add('listTexture-box');
        
        if ($textureInfo['path']['logo']) {
            $imageLogo = new UXImageArea(new UXImage($textureInfo['path']['logo']));
            $imageLogo->stretch = true;
            $imageLogo->size = [86, 86];
            
            $boxLogo = new UXVBox([$imageLogo]);
            $boxLogo->classes->add('listTexture-logo');
        }
        
        $labelName = new UXLabel($textureInfo['pack']['name']);
        $labelName->classes->add('listTexture-name');
        $labelName->wrapText = true;
        
        $labelDescription = new UXLabel(MojangAPI::replaceColor($textureInfo['pack']['description']));
        $labelDescription->classes->add('listTexture-description');
        $labelDescription->wrapText = true;
        
        $labelBox = new UXVBox([$labelName, $labelDescription]);
        
        $allBox = new UXHBox([$boxLogo, $labelBox]);
        
        $buttonMode = new UXMaterialButton();
        if (empty($textureInfo['enabled'])) $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/add.png'));
        else $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/line.png'));
        $buttonMode->contentDisplay = 'GRAPHIC_ONLY';
        $buttonMode->ripplerFill = '#333333';
        $buttonMode->bottomAnchor = 1;
        $buttonMode->rightAnchor = 1;
        $buttonMode->size = [39, 44];
        $buttonMode->tooltipText = 'Вкл./Откл. текстур-пак';
        $buttonMode->cursor = 'HAND';
        $buttonMode->classes->addAll(['listTexture-mode', 'normal-tooltip']);
        $buttonMode->nameTexture = fs::name($textureInfo['path']['texture']);
        $buttonMode->on('action', function () use (UXMaterialButton $buttonMode) {
            if (AddonCraft::$listTextures[$buttonMode->nameTexture]['enabled']) {
                ApiTextures::disabledTexture($buttonMode->nameTexture, $buttonMode);
            } else {
                ApiTextures::enabledTexture($buttonMode->nameTexture, $buttonMode);
            }
        });
        $box->add($buttonMode);
        
        $buttonDelete = new UXMaterialButton();
        $buttonDelete->graphic = new UXImageView(new UXImage('res://.data/img/close.png'));
        $buttonDelete->contentDisplay = 'GRAPHIC_ONLY';
        $buttonDelete->ripplerFill = white;
        $buttonDelete->topAnchor = 1;
        $buttonDelete->rightAnchor = 1;
        $buttonDelete->size = [39, 44];
        $buttonDelete->tooltipText = 'Удалить текстур-пак';
        $buttonDelete->cursor = 'HAND';
        $buttonDelete->classes->addAll(['listTexture-delete', 'normal-tooltip']);
        $buttonDelete->nameTexture = fs::name($textureInfo['path']['texture']);
        $buttonDelete->on('action', function () use (UXMaterialButton $buttonDelete) {
            $alert = new UXAlert('INFORMATION');
            $alert->title = 'AddonCraft';
            $alert->headerText = 'Удаление текстур-пака...';
            $alert->contentText = 'Вы действительно хотите удалить текстур-пак?';
            $alert->setButtonTypes(['Да', 'Нет']);
            $alert->graphic = new UXImageView(new UXImage('res://.data/img/delete_alert.png'));
            
            $textUrl = new UXLabelEx(fs::nameNoExt($buttonDelete->nameTexture));
            $textUrl->style = '-fx-font-family: "Impact"; -fx-font-size: 26px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
            $box = new UXVBox([$textUrl]);
            $box->style = '-fx-alignment: CENTER;';
            
            $alert->expandableContent = $box;
            $alert->expanded = true;
            
            switch ($alert->showAndWait()) {
                case 'Да':
                    ApiTextures::deleteTexture($buttonDelete->nameTexture);
                break;
            }
        });
        $box->add($buttonDelete);
        
        $box->add($allBox);
        
        if ($textureInfo['enabled']) app()->form(MainForm)->boxEnTextures->items->add($box);
        else app()->form(MainForm)->boxTextures->items->add($box);
    }
    
}