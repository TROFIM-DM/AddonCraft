<?php
namespace trofim\scripts\api;

use std, gui, framework, trofim;
use windows;
use Exception;
use php\compress\ZipFile;

/**
 * Класс для работы с API шейдеров.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class ApiShaders 
{

    /**
     * 1 - Список настроек для шейдера.
     * 2 - Параметры настроек.
     */
    private static $LIST_SETTINGS   = ['shaderPack', 'antialiasingLevel', 'normalMapEnabled', 'specularMapEnabled', 'renderResMul', 'shadowResMul', 'handDepthMul', 'oldHandLight', 'oldLighting'],
                   $CHANGE_SETTINGS = ['antialiasingLevel' => ['0', '2', '4'], 'normalMapEnabled' => ['false', 'true'], 'specularMapEnabled' => ['false', 'true'],
                                       'renderResMul' => ['0.5', '1.0', '2.0'], 'shadowResMul' => ['0.5', '1.0', '2.0'], 'handDepthMul' => ['0.0625', '0.125', '0.25'],
                                       'oldHandLight' => ['default', 'false', 'true'], 'oldLighting' => ['default', 'false', 'true']];
    
    /**
     * Поиск шейдер-паков.
     */
    public static function findShaders () {

        // Проверка на существование папки shaderpacks
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\shaderpacks\\')) {
            
            uiLater(function () {
                app()->form(StartForm)->setStatus(Language::translate('word.shaders') . '...');
            });
            
            // Поиск файлов shaderpacks
            $fileShaders = new File(AddonCraft::getPathMinecraft() . '\\shaderpacks\\');
            
            foreach ($fileShaders->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getName()) == 'zip') {
                
                    // Добавление названия для shaderpack'a
                    $shaderInfo['name'] = fs::name($file->getPath());
                    
                    // Добавление путей до shaderpack'a
                    $shaderInfo['path']['shader'] = $file->getPath();
                    
                    // Добавление shaderpack'a в список
                    AddonCraft::$listShaders['list'][] = $shaderInfo;
                    
                    // Создание item shaderpack
                    app()->form(MainForm)->boxShaders->items->add(fs::nameNoExt($file->getPath()));
                    
                    // Регистрация файлов для запрета на изменение
                    //AddonCraft::registerFile($file->getPath());
                }
            }
            
        }
        
        // Поиск активного shaderpack'a
        $enabledShader = app()->form(MainForm)->iniShader->get('shaderPack');
        foreach (AddonCraft::$listShaders['list'] as $key => $shader) {
            if ($enabledShader == $shader['name']) {
                self::selectedShader($key);
                break;
            }
        }
    }
    
    /**
     * Добавление шейдера.
     * 
     * @param $SHADER
     */
    public static function addShader ($SHADER) {
    
        // Проверки
        if (fs::isFile($SHADER->getPath()) && fs::ext($SHADER->getPath()) == 'zip') {
            
            // Поиск файлов в папке shaderpacks
            $filesShaders = new File(AddonCraft::getPathMinecraft() . '\\shaderpacks\\');
            foreach ($filesShaders->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'zip' &&
                    $file->getName() == $SHADER->getName()) {
                    app()->form(MainForm)->toast(Language::translate('mainform.toast.shaders.exist'));
                    return;
                }
            }
            
            try {
                
                 // Создание ZIP
                $zipFile = new ZipFile($SHADER->getPath());
                
                // Проверка на наличие папки shaders
                if (!$zipFile->has('shaders/')) {
                    $alert = new UXAlert('INFORMATION');
                    $alert->title = app()->getName();
                    $alert->headerText = Language::translate('mainform.message.shaders.setup.header');
                    $alert->contentText = Language::translate('mainform.message.shaders.setup.content');
                    $alert->setButtonTypes([Language::translate('word.yes'), Language::translate('word.no')]);
                    $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/add-24.png'));
                    
                    $nameFile = new UXLabelEx(fs::nameNoExt($SHADER->getPath()));
                    $nameFile->style = '-fx-font-family: "Impact"; -fx-font-size: 22px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
                    $box = new UXVBox([$nameFile]);
                    $box->style = '-fx-alignment: CENTER;';
                    
                    $alert->expandableContent = $box;
                    $alert->expanded = true;
                    
                    switch ($alert->showAndWait()) {
                        case Language::translate('word.no'):
                            app()->form(MainForm)->toast(Language::translate('mainform.toast.shaders.not.setup.error'));
                            return;
                        break;
                    }
                }
                
                // Путь добавленного shaderpack'a
                $pathShader = AddonCraft::getPathMinecraft() . '\\shaderpacks\\' . $SHADER->getName();
                
                // Создание папки shaderpacks, если нет
                if (!fs::exists(AddonCraft::getPathMinecraft() . '\\shaderpacks\\'))
                    fs::makeDir(AddonCraft::getPathMinecraft() . '\\shaderpacks\\');
                
                // Добавление shaderpack'a
                if (!fs::copy($SHADER->getPath(), $pathShader)) {
                    app()->form(MainForm)->toast(Language::translate('mainform.toast.shaders.not.setup'));
                    return;
                }
                
                // Добавление названия для shaderpack'a
                $shaderInfo['name'] = fs::name($SHADER->getPath());
                
                // Добавление путей до shaderpack'a
                $shaderInfo['path']['shader'] = $pathShader;
                
                // Добавление shaderpack'a в список
                AddonCraft::$listShaders['list'][] = $shaderInfo;
                
                // Создание item shaderpack
                app()->form(MainForm)->boxShaders->items->add(fs::nameNoExt($SHADER->getPath()));
                
                // Регистрация файлов для запрета на изменение
                //AddonCraft::registerFile($SHADER->getPath());
                
                // Сообщение о успешном добавлении текстур-пака
                app()->form(MainForm)->toast(Language::translate('mainform.toast.shaders.added'));
                
            } catch (Exception $error) {
                app()->form(MainForm)->toast(Language::translate('mainform.toast.shaders.unknown.error'));
                return;
            }
            
        } else {
            app()->form(MainForm)->toast(Language::translate('mainform.toast.shaders.select.file'));
        }
        
    }
    
    /**
     * Выбор шейдеров.
     * 
     * @param $index
     */
    public static function selectedShader ($index) {
        if (app()->form(MainForm)->boxShaders->selectedIndex == -1)
            app()->form(MainForm)->boxShaders->selectedIndex = $index;
        AddonCraft::$listShaders['enabled'] = $index;
        
        app()->form(MainForm)->iniShader->set('shaderPack', AddonCraft::$listShaders['list'][$index]['name']);
        self::setShadersOptions();
        
        foreach (self::$LIST_SETTINGS as $key => $setting) {
            if ($index == 0 && $key > 1)
                app()->form(MainForm)->{$setting}->enabled = false;
            elseif ($index > 0 && $key > 1)
                app()->form(MainForm)->{$setting}->enabled = true;
        }
    }
    
    /**
     * Изменение настроек шейдеров.
     * 
     * @param $setting
     */
    public static function changeSetting ($setting) {
        $change = self::$CHANGE_SETTINGS[$setting];
        $searchKey = array_search($change, app()->form(MainForm)->iniShader->get($setting), false);
        if (++$searchKey == count($change)) $searchKey = 0;
        app()->form(MainForm)->iniShader->set($setting, $change[$searchKey]);
        self::setShadersOptions();
        self::handlerSettings(app()->form(MainForm)->iniShader->toArray()[''], false);
    }
    
    /**
     * Получение опций шейдеров.
     */
    public static function getShadersOptions () {
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\optionsshaders.txt'))
            $pathOptions = AddonCraft::getPathMinecraft() . '\\optionsshaders.txt';
        else {
            $pathOptions = 'res://assets/minecraft/optionsshaders.txt';
            copy($pathOptions, AddonCraft::getPathMinecraft() . '\\optionsshaders.txt');
        }
        
        app()->form(MainForm)->iniShader->path = AddonCraft::getPathMinecraft() . '\\optionsshaders.txt';
        if (app()->form(MainForm)->iniShader->load()) {
            self::createActionBtn();
            self::handlerSettings(app()->form(MainForm)->iniShader->toArray()['']);
            return true;
        }
        else return false;
    }
    
    /**
     * Сохранение опций шейдеров.
     */
    private static function setShadersOptions () {
        if (fs::exists(AddonCraft::getPathMinecraft())) {
            app()->form(MainForm)->iniShader->save();
            return true;
        }
        return false;
    }
    
    /**
     * Изменение опций под действием кнопок.
     * 
     * @param $settings
     * @param $put
     */
    private static function handlerSettings ($settings, $put = true) {
        foreach (self::$LIST_SETTINGS as $setting) {
            switch ($setting) {
                case 'shaderPack':
                    if (empty($settings[$setting]) ||
                        ($put && !in_array($settings[$setting], ['OFF', '(internal)']) &&
                        !fs::exists(AddonCraft::getPathMinecraft() . '\\shaderpacks\\' . $settings[$setting])))
                            $settings[$setting] = 'OFF';
                break;
                case 'antialiasingLevel':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['0', '2', '4'])) 
                        $settings[$setting] = 0;
                    app()->form(MainForm)->{$setting}->text = Language::translate('mainform.shaders.option.' . $setting) . ': ' . Language::translate('mainform.shaders.option.' . $setting . '.' . $settings[$setting]);
                break;
                case 'normalMapEnabled':
                case 'specularMapEnabled':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['false', 'true']))
                        $settings[$setting] = 'true';
                    app()->form(MainForm)->{$setting}->text = Language::translate('mainform.shaders.option.' . $setting) . ': ' . Language::translate('command.' . $settings[$setting]);
                break;
                case 'renderResMul':
                case 'shadowResMul':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['0.5', '1.0', '2.0']))
                        $settings[$setting] = '1.0';
                    app()->form(MainForm)->{$setting}->text = Language::translate('mainform.shaders.option.' . $setting) . ': ' . $settings[$setting] . 'x';
                break;
                case 'handDepthMul':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['0.0625', '0.125', '0.25']))
                        $settings[$setting] = '0.125';
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
                    app()->form(MainForm)->{$setting}->text = Language::translate('mainform.shaders.option.' . $setting) . ': ' . $value;
                break;
                case 'oldHandLight':
                case 'oldLighting':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['default', 'false', 'true']))
                        $settings[$setting] = 'default';
                    app()->form(MainForm)->{$setting}->text = Language::translate('mainform.shaders.option.' . $setting) . ': ' . Language::translate('command.' . $settings[$setting]);
                break;
            }
        }
        if ($put) app()->form(MainForm)->iniShader->put($settings);
    }
    
    /**
     * Присваивание действий кнопкам настроек шейдеров.
     */
    private static function createActionBtn () {
        foreach (self::$LIST_SETTINGS as $key => $setting) {
            if ($key > 0) {
                app()->form(MainForm)->{$setting}->tooltip->style = '-fx-text-alignment: LEFT;';
                app()->form(MainForm)->{$setting}->on('action', function () use ($setting) {
                    ApiShaders::changeSetting($setting);
                });
            }
        }
    }
    
    /**
     * Получение настроек видеокарты.
     */
    public static function getVideoCard () {
        if ($videoCard = Windows::getVideo()[0])
            $infoVideo = $videoCard['VideoProcessor'] . ' ' .
                         $videoCard['AdapterRAM'][0] . 'GB' . ' (' .
                         $videoCard['CurrentHorizontalResolution'] . 'x' .
                         $videoCard['CurrentVerticalResolution'] . ')';
        return empty($infoVideo) ? false : $infoVideo;
    }
}