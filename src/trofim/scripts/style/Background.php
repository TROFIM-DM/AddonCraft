<?php
namespace trofim\scripts\style;

use gui;
use trofim;

class Background 
{

    static function setImage () {
        app()->form(MainForm)->imageGlobal->image = new UXImage('res://.data/img/background/mainForm/mainForm-' . rand(0, 5) . '.png');
    }
    
}