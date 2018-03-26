<?php
namespace trofim\scripts\design;

use std, gui, trofim;

/**
 * Класс для работы с Design текстур-паков.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class DesignTextures 
{

    /**
     * Добавление нового item в список textures.
     * 
     * @param $textureInfo
     */
    public static function addItem ($textureInfo) {
        
        $box = new UXPanel();
        $box->classes->add('itemTexture-box');
        
        if ($textureInfo['path']['logo']) {
            $imageLogo = new UXImageArea(new UXImage($textureInfo['path']['logo']));
            $imageLogo->stretch = true;
            $imageLogo->size = [86, 86];
            
            $boxLogo = new UXVBox([$imageLogo]);
            $boxLogo->classes->add('itemTexture-logo');
        }
        
        $labelName = new UXLabel($textureInfo['pack']['name']);
        $labelName->classes->add('itemTexture-name');
        $labelName->wrapText = true;
        
        $labelDescription = new UXLabel(MojangAPI::replaceColor($textureInfo['pack']['description']));
        $labelDescription->classes->add('itemTexture-description');
        $labelDescription->wrapText = true;
        
        $labelBox = new UXVBox([$labelName, $labelDescription]);
        
        $allBox = new UXHBox([$boxLogo, $labelBox]);
        
        $buttonMode = new UXMaterialButton();
        if (empty($textureInfo['enabled'])) $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/icon/add-16.png'));
        else $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/icon/line-16.png'));
        $buttonMode->contentDisplay = 'GRAPHIC_ONLY';
        $buttonMode->focusTraversable = false;
        $buttonMode->ripplerFill = '#333333';
        $buttonMode->bottomAnchor = 1;
        $buttonMode->rightAnchor = 1;
        $buttonMode->size = [39, 44];
        $buttonMode->tooltipText = Language::translate('mainform.tooltip.textures.btn.mode');
        $buttonMode->cursor = 'HAND';
        $buttonMode->classes->addAll(['itemTexture-mode', 'help-tooltip']);
        $buttonMode->nameTexture = fs::name($textureInfo['path']['texture']);
        $buttonMode->on('action', function () use (UXMaterialButton $buttonMode) {
            if (AddonCraft::$listTextures[$buttonMode->nameTexture]['enabled']) 
                ApiTextures::disabledTexture($buttonMode->nameTexture, $buttonMode);
            else 
                ApiTextures::enabledTexture($buttonMode->nameTexture, $buttonMode);
        });
        $box->add($buttonMode);
        
        $buttonDelete = new UXMaterialButton();
        $buttonDelete->graphic = new UXImageView(new UXImage('res://.data/img/icon/close-16.png'));
        $buttonDelete->contentDisplay = 'GRAPHIC_ONLY';
        $buttonDelete->focusTraversable = false;
        $buttonDelete->ripplerFill = white;
        $buttonDelete->topAnchor = 1;
        $buttonDelete->rightAnchor = 1;
        $buttonDelete->size = [39, 44];
        $buttonDelete->tooltipText = Language::translate('mainform.tooltip.textures.btn.delete');
        $buttonDelete->cursor = 'HAND';
        $buttonDelete->classes->addAll(['itemTexture-delete', 'help-tooltip']);
        $buttonDelete->nameTexture = fs::name($textureInfo['path']['texture']);
        $buttonDelete->on('action', function () use (UXMaterialButton $buttonDelete) {
            $alert = new UXAlert('INFORMATION');
            $alert->title = app()->getName();
            $alert->headerText = Language::translate('mainform.message.textures.delete.header');
            $alert->contentText = Language::translate('mainform.message.textures.delete.content');
            $alert->setButtonTypes([Language::translate('word.yes'), Language::translate('word.no')]);
            $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/delete_alert-24.png'));
            
            $textUrl = new UXLabelEx(fs::nameNoExt($buttonDelete->nameTexture));
            $textUrl->style = '-fx-font-family: "Impact"; -fx-font-size: 22px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
            $box = new UXVBox([$textUrl]);
            $box->style = '-fx-alignment: CENTER;';
            
            $alert->expandableContent = $box;
            $alert->expanded = true;
            
            switch ($alert->showAndWait()) {
                case Language::translate('word.yes'):
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