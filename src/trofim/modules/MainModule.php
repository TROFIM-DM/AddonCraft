<?php
namespace trofim\modules;

use facade\Json;
use php\compress\ZipFile;
use std, gui, framework, trofim;


class MainModule extends AbstractModule
{

    /**
     * @event selectMod.action 
     */
    function doSelectModAction(ScriptEvent $e = null)
    {    
        ApiMods::addMod($e->sender->file);
    }

    /**
     * @event selectTexture.action 
     */
    function doSelectTextureAction(ScriptEvent $e = null)
    {    
        ApiTextures::addTexture($e->sender->file);
    }

}
