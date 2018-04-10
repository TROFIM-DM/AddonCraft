<?php
namespace trofim\modules;

use framework, trofim;

class MainModule extends AbstractModule
{

    /**
     * @event selectMod.action 
     */
    function doSelectModAction (ScriptEvent $e = null)
    {    
        ApiMods::add($e->sender->file);
    }

    /**
     * @event selectTexture.action 
     */
    function doSelectTextureAction (ScriptEvent $e = null)
    {    
        ApiTextures::add($e->sender->file);
    }

    /**
     * @event selectShader.action 
     */
    function doSelectShaderAction (ScriptEvent $e = null)
    {    
        ApiShaders::add($e->sender->file);
    }

    /**
     * @event dirChooser.action 
     */
    function doDirChooserAction (ScriptEvent $e = null)
    {    
        ApiMaps::add($e->sender->file);
    }
    
}
