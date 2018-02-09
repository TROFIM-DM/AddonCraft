<?php
namespace trofim\scripts\api;

use windows;
use framework;
use gui;
use Exception;
use php\compress\ZipFile;
use std;
use trofim;

class ApiShaders 
{

    /**
     * Список настроек для шейдера.
     */
    private static $listSettings = ['antialiasingLevel', 'normalMapEnabled', 'specularMapEnabled', 'renderResMul', 'shadowResMul', 'handDepthMul', 'oldHandLight', 'oldLighting'];
    
    /**
     * Поиск шейдер-паков.
     */
    public static function findShaders () {
        
        /*Проверка на существование папки shaderpacks*/
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\shaderpacks\\')) {
        
            /*Поиск файлов shaderpacks*/
            $fileShaders = new File(AddonCraft::getPathMinecraft() . '\\shaderpacks\\');
            foreach ($fileShaders->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getName()) == 'zip') {
                
                    /*Добавление названия для shaderpack'a*/
                    $shaderInfo['name'] = fs::name($file->getPath());
                    
                    /*Добавление путей до shaderpack'a*/
                    $shaderInfo['path']['shader'] = $file->getPath();
                    
                    /*Добавление shaderpack'a в список*/
                    AddonCraft::$listShaders['list'][] = $shaderInfo;
                    
                    /*Создание item shaderpack*/
                    app()->form(MainForm)->boxShaders->items->add(fs::nameNoExt($file->getPath()));
                    
                    /*Регистрация файлов для запрета на изменение*/
                    AddonCraft::registerFile($file->getPath());
                }
            }
            
        }
        
        $enabledShader = app()->form(MainForm)->iniShader->get('shaderPack');
        foreach (AddonCraft::$listShaders['list'] as $key => $shader) {
            if ($enabledShader == $shader['name']) {
                self::selectedShader($key);
                break;
            }
        }
    }
    
    public static function addShader () {
        /*Проверка на наличие папки shaders*/
            /*if (!$zipFile->has('shaders/')) {
                $alert = new UXAlert('INFORMATION');
                $alert->title = 'AddonCraft';
                $alert->headerText = 'Установка шейдер-пака...';
                $alert->contentText = 'Не удалось проверить шейдер-пак!' . "\n" . 'Все равно установить?';
                $alert->setButtonTypes(['Да', 'Нет']);
                $alert->graphic = new UXImageView(new UXImage('res://.data/img/add-24.png'));
                
                $textUrl = new UXLabelEx(fs::nameNoExt($shader['path']));
                $textUrl->style = '-fx-font-family: "Impact"; -fx-font-size: 26px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
                $box = new UXVBox([$textUrl]);
                $box->style = '-fx-alignment: CENTER;';
                
                $alert->expandableContent = $box;
                $alert->expanded = true;
                
                switch ($alert->showAndWait()) {
                    case 'Нет':
                        app()->form(MainForm)->toast('[Шейдер-пак] Установка была прервана!');
                    break;
                }
            }*/
    }
    
    public static function selectedShader ($index) {
        if (app()->form(MainForm)->boxShaders->selectedIndex == -1)
            app()->form(MainForm)->boxShaders->selectedIndex = $index;
        AddonCraft::$listShaders['enabled'] = $index;
        
        app()->form(MainForm)->iniShader->set('shaderPack', AddonCraft::$listShaders['list'][$index]['name']);
        self::setShadersOptions();
        
        foreach (self::$listSettings as $key => $setting) {
            if ($index == 0 && $key)
                app()->form(MainForm)->{$setting}->enabled = false;
            else
                app()->form(MainForm)->{$setting}->enabled = true;
        }
    }
    
    private static function editSettings ($settings) {
    
        foreach (self::$listSettings as $setting) {
            switch ($setting) {
                case 'antialiasingLevel':
                    if (!key_exists($setting, $settings)) 
                        $settings[$setting] = 0;
                    app()->form(MainForm)->{$setting}->text = Language::translate('shader.option.' . $setting) . ': ' . Language::translate('shader.option.' . $setting . '.' . $settings[$setting]);
                break;
                case 'normalMapEnabled':
                case 'specularMapEnabled':
                    if (!key_exists($setting, $settings))
                        $settings[$setting] = true;
                    app()->form(MainForm)->{$setting}->text = Language::translate('shader.option.' . $setting) . ': ' . Language::translate('command.' . $settings[$setting]);
                break;
                case 'renderResMul':
                case 'shadowResMul':
                    if (!key_exists($setting, $settings))
                        $settings[$setting] = 1.0;
                    app()->form(MainForm)->{$setting}->text = Language::translate('shader.option.' . $setting) . ': ' . $settings[$setting] . 'x';
                break;
                case 'handDepthMul':
                    if (!key_exists($setting, $settings))
                        $settings[$setting] = 0.125;
                    switch ($settings[$setting]) {
                        case 0.0625:
                            $value = '0.5x';
                        break;
                        case 0.125:
                            $value = '1x';
                        break;
                        case 0.25:
                            $value = '2x';
                        break;
                    }
                    app()->form(MainForm)->{$setting}->text = Language::translate('shader.option.' . $setting) . ': ' . $value;
                break;
                case 'oldHandLight':
                case 'oldLighting':
                    if (!key_exists($setting, $settings))
                        $settings[$setting] = 'default';
                    app()->form(MainForm)->{$setting}->text = Language::translate('shader.option.' . $setting) . ': ' . Language::translate('command.' . $settings[$setting]);
                break;
            }
        }
        app()->form(MainForm)->iniShader->put($settings);
    }
    
    public static function getShadersOptions () {
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\optionsshaders.txt'))
            $pathOptions = AddonCraft::getPathMinecraft() . '\\optionsshaders.txt';
        else {
            $pathOptions = 'res://files/optionsshaders.txt';
            copy($pathOptions, AddonCraft::getPathMinecraft() . '\\optionsshaders.txt');
        }
        
        app()->form(MainForm)->iniShader->path = AddonCraft::getPathMinecraft() . '\\optionsshaders.txt';
        app()->form(MainForm)->iniShader->load();
        if (app()->form(MainForm)->iniShader->get('shaderPack')) {
            self::editSettings(app()->form(MainForm)->iniShader->toArray()['']);
            return true;
        }
        else return false;
    }
    
    public static function setShadersOptions () {
        if (fs::exists(AddonCraft::getPathMinecraft())) {
            app()->form(MainForm)->iniShader->save();
        }
        return false;
    }
    
    public static function getVideoCard () {
        if ($videoCard = Windows::getVideo()[0])
            $infoVideo = $videoCard['VideoProcessor'] . ' ' .
                         $videoCard['AdapterRAM'][0] . 'GB' . ' (' .
                         $videoCard['CurrentHorizontalResolution'] . 'x' .
                         $videoCard['CurrentVerticalResolution'] . ')';
        return empty($infoVideo) ? false : $infoVideo;
    }
}