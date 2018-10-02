<?php
namespace trofim\forms;

use std, gui, framework, trofim;

class EditMapForm extends AbstractForm
{
    
    private $mapInfo,
            $index;
    
    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        Animation::fadeOut($this, 1, function () {
            Animation::fadeIn($this, 150);
        });
    }
    
    /**
     * @event imageExit.click-Left 
     */
    function doImageExitClickLeft(UXMouseEvent $e = null)
    {
        $this->closeForm();
    }

    /**
     * @event buttonCancel.action 
     */
    function doButtonCancelAction(UXEvent $e = null)
    {    
        $this->closeForm();
    }

    /**
     * @event listValue.action 
     */
    function doListValueAction(UXEvent $e = null)
    {    
        switch ($e->sender->selectedIndex) {
            case 0:
                $this->selectedValue->selectedIndex = ($this->mapInfo['info']['GameType']) ? 1 : 0;
            break;
            case 1:
                $this->selectedValue->selectedIndex = ($this->mapInfo['info']['hardcore']) ? 1 : 0;
            break;
            case 2:
                $this->selectedValue->selectedIndex = ($this->mapInfo['info']['allowCommands']) ? 1 : 0;
            break;
        }
    }

    /**
     * @event buttonDeleteIcon.action 
     */
    function doButtonDeleteIconAction(UXEvent $e = null)
    {    
        $alert = new UXAlert('INFORMATION');
        $alert->title = app()->getName();
        $alert->headerText = Language::translate('editmapform.message.delete.icon.header');
        $alert->contentText = Language::translate('editmapform.message.delete.icon.content');
        $alert->setButtonTypes([Language::translate('word.yes'), Language::translate('word.no')]);
        $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/delete_alert-24.png'));
        
        switch ($alert->showAndWait()) {
            case Language::translate('word.yes'):
                if (ApiMaps::deleteIcon($this->index))
                    unset($this->mapInfo['path']['icon']);
            break;
        }
    }

    /**
     * @event selectedValue.action 
     */
    function doSelectedValueAction(UXEvent $e = null)
    {    
        switch ($this->listValue->selectedIndex) {
            case 0:
                $this->mapInfo['info']['GameType'] = $e->sender->selectedIndex;
            break;
            case 1:
                $this->mapInfo['info']['hardcore'] = $e->sender->selectedIndex;
            break;
            case 2:
                $this->mapInfo['info']['allowCommands'] = $e->sender->selectedIndex;
            break;
        }
    }

    /**
     * @event buttonSave.action 
     */
    function doButtonSaveAction(UXEvent $e = null)
    {    
        $this->mapInfo['info']['LevelName'] = $this->editNameMap->text;
        ApiMaps::saveMap($this->mapInfo);
    }

    function closeForm () {
        Animation::fadeOut($this, 150, function () {
            $this->hide();
        });
    }
    
    function showForm ($index) {
        $this->mapInfo = AddonCraft::$listMaps[$index];
        $this->index = $index;
        $this->editNameMap->text = $this->mapInfo['info']['LevelName'];
        $this->buttonDeleteIcon->enabled = (fs::exists($this->mapInfo['path']['icon'])) ? true : false;
        $this->listValue->selectedIndex = 0;
        $this->selectedValue->selectedIndex = ($this->mapInfo['info']['GameType']) ? 1 : 0;
        $this->showAndWait();
    }
    
}
