<?php
namespace trofim\forms;

use std, gui, framework, trofim;
use trofim\scripts\lang\Language as L;

class SettingsForm extends AbstractForm
{
    
    /**
     * @event showing 
     */
    function doShowing(UXWindowEvent $e = null)
    {    
        $this->listLanguage->selectedIndex = (L::getLocale() == 'en') ? 0 : 1;
    }
    
    /**
     * @event show 
     */
    function doShow (UXWindowEvent $e = null)
    {    
        Animation::fadeOut($this, 1, function () {
            Animation::fadeIn($this, 150);
        });
    }
    

    /**
     * @event imageExit.click-Left 
     */
    function doImageExitClickLeft (UXMouseEvent $e = null)
    {
        $this->closeForm();
    }

    /**
     * @event buttonSave.action 
     */
    function doButtonSaveAction (UXEvent $e = null)
    {
        $alert = new UXAlert('INFORMATION');
        $alert->title = app()->getName();
        $alert->headerText = L::translate('settingsform.message.save.header');
        $alert->contentText = L::translate('settingsform.message.save.content');
        $alert->setButtonTypes([L::translate('word.yes'), L::translate('word.no')]);
        $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/program/icon_16x16.png'));
        
        
        switch ($alert->showAndWait()) {
            case L::translate('word.yes'):
                $locale = ($this->listLanguage->selectedIndex == 0) ? 'en' : 'ru';
                Settings::getINI()->set('language', $locale);
                Settings::save();
                (new Process($GLOBALS['argv'])->start());
                die;
            break;
        }
    }

    /**
     * @event listLanguage.action 
     */
    function doListLanguageAction(UXEvent $e = null)
    {    
        if (L::getList()[L::getLocale()]['name'] == $e->sender->selectedItem)
            $this->buttonSave->enabled = false;
        else
            $this->buttonSave->enabled = true;
    }
    
    function closeForm () {
        Animation::fadeOut($this, 150, function () {
            $this->hide();
        });
    }

}