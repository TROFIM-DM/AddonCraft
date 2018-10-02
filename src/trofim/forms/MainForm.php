<?php
namespace trofim\forms;

use std, gui, framework, trofim;
use Exception;
use php\compress\ZipFile;
use trofim\scripts\lang\Language as L;

/**
 * Класс формы MainForm.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class MainForm extends AbstractForm
{
    
    /**
     * Загрузка формы.
     * 
     * @event construct 
     */
    function doConstruct (UXEvent $e = null)
    {
        $this->on('dragOver', function (UXDragEvent $drag) {
            if (count($drag->dragboard->files) == 1) {
                if (($this->tabPane->selectedIndex == 0 && fs::ext($drag->dragboard->files[0]->getName()) == 'jar') ||
                    ($this->tabPane->selectedIndex == 1 && fs::ext($drag->dragboard->files[0]->getName()) == 'zip') ||
                    ($this->tabPane->selectedIndex == 2 && fs::ext($drag->dragboard->files[0]->getName()) == 'zip') ||
                    ($this->tabPane->selectedIndex == 3 && fs::isDir($drag->dragboard->files[0]->getPath()))) {
                    $drag->acceptTransferModes(['MOVE', 'COPY']);
                }
            }
            $drag->consume();
        });
        $this->on('dragDrop', function (UXDragEvent $drag) { 
            if (count($drag->dragboard->files)) {
                foreach ($drag->dragboard->files as $file) {
                    if ($this->tabPane->selectedIndex == 0 && fs::ext($file->getName()) == 'jar')
                        ApiMods::add($file);
                    else if ($this->tabPane->selectedIndex == 1 && fs::ext($file->getName()) == 'zip')
                        ApiTextures::add($file);
                    else if ($this->tabPane->selectedIndex == 2 && fs::ext($file->getName()) == 'zip')
                        ApiShaders::add($file);
                    else if ($this->tabPane->selectedIndex == 3 && fs::isDir($file->getPath()))
                        ApiMaps::add($file);
                }
                return;
            }
        });
        Wiki::switch(0);
    }
    
    /**
     * Появление формы.
     * 
     * @event show 
     */
    function doShow (UXWindowEvent $e = null)
    {    
        // Плавное появление окна
        Animation::fadeOut($this, 1, function () {
            $this->panelInfoMod->visible = false;
            $this->panelInfoVersion->visible = false;
            Animation::fadeIn($this, 350);
        });
    }
    
    /**
     * @event imageHide.click-Left 
     */
    function doImageHideClickLeft (UXMouseEvent $e = null)
    {    
        $this->iconified = !$this->iconified;
    }

    /**
     * @event imageExit.click-Left 
     */
    function doImageExitClickLeft (UXMouseEvent $e = null)
    {    
        Animation::fadeOut($this, 350, function () {
            app()->shutdown();
        });
    }
    
    /**
     * @event buttonWiki.action 
     */
    function doButtonWikiAction (UXEvent $e = null)
    {    
        Wiki::open();
    }
    
    /**
     * @event tabPane.change 
     */
    function doTabPaneChange (UXEvent $e = null)
    {    
        Wiki::switch($e->sender->selectedIndex);
    }
    
    /**
     * @event boxMods.action 
     */
    function doBoxModsAction (UXEvent $e = null)
    {
        if ($e->sender->items->count > 0 && $e->sender->selectedIndex > -1) {
            if (!$this->panelInfoMod->visible) $this->panelInfoMod->visible = true;
            $this->boxInfoMod->items->clear();
            DesignMods::showInfo(ApiMods::getObjects()[$e->sender->selectedIndex]);
        }
    }

    /**
     * @event buttonDeleteMod.action 
     */
    function doButtonDeleteModAction (UXEvent $e = null)
    {    
        $alert = new UXAlert('INFORMATION');
        $alert->title = app()->getName();
        $alert->headerText = L::translate('mainform.message.mods.delete.header');
        $alert->contentText = L::translate('mainform.message.mods.delete.content');
        $alert->setButtonTypes([L::translate('word.yes'), L::translate('word.no')]);
        $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/delete_alert-24.png'));
        
        $textUrl = new UXLabelEx(ApiMods::getObjects()[$this->boxMods->selectedIndex]['info']['name']);
        $textUrl->style = '-fx-font-family: "Impact"; -fx-font-size: 22px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
        $box = new UXVBox([$textUrl]);
        $box->style = '-fx-alignment: CENTER;';
        
        $alert->expandableContent = $box;
        $alert->expanded = true;
        
        switch ($alert->showAndWait()) {
            case L::translate('word.yes'):
                ApiMods::delete($this->boxMods->selectedIndex);
            break;
        }
    }

    /**
     * @event buttonOpenMod.action 
     */
    function doButtonOpenModAction (UXEvent $e = null)
    {    
        execute('explorer.exe  /select, "' . ApiMods::getObjects()[$this->boxMods->selectedIndex]['path']['mod'] . '"');
    }

    /**
     * @event buttonUpdateMod.action 
     */
    function doButtonUpdateModAction (UXEvent $e = null)
    {    
        $e->sender->enabled = false;
        
        ApiMods::clearValue('objectsInfo');
        $this->panelInfoMod->visible = false;
        $this->boxMods->items->clear();
        $this->boxInfoMod->items->clear();
        
        ApiMods::find();
        
        waitAsync(3000, function () use ($e) {
            $e->sender->enabled = true;
        });
    }

    /**
     * @event buttonModeMod.action 
     */
    function doButtonModeModAction (UXEvent $e = null)
    {
        if ($this->boxMods->selectedIndex > -1) 
            ApiMods::setMode($this->boxMods->selectedIndex);
    }

    /**
     * @event boxEnTextures.action
     */
    function doBoxEnTexturesAction (UXEvent $e = null)
    {    
        if ($e->sender->selectedIndex > -1)
            $this->boxTextures->selectedIndex = -1;
    }

    /**
     * @event boxTextures.action 
     */
    function doBoxTexturesAction (UXEvent $e = null)
    {    
        if ($e->sender->selectedIndex > -1)
            $this->boxEnTextures->selectedIndex = -1;
    }

    /**
     * @event buttonUpdateTexture.action 
     */
    function doButtonUpdateTextureAction (UXEvent $e = null)
    {    
        $e->sender->enabled = false;
        
        ApiTextures::clearValue('objectsInfo');
        $this->boxTextures->items->clear();
        $this->boxEnTextures->items->clear();
        
        ApiTextures::find();
        
        waitAsync(3000, function () use ($e) {
            $e->sender->enabled = true;
        });
    }

    /**
     * @event buttonFolderTexture.action 
     */
    function doButtonFolderTextureAction (UXEvent $e = null)
    {    
        open(Path::getPathMinecraft() . '\\resourcepacks\\');
    }

    /**
     * @event boxShaders.action 
     */
    function doBoxShadersAction (UXEvent $e = null)
    {    
        if ($e->sender->items->count() > 0)
            ApiShaders::selected($e->sender->selectedIndex);
    }

    /**
     * @event buttonFolderShader.action 
     */
    function doButtonFolderShaderAction (UXEvent $e = null)
    {    
        open(Path::getPathMinecraft() . '\\shaderpacks\\');
    }

    /**
     * @event buttonUpdateShader.action 
     */
    function doButtonUpdateShaderAction (UXEvent $e = null)
    {    
        $e->sender->enabled = false;
        
        ApiShaders::clearValue('objectsInfo');
        $this->boxShaders->items->clear();
        $this->boxShaders->items->addAll([L::translate('word.no'), '(' . L::translate('word.internal') . ')']);
        
        ApiShaders::find();
        
        waitAsync(3000, function () use ($e) {
            $e->sender->enabled = true;
        });
    }

    /**
     * @event boxShaders.construct 
     */
    function doBoxShadersConstruct (UXEvent $e = null)
    {    
        $e->sender->items->addAll([L::translate('word.no'), '(' . L::translate('word.internal') . ')']);
    }

    /**
     * @event boxMaps.action 
     */
    function doBoxMapsAction (UXEvent $e = null)
    {    
        if ($e->sender->selectedIndex != -1 && !$this->buttonEditMap->enabled)
            $this->buttonDeleteMap->enabled =
            $this->buttonFolderMap->enabled =
            $this->buttonEditMap->enabled = true;
    }

    /**
     * @event buttonFolderMap.action 
     */
    function doButtonFolderMapAction (UXEvent $e = null)
    {    
        open(ApiMaps::getObjects()[$this->boxMaps->selectedIndex]['path']['map']);
    }

    /**
     * @event buttonUpdateMap.action 
     */
    function doButtonUpdateMapAction (UXEvent $e = null)
    {    
        $e->sender->enabled = false;
        
        ApiMaps::clearValue('objectsInfo');
        $this->boxMaps->items->clear();
        
        ApiMaps::find();
        
        if ($this->buttonEditMap->enabled)
            $this->buttonDeleteMap->enabled =
            $this->buttonFolderMap->enabled =
            $this->buttonEditMap->enabled = false;
        
        waitAsync(3000, function () use ($e) {
            $e->sender->enabled = true;
        });
    }

    /**
     * @event buttonDeleteMap.action 
     */
    function doButtonDeleteMapAction (UXEvent $e = null)
    {    
        $alert = new UXAlert('INFORMATION');
        $alert->title = app()->getName();
        $alert->headerText = L::translate('mainform.message.maps.delete.header');
        $alert->contentText = L::translate('mainform.message.maps.delete.content');
        $alert->setButtonTypes([L::translate('word.yes'), L::translate('word.no')]);
        $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/delete_alert-24.png'));
        
        $textUrl = new UXLabelEx(ApiMaps::getObjects()[$this->boxMaps->selectedIndex]['info']['LevelName']);
        $textUrl->style = '-fx-font-family: "Impact"; -fx-font-size: 22px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
        $box = new UXVBox([$textUrl]);
        $box->style = '-fx-alignment: CENTER;';
        
        $alert->expandableContent = $box;
        $alert->expanded = true;
        
        switch ($alert->showAndWait()) {
            case L::translate('word.yes'):
                ApiMaps::delete($this->boxMaps->selectedIndex);
            break;
        }
    }

    /**
     * @event buttonEditMap.action 
     */
    function doButtonEditMapAction (UXEvent $e = null)
    {    
        app()->form(EditMapForm)->showForm($this->boxMaps->selectedIndex);
    }

    /**
     * @event buttonUpdateVersion.action 
     */
    function doButtonUpdateVersionAction (UXEvent $e = null)
    {    
        $e->sender->enabled = false;
        
        ApiVersions::clearValue('objectsInfo');
        $this->boxVersions->items->clear();
        
        ApiVersions::find();
        
        waitAsync(3000, function () use ($e) {
            $e->sender->enabled = true;
        });
    }

    /**
     * @event boxVersions.action 
     */
    function doBoxVersionsAction (UXEvent $e = null)
    {    
        $this->boxInfoVersion->items->clear();
        if ($e->sender->items->count() > 0 && $e->sender->selectedIndex > -1)
            ApiVersions::exists($e->sender->selectedIndex);
            
    }



    function setModeMod ($mode)
    {
        if ($mode) {
            $this->buttonModeMod->text = L::translate('mainform.mods.btn.disabled');
            $this->buttonModeMod->textColor = '#b31a1a';
        } else {
            $this->buttonModeMod->text = L::translate('mainform.mods.btn.enabled');
            $this->buttonModeMod->textColor = '#00e209';
        }
    }

}
