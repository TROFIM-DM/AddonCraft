<?php
namespace trofim\scripts\api;

use std, gui, framework, trofim;
use Exception, windows;
use php\compress\ZipFile;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с API шейдеров.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class ApiShaders 
{

    /**
     * Список информации о шейдерах.
     * 
     * @var array
     */
    private static $objectsInfo = ['list' => [['name' => 'OFF'], ['name' => '(internal)']]];

    /**
     * Основные настройки шейдеров.
     * 
     * @var array
     */
    private static $LIST_SETTINGS   = ['shaderPack', 'antialiasingLevel', 'normalMapEnabled', 'specularMapEnabled',
                                       'renderResMul', 'shadowResMul', 'handDepthMul', 'oldHandLight', 'oldLighting'];
    
    /**
     * Значения настроек шейдеров.
     * 
     * @var array
     */
    private static $CHANGE_SETTINGS = ['antialiasingLevel' => ['0', '2', '4'], 'normalMapEnabled' => ['false', 'true'], 'specularMapEnabled' => ['false', 'true'],
                                     'renderResMul' => ['0.5', '1.0', '2.0'], 'shadowResMul' => ['0.5', '1.0', '2.0'], 'handDepthMul' => ['0.0625', '0.125', '0.25'],
                                     'oldHandLight' => ['default', 'false', 'true'], 'oldLighting' => ['default', 'false', 'true']];
    
    /**
     * Поиск шейдер-паков.
     */
    static function find ()
    {
        // Проверка на существование папки shaderpacks
        if (fs::exists(Path::getPathMinecraft() . '\\shaderpacks\\')) {

            uiLater(function () {
                app()->getForm(StartForm)->setStatus(L::translate('word.shaders') . '...');
            });
            
            // Поиск файлов в папке shaderpacks
            $files = new File(Path::getPathMinecraft() . '\\shaderpacks\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getName()) == 'zip') {
                    
                    // Добавление названия и путей для shaderpack'a
                    $objectInfo = ['name' => fs::name($file->getPath()),
                                   'path' => ['shader' => $file->getPath()]];
                    
                    // Добавление shaderpack'a в список
                    self::$objectsInfo['list'][] = $objectInfo;
                    
                    // Создание item shaderpack
                    $name = fs::nameNoExt($objectInfo['name']);
                    app()->getForm(MainForm)->boxShaders->items->add((str::length($name) > 30) ? str::sub($name, 0, 30) . '...' : $name);
                }
            }
        }
        
        // Поиск активного shaderpack'a
        $enabledShader = app()->getForm(MainForm)->iniShader->get('shaderPack');
        foreach (self::$objectsInfo['list'] as $key => $shader) {
            if ($enabledShader == $shader['name']) {
                self::selected($key);
                break;
            }
        }
    }
    
    /**
     * Добавить шейдер.
     * 
     * @param File $object
     */
    static function add (File $object)
    {
        // Проверки
        if (fs::isFile($object->getPath()) && fs::ext($object->getPath()) == 'zip') {
            
            // Поиск файлов в папке shaderpacks
            $files = new File(Path::getPathMinecraft() . '\\shaderpacks\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'zip' &&
                    $file->getName() == $object->getName()) {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.shaders.exist'));
                    return;
                }
            }
            
            try {
                
                 // Создание ZIP
                $zipFile = new ZipFile($object->getPath());
                
                // Проверка на наличие папки shaders
                if (!$zipFile->has('shaders/')) {
                    $alert = new UXAlert('INFORMATION');
                    $alert->title = app()->getName();
                    $alert->headerText = L::translate('mainform.message.shaders.setup.header');
                    $alert->contentText = L::translate('mainform.message.shaders.setup.content');
                    $alert->setButtonTypes([L::translate('word.yes'), L::translate('word.no')]);
                    $alert->graphic = new UXImageView(new UXImage('res://.data/img/icon/add-24.png'));
                    
                    $nameFile = new UXLabelEx(fs::nameNoExt($object->getPath()));
                    $nameFile->style = '-fx-font-family: "Impact"; -fx-font-size: 22px; -fx-text-alignment: CENTER; -fx-alignment: CENTER;';
                    $box = new UXVBox([$nameFile]);
                    $box->style = '-fx-alignment: CENTER;';
                    
                    $alert->expandableContent = $box;
                    $alert->expanded = true;
                    
                    switch ($alert->showAndWait()) {
                        case L::translate('word.no'):
                            app()->getForm(MainForm)->toast(L::translate('mainform.toast.shaders.not.setup.error'));
                            return;
                        break;
                    }
                    unset($zipFile);
                }
                
                // Путь добавленного shaderpack'a
                $pathShader = Path::getPathMinecraft() . '\\shaderpacks\\' . $object->getName();
                
                // Создание папки shaderpacks, если нет
                if (!fs::exists(Path::getPathMinecraft() . '\\shaderpacks\\'))
                    fs::makeDir(Path::getPathMinecraft() . '\\shaderpacks\\');
                
                // Добавление shaderpack'a
                if (!fs::copy($object->getPath(), $pathShader)) {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.shaders.not.setup'));
                    return;
                }
                
                // Добавление названия и путей для shaderpack'a
                $objectInfo = ['name' => $object->getName(),
                               'path' => ['shader' => $pathShader]];
                
                // Добавление shaderpack'a в список
                self::$objectsInfo['list'][] = $objectInfo;
                
                // Создание item shaderpack
                $name = fs::nameNoExt($objectInfo['name']);
                app()->getForm(MainForm)->boxShaders->items->add((str::length($name) > 30) ? str::sub($name, 0, 30) . '...' : $name);
                
                // Сообщение о успешном добавлении текстур-пака
                app()->getForm(MainForm)->toast(L::translate('mainform.toast.shaders.added'));
                
            } catch (Exception $error) {
                app()->getForm(MainForm)->toast(L::translate('mainform.toast.shaders.unknown.error'));
                return;
            }
            
        } else {
            app()->getForm(MainForm)->toast(L::translate('mainform.toast.shaders.select.file'));
        }
    }
    
    /**
     * Выбрать шейдер.
     * 
     * @param int $index
     */
    static function selected (int $index)
    {
        if (app()->getForm(MainForm)->boxShaders->selectedIndex == -1)
            app()->getForm(MainForm)->boxShaders->selectedIndex = $index;
        self::$objectsInfo['enabled'] = $index;
        app()->getForm(MainForm)->iniShader->set('shaderPack', self::$objectsInfo['list'][$index]['name']);
        
        self::setShadersOptions();
        
        foreach (self::$LIST_SETTINGS as $key => $setting) {
            if ($index == 0 && $key > 1)
                app()->getForm(MainForm)->{$setting}->enabled = false;
            elseif ($index > 0 && $key > 1)
                app()->getForm(MainForm)->{$setting}->enabled = true;
        }
    }
    
    /**
     * Изменить настройки шейдеров.
     * 
     * @param string $setting
     */
    static function changeSetting (string $setting)
    {
        $change = self::$CHANGE_SETTINGS[$setting];
        $searchKey = array_search($change, app()->getForm(MainForm)->iniShader->get($setting), false);
        if (++$searchKey == count($change)) $searchKey = 0;
        app()->getForm(MainForm)->iniShader->set($setting, $change[$searchKey]);
        self::setShadersOptions();
        self::handlerSettings(app()->getForm(MainForm)->iniShader->toArray()[''], false);
    }
    
    /**
     * Получить опции шейдеров.
     * 
     * @return bool
     */
    static function getShadersOptions () : bool
    {
        if (!fs::exists(Path::getPathMinecraft() . '\\optionsshaders.txt'))
            copy('res://assets/minecraft/optionsshaders.txt', Path::getPathMinecraft() . '\\optionsshaders.txt');
        
        app()->getForm(MainForm)->iniShader->path = Path::getPathMinecraft() . '\\optionsshaders.txt';
        if (app()->getForm(MainForm)->iniShader->load()) {
            self::createActionBtn();
            self::handlerSettings(app()->getForm(MainForm)->iniShader->toArray()['']);
            return true;
        }
        return false;
    }
    
    /**
     * Сохранить опции шейдеров.
     * 
     * @return bool
     */
    private static function setShadersOptions () : bool
    {
        if (fs::exists(Path::getPathMinecraft())) {
            app()->getForm(MainForm)->iniShader->save();
            return true;
        }
        return false;
    }
    
    /**
     * Изменить настройки под действием кнопок.
     * 
     * @param array $settings
     * @param bool $put
     */
    static function handlerSettings (array $settings, bool $put = true)
    {
        foreach (self::$LIST_SETTINGS as $setting) {
            switch ($setting) {
                case 'shaderPack':
                    if (empty($settings[$setting]) ||
                        ($put && !in_array($settings[$setting], ['OFF', '(internal)']) &&
                        !fs::exists(Path::getPathMinecraft() . '\\shaderpacks\\' . $settings[$setting])))
                            $settings[$setting] = 'OFF';
                break;
                case 'antialiasingLevel':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['0', '2', '4'])) 
                        $settings[$setting] = 0;
                    app()->getForm(MainForm)->{$setting}->text = L::translate('mainform.shaders.option.' . $setting) . ': ' . L::translate('mainform.shaders.option.' . $setting . '.' . $settings[$setting]);
                break;
                case 'normalMapEnabled':
                case 'specularMapEnabled':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['false', 'true']))
                        $settings[$setting] = 'true';
                    app()->getForm(MainForm)->{$setting}->text = L::translate('mainform.shaders.option.' . $setting) . ': ' . L::translate('command.' . $settings[$setting]);
                break;
                case 'renderResMul':
                case 'shadowResMul':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['0.5', '1.0', '2.0']))
                        $settings[$setting] = '1.0';
                    app()->getForm(MainForm)->{$setting}->text = L::translate('mainform.shaders.option.' . $setting) . ': ' . $settings[$setting] . 'x';
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
                    app()->getForm(MainForm)->{$setting}->text = L::translate('mainform.shaders.option.' . $setting) . ': ' . $value;
                break;
                case 'oldHandLight':
                case 'oldLighting':
                    if ($put && !key_exists($setting, $settings) || !in_array($settings[$setting], ['default', 'false', 'true']))
                        $settings[$setting] = 'default';
                    app()->getForm(MainForm)->{$setting}->text = L::translate('mainform.shaders.option.' . $setting) . ': ' . L::translate('command.' . $settings[$setting]);
                break;
            }
        }
        if ($put) app()->getForm(MainForm)->iniShader->put($settings);
    }
    
    /**
     * Присвоить действия кнопкам настроек шейдеров.
     */
    private static function createActionBtn ()
    {
        foreach (self::$LIST_SETTINGS as $key => $setting) {
            if ($key > 0) {
                app()->getForm(MainForm)->{$setting}->tooltip->style = '-fx-text-alignment: LEFT;';
                app()->getForm(MainForm)->{$setting}->on('action', function () use ($setting) {
                    self::changeSetting($setting);
                });
            }
        }
    }
    
    /**
     * Получить настройки видеокарты.
     */
    static function getVideoCard ()
    {
        if ($videoCard = Windows::getVideo()[0])
            $infoVideo = $videoCard['VideoProcessor'] . ' ' .
                         $videoCard['AdapterRAM'][0] . 'GB' . ' (' .
                         $videoCard['CurrentHorizontalResolution'] . 'x' .
                         $videoCard['CurrentVerticalResolution'] . ')';
        return empty($infoVideo) ? false : $infoVideo;
    }
    
    /**
     * Очистить значения класса.
     * 
     * @param string $value
     */
    static function clearValue (string $value)
    {
        self::{$value} = ($value == 'objectsInfo') ? ['list' => [['name' => 'OFF'], ['name' => '(internal)']]] : false;
    }
    
    /**
     * Получить список объектов класса.
     * 
     * @return array
     */
    static function getObjects () : array
    {
        return self::$objectsInfo;
    }
    
}