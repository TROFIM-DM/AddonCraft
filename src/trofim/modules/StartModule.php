<?php
namespace trofim\modules;

use std, gui, framework, trofim;
use trofim\scripts\lang\Language as L;

class StartModule extends AbstractModule
{

    /**
     * @event timerLabelLoad.action 
     */
    function doTimerLabelLoadAction (ScriptEvent $e = null)
    {    
        $count = [1 => '..', 2 => '...', 3 => '.'];
        $this->labelLoad->text = L::translate('startform.label.load') . $count[substr_count($this->labelLoad->text, '.')];
    }
    
}