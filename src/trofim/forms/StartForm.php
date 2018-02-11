<?php
namespace trofim\forms;

use Exception;
use facade\Json;
use bundle\zip\ZipFileScript;
use php\compress\ZipFile;
use Exception;
use windows;
use std, gui, framework, trofim;
use trofim\scripts\design;


class StartForm extends AbstractForm
{

    /**
     * Появление формы
     * 
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    

        try {

            /* Загрузка языка */
            Language::getLanguage();
            
            /* Пропись версии AddonCraft */
            $this->labelStatus->text = 'v' . AddonCraft::getAppVersion() . ' ' . AddonCraft::getAppVersionPrefix();
        
            /* Включение таймера с изменением LabelLoad */
            $this->timerLabelLoad->enabled = true;
            
            /* Плавное появление окна */
            Animation::fadeOut($this, 1, function () {
                Animation::fadeIn($this, 350);
            });
            
            /* Проверка запуска программы от имени администратора */
            if (Windows::isAdmin()) {
                throw new Exception(Language::translate('startform.message.not.admin'));
            }
            
            /* Проверка папки AddonCraft в AppData */
            if (!fs::exists(AddonCraft::getAppPath())) {
                mkdir(AddonCraft::getAppPath());
            }
            
            /* Проверка на целостность папок и файлов в AppData */
            foreach (AddonCraft::$appPath as $path) {
                if (!fs::exists(AddonCraft::getAppPath() . $path)) 
                    mkdir(AddonCraft::getAppPath() . $path);
            }
            
            /* Проверка папки AddonCraft в Temp */
            if (!fs::exists(AddonCraft::getAppTemp())) {
                mkdir(AddonCraft::getAppTemp());
            }
            
            /* Проверка на существование папки .minecraft */
            if (fs::exists(AddonCraft::getPathMinecraft())) {
            
                /* Получение настроек Minecraft */
                AddonCraft::getMinecraftOptions();
                
                /* Получение настроек Shaders */
                ApiShaders::getShadersOptions();
                
                /* Замедление процесса загрузки */
                waitAsync(rand(500, 1000), function () {
                
                    /* Работа с файлами mods */
                    if (fs::exists(AddonCraft::getPathMinecraft() . '\\mods\\')) {
                        uiLaterAndWait(function () {
                            //$this->labelStatus->text = 'Mods...';
                            app()->form(MainForm)->boxMods->placeholder = AddonCraft::createPlaceholder();
                            ApiMods::findMods();
                        });
                    }
                    
                    /* Работа с файлами resourcepacks */
                    if (fs::exists(AddonCraft::getPathMinecraft() . '\\resourcepacks\\')) {
                        uiLaterAndWait(function () {
                            //$this->labelStatus->text = 'Textures...';
                            app()->form(MainForm)->boxTextures->placeholder = AddonCraft::createPlaceholder();
                            app()->form(MainForm)->boxEnTextures->placeholder = AddonCraft::createPlaceholder();
                            ApiTextures::findTextures();
                        });
                    }
                    
                    /* Работа с файлами shaderpacks */
                    uiLaterAndWait(function () {
                        //$this->labelStatus->text = 'Shaders...';
                        app()->form(MainForm)->labelShaderVideo->text = ApiShaders::getVideoCard();
                        ApiShaders::findShaders();
                    });
                    
                    /* Загрузка MainForm */
                    //uiLaterAndWait(function () {
                        Animation::fadeOut($this, 350, function () {
                            waitAsync(10, function () {
                                $this->timerLabelLoad->stop();
                                $this->loadForm(MainForm);
                            });
                        });
                    //});
                    
                });
                
            } else {
                throw new Exception(Language::translate('startform.message.not.minecraft'));
            }
            
        } catch (Exception $error) {
            if (UXDialog::showAndWait($error->getMessage())) exit();
        }
        
    }

}
