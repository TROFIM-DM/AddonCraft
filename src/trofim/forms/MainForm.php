<?php
namespace trofim\forms;

use Exception;
use facade\Json;
use php\compress\ZipFile;
use windows;
use std, gui, framework, trofim;


class MainForm extends AbstractForm
{
    
    /**
     * Загрузка формы.
     * 
     * @event construct 
     */
    function doConstruct(UXEvent $e = null)
    {
        $this->on('dragOver', function (UXDragEvent $drag) {
            if (count($drag->dragboard->files) == 1) {
                if (($this->tabPane->selectedIndex == 0 && fs::ext($drag->dragboard->files[0]->getName()) == 'jar') ||
                    ($this->tabPane->selectedIndex == 1 && fs::ext($drag->dragboard->files[0]->getName()) == 'zip')) {
                    $drag->acceptTransferModes(['MOVE', 'COPY']);
                }
            }
            $drag->consume();
        });
        $this->on('dragDrop', function (UXDragEvent $drag) { 
            if (count($drag->dragboard->files)) {
                foreach ($drag->dragboard->files as $file) {
                    if ($this->tabPane->selectedIndex == 0 && fs::ext($file->getName()) == 'jar') {
                        ApiMods::addMod($file);
                    } else if ($this->tabPane->selectedIndex == 1 && fs::ext($file->getName()) == 'zip') {
                        ApiTextures::addTexture($file);
                    }
                }
                return;
            }
        });
    }
    
    /**
     * Появление формы.
     * 
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        /*Плавное появление окна*/
        Animation::fadeOut($this, 1, function () {
            Animation::fadeOut($this->panelInfoMod, 1);
            Animation::fadeIn($this, 350);
        });
        
        
    }
    
    /**
     * @event imageHide.click-Left 
     */
    function doImageHideClickLeft(UXMouseEvent $e = null)
    {    
        $this->iconified = !$this->iconified;
    }

    /**
     * @event imageExit.click-Left 
     */
    function doImageExitClickLeft(UXMouseEvent $e = null)
    {    
        Animation::fadeOut($this, 350, function () {
            app()->shutdown();
            exit();
        });
    }

    /**
     * @event boxMods.action 
     */
    function doBoxModsAction(UXEvent $e = null)
    {
        if ($e->sender->items->count > 0) {
            if (!$this->panelInfoMod->opacity) Animation::fadeIn($this->panelInfoMod, 200);
            $this->boxInfoMod->items->clear();
            DesignMods::showInfoMod(AddonCraft::$listMods[$e->sender->selectedIndex]);
        }
    }

    /**
     * @event buttonDeleteMod.action 
     */
    function doButtonDeleteModAction(UXEvent $e = null)
    {    
        $alert = new UXAlert('INFORMATION');
        $alert->title = 'AddonCraft';
        $alert->headerText = 'Удаление мода...';
        $alert->contentText = 'Вы действительно хотите удалить мод?';
        $alert->setButtonTypes(['Да', 'Нет']);
        $alert->graphic = new UXImageView(new UXImage('res://.data/img/delete_alert.png'));
        
        $textUrl = new UXLabelEx(AddonCraft::$listMods[$this->boxMods->selectedIndex]['name']);
        $textUrl->style = '-fx-font-family: "Impact"; -fx-font-size: 22px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
        $box = new UXVBox([$textUrl]);
        $box->style = '-fx-alignment: CENTER;';
        
        $alert->expandableContent = $box;
        $alert->expanded = true;
        
        switch ($alert->showAndWait()) {
            case 'Да':
                ApiMods::deleteMod($this->boxMods->selectedIndex);
            break;
        }
    }

    /**
     * @event buttonOpenMod.action 
     */
    function doButtonOpenModAction(UXEvent $e = null)
    {    
        execute('explorer.exe  /select, ' . AddonCraft::$listMods[$this->boxMods->selectedIndex]['path']['mod']);
    }

    /**
     * @event buttonUpdateMod.action 
     */
    function doButtonUpdateModAction(UXEvent $e = null)
    {    
        $this->buttonUpdateMod->enabled = false;
        Animation::fadeOut($this->panelInfoMod, 150, function () {
            AddonCraft::$listMods = false;
            $this->boxMods->items->clear();
            $this->boxInfoMod->items->clear();
        
            ApiMods::findMods();
        });
        
        waitAsync(3000, function () {
            $this->buttonUpdateMod->enabled = true;
        });
    }

    /**
     * @event buttonModeMod.action 
     */
    function doButtonModeModAction(UXEvent $e = null)
    {
        if ($this->boxMods->selectedIndex > -1) {
            ApiMods::setMode($this->boxMods->selectedIndex);
        }
    }

    /**
     * @event boxEnTextures.action 
     */
    function doBoxEnTexturesAction(UXEvent $e = null)
    {    
        if ($e->sender->selectedIndex > -1) $this->boxTextures->selectedIndex = -1;
    }

    /**
     * @event boxTextures.action 
     */
    function doBoxTexturesAction(UXEvent $e = null)
    {    
        if ($e->sender->selectedIndex > -1) $this->boxEnTextures->selectedIndex = -1;
    }

    /**
     * @event buttonUpdateTexture.action 
     */
    function doButtonUpdateTextureAction(UXEvent $e = null)
    {    
        $this->buttonUpdateTexture->enabled = false;
        AddonCraft::$listTextures = false;
        $this->boxTextures->items->clear();
        $this->boxEnTextures->items->clear();
        
        ApiTextures::findTextures();
        
        waitAsync(3000, function () {
            $this->buttonUpdateTexture->enabled = true;
        });
    }

    /**
     * @event buttonFolderTexture.action 
     */
    function doButtonFolderTextureAction(UXEvent $e = null)
    {    
        open(AddonCraft::getPathMinecraft() . '\\resourcepacks\\');
    }

    /**
     * @event boxShaders.action 
     */
    function doBoxShadersAction(UXEvent $e = null)
    {    
        if ($e->sender->items->count() > 0)
            ApiShaders::selectedShader($e->sender->selectedIndex);
    }

    /**
     * @event antialiasingLevel.action 
     */
    function doAntialiasingLevelAction(UXEvent $e = null)
    {    
        
    }

    function setModeMod ($mode) {
        if ($mode) {
            $this->buttonModeMod->text = 'Отключить';
            $this->buttonModeMod->textColor = '#b31a1a';
        } else if (!$mode) {
            $this->buttonModeMod->text = 'Включить';
            $this->buttonModeMod->textColor = '#00e209';
        }
    }

}
