<?php
namespace trofim\modules;

use std, gui, framework, trofim;


class StartModule extends AbstractModule
{

    /**
     * @event timerLabelLoad.action 
     */
    function doTimerLabelLoadAction(ScriptEvent $e = null)
    {    
        switch ($this->labelLoad->text) {
            case 'Загрузка.' :
                $this->labelLoad->text = 'Загрузка..';
            break;
            case 'Загрузка..' :
                $this->labelLoad->text = 'Загрузка...';
            break;
            case 'Загрузка...' :
                $this->labelLoad->text = 'Загрузка.';
            break;
        }
    }
    
}