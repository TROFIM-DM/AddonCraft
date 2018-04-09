<?php
namespace trofim\scripts\minecraft;

use trofim, std;

class Minecraft
{
    
    /**
     * Получить настройки Minecraft.
     * 
     * @return bool
     */
    static function getMinecraftOptions () : bool
    {
        if (!fs::exists(Path::getPathMinecraft() . '\\options.txt'))
            copy('res://assets/minecraft/options.txt', Path::getPathMinecraft() . '\\options.txt');
        
        if ($fileOptions = file(Path::getPathMinecraft() . '\\options.txt')) {
            foreach ($fileOptions as $option) {
                $explode = explode(':', $option);
                $options[$explode[0]] = $explode[1];
            }
            if (isset($options) && app()->getForm(MainForm)->iniOptions->put($options))
                return true;
        }
        return false;
    }
    
    /**
     * Изменить настройки Minecraft.
     * 
     * @param array $options
     * @return bool
     */
    static function setMinecraftOptions (array $options) : bool
    {
        foreach ($options[''] as $key => $option) 
            if (isset($key) && isset($option))
                $fileOptions[] = $key . ':' . $option;
        if (Stream::putContents(Path::getPathMinecraft() . '\\options.txt', implode("\n", $fileOptions)))
            return true;
        return false;
    }
    
}