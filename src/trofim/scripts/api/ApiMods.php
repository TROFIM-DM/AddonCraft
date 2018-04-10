<?php
namespace trofim\scripts\api;

use std, framework, trofim;
use Exception;
use php\compress\ZipFile;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с API модов.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class ApiMods 
{
    
    /**
     * Список информации о модах.
     * 
     * @var array
     */
    private static $objectsInfo = [];
    
    /**
     * Поиск модов.
     */
    static function find ()
    {
        // Проверка на существование папки mods
        if (fs::exists(Path::getPathMinecraft() . '\\mods\\')) {
            
            uiLater(function () {
                app()->getForm(StartForm)->setStatus(L::translate('word.mods') . '...');
            });
        
            // Поиск файлов mods
            $files = new File(Path::getPathMinecraft() . '\\mods\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getPath()) == 'jar')
                    $objects[] = ['path' => $file->getPath(), 'mode' => 'enabled'];
            }
            
            // Поиск файлов disabled_mods
            $disabledFiles = new File(Path::getAppPath() . '\\disabled\\mods\\');
            foreach ($disabledFiles->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getPath()) == 'jar')
                    $objects[] = ['path' => $file->getPath(), 'mode' => 'disabled'];
            }
            
            // Если mods нет
            if (empty($objects)) return;
            
            foreach ($objects as $object) {
                
                try {
                       
                    // Создание ZIP
                    $zipFile = new ZipFile($object['path']);
                    
                    // Проверка на наличие файла mcmod.info
                    if ($zipFile->has('mcmod.info')) {
                    
                        // Путь к Temp для mod'а
                        $pathTemp = Path::getAppTemp() . '\\' . fs::nameNoExt($object['path']) . '\\';
                        
                        // Очистка информации
                        //$objectInfo = false;
                        
                        // Разархивирование mcmod.info
                        $zipFile->read('mcmod.info', function ($entry, MiscStream $stream) use ($pathTemp) {
                            fs::makeDir($pathTemp);
                            fs::copy($stream, $pathTemp . 'mcmod.info'); 
                        });
                        
                        // Получение содержимого mcmod.info
                        if (fs::exists($pathTemp . 'mcmod.info'))
                            $objectInfo['info'] = Json::decode(Stream::getContents($pathTemp . 'mcmod.info'))[0];
                        else return;
                        
                        // Если файл mcmod.info успешно прочитан
                        if (isset($objectInfo['info'])) {
                            
                            // Замена modlist
                            if ($objectInfo['info']['modList']) $objectInfo['info'] = $objectInfo['info']['modList'];
                            
                            // Добавление путей и режима mod'a
                            $objectInfo = array_merge($objectInfo, ['mode' => $object['mode'],
                                                                    'path' => ['mod' => $object['path'],
                                                                               'temp' => $pathTemp]]);
                            
                            // Проверка, есть ли logo у mod'a
                            if ($objectInfo['info']['logoFile'] && $zipFile->has($objectInfo['info']['logoFile'])) {
                                
                                // Разархивирование logo mod'a
                                $zipFile->read($objectInfo['info']['logoFile'], function ($entry, MiscStream $stream) use (&$objectInfo) {
                                    $pathLogo = $objectInfo['path']['temp'] . 'logo.png';
                                    if (fs::copy($stream, $pathLogo))
                                        $objectInfo['path']['logo'] = $pathLogo;
                                });
                            }

                            // Создание файла с hash-суммой
                            Stream::putContents($pathTemp . 'hash', sha1_file($object['path']));
                            
                            // Добавление mod'а в список
                            self::$objectsInfo[] = $objectInfo;
                            
                            // Создание item mod
                            DesignMods::addItem($objectInfo);
                            
                            // Регистрация файлов для запрета на изменение
                            FileSystem::registerFile($objectInfo['path']['mod']);
                            FileSystem::registerFile($pathTemp . 'mcmod.info');
                            FileSystem::registerFile($pathTemp . 'hash');
                            if (isset($objectInfo['path']['logo'])) FileSystem::registerFile($objectInfo['path']['logo']);
                        }
                    }
                    unset($zipFile);
                    
                } catch (Exception $error) {
                    
                }
                
            }
        }
    }

    /**
     * Добавить мод.
     * 
     * @param File $object
     */
    static function add (File $object)
    {
        // Проверки
        if (fs::isFile($object->getPath()) && fs::ext($object->getPath()) == 'jar') {
        
            // Поиск файлов в папке mods
            $files = new File(Path::getPathMinecraft() . '\\mods\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'jar' &&
                    $file->getName() == $object->getName()) {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.exist'));
                    return;
                }
            }
                
            // Поиск файлов в папке disabled 
            $disabledFiles = new File(Path::getAppPath() . '\\disabled\\mods\\');
            foreach ($disabledFiles->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'jar' &&
                    $file->getName() == $object->getName()) {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.exist'));
                    return;
                }
            }
        
            try {
                
                // Создание ZIP
                $zipFile = new ZipFile($object->getPath());
                
                // Проверка на наличие файла mcmod.info
                if ($zipFile->has('mcmod.info')) {
                
                    // Путь к Temp для mod'а
                    $pathTemp = Path::getAppTemp() . '\\' . fs::nameNoExt($object->getName()) . '\\';
                    
                    // Разархивирование mcmod.info
                    $zipFile->read('mcmod.info', function ($entry, MiscStream $stream) use ($pathTemp) {
                        fs::makeDir($pathTemp);
                        fs::copy($stream, $pathTemp . 'mcmod.info'); 
                    });
                    
                    // Получение содержимого mcmod.info
                    if (fs::exists($pathTemp . 'mcmod.info'))
                        $objectInfo['info'] = Json::decode(Stream::getContents($pathTemp . 'mcmod.info'))[0];
                    else {
                        app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.not.read'));
                        return;
                    }
                    
                    // Если файл mcmod.info успешно прочитан
                    if (isset($objectInfo['info'])) {
                    
                        // Путь добавленного mod'a
                        $pathMod = Path::getPathMinecraft() . '\\mods\\' . $object->getName();
                        
                        // Создание папки mods, если нет
                        if (!fs::exists(Path::getPathMinecraft() . '\\mods\\'))
                            fs::makeDir(Path::getPathMinecraft() . '\\mods\\');
                        
                        // Копирование mod'a
                        if (!fs::copy($object->getPath(), $pathMod)) {
                            app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.not.setup'));
                            return;
                        }
                        
                        // Замена modInfo
                        if (isset($objectInfo['info']['modList'])) $objectInfo['info'] = $objectInfo['info']['modList'];
                        
                        // Добавление путей и режима до mod'a
                        $objectInfo = array_merge($objectInfo, ['mode' => 'enabled',
                                                                'path' => ['mod' => $pathMod,
                                                                           'temp' => $pathTemp]]);
                        
                        // Проверка, есть ли logo у mod'a
                        if ($objectInfo['info']['logoFile'] && $zipFile->has($objectInfo['info']['logoFile'])) {
                            
                            // Разархивирование logo mod'a
                            $zipFile->read($objectInfo['info']['logoFile'], function ($entry, MiscStream $stream) use (&$objectInfo) {
                                $pathLogo = $objectInfo['path']['temp'] . 'logo.png';
                                if (fs::copy($stream, $pathLogo))
                                    $objectInfo['path']['logo'] = $pathLogo;
                            });
                        }
                        
                        // Создание файла с hash-суммой
                        Stream::putContents($pathTemp . 'hash', sha1_file($object->getPath()));
                        
                        // Добавление mod'а в список
                        self::$objectsInfo[] = $objectInfo;
                    
                        // Создание item mod
                        DesignMods::addItem($objectInfo);
                        
                        // Регистрация файлов для запрета на изменение
                        FileSystem::registerFile($objectInfo['path']['mod']);
                        FileSystem::registerFile($pathTemp . 'mcmod.info');
                        if (isset($objectInfo['path']['logo'])) FileSystem::registerFile($objectInfo['path']['logo']);
                        
                        // Сообщение о успешном добавлении мода
                        app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.added'));
                    }
                } else {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.incorrect'));
                }
                unset($zipFile);
                
            } catch (Exception $error) {
                app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.unknown.error'));
                return;
            }
            
        } else {
            app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.select.file'));
        }
    }
    
    /**
     * Изменить режим для мода.
     * 
     * @param int $index
     */
    static function setMode (int $index)
    {
        // Если mod включен
        if (self::$objectsInfo[$index]['mode'] == 'enabled') {
        
            // Путь отключенного mod'a
            $pathMod = Path::getAppPath() . '\\disabled\\mods\\' . fs::name(self::$objectsInfo[$index]['path']['mod']);
            
            // Создаем папки
            fs::makeDir(Path::getAppPath() . '\\disabled\\');
            fs::makeDir(Path::getAppPath() . '\\disabled\\mods\\');

            // Разрегистрация mod'a && перемещение mod'a
            if (FileSystem::unRegisterFile(self::$objectsInfo[$index]['path']['mod']) && fs::move(self::$objectsInfo[$index]['path']['mod'], $pathMod)) {
            
                // Изменение информации о mod
                self::$objectsInfo[$index]['path']['mod'] = $pathMod;
                self::$objectsInfo[$index]['mode'] = 'disabled';
                
                // Действия
                app()->getForm(MainForm)->boxMods->items[$index]->children[1]->children[0]->style = '-fx-text-fill: red;';
                app()->getForm(MainForm)->setModeMod(false);
                app()->getForm(MainForm)->infoMod_name->style = '-fx-text-fill: red;';
                
                // Регистрация mod'a
                FileSystem::registerFile(self::$objectsInfo[$index]['path']['mod']);
            } else app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.not.disabled'));
        }
        
        // Если mod отключен
        else if (self::$objectsInfo[$index]['mode'] == 'disabled') {
        
            // Путь включенного mod'a
            $pathMod = Path::getPathMinecraft() . '\\mods\\' . fs::name(self::$objectsInfo[$index]['path']['mod']);
            
            // Разрегистрация mod'a && перемещение mod'a
            if (FileSystem::unRegisterFile(self::$objectsInfo[$index]['path']['mod']) && fs::move(self::$objectsInfo[$index]['path']['mod'], $pathMod)) {
            
                // Изменение информации о mod
                self::$objectsInfo[$index]['path']['mod'] = $pathMod;
                self::$objectsInfo[$index]['mode'] = 'enabled';
                
                // Действия
                app()->getForm(MainForm)->boxMods->items[$index]->children[1]->children[0]->style = '-fx-text-fill: white;';
                app()->getForm(MainForm)->setModeMod(true);
                app()->getForm(MainForm)->infoMod_name->style = '-fx-text-fill: white;';
                
                // Регистрация mod'a
                FileSystem::registerFile(self::$objectsInfo[$index]['path']['mod']);
            } else app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.not.enabled'));
        }
    }
    
    /**
     * Удалить мод.
     * 
     * @param int $index
     */
    static function delete (int $index)
    {
        // Разрегистрация mod'a && удаление mod'a
        if (FileSystem::unRegisterFile(self::$objectsInfo[$index]['path']['mod']) && fs::delete(self::$objectsInfo[$index]['path']['mod'])) {
        
            // Удаление mod'a из списка
            unset(self::$objectsInfo[$index]);
            
            // Сортировка
            sort(self::$objectsInfo, SORT_NUMERIC);
            
            // Действия
            app()->getForm(MainForm)->boxInfoMod->items->clear();
            app()->getForm(MainForm)->boxMods->items->removeByIndex($index);
            
            // Успех!
            app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.delete.success'));
        } app()->getForm(MainForm)->toast(L::translate('mainform.toast.mods.delete.not.success'));
    }
    
    /**
     * Очистить значения класса.
     * 
     * @param string $value
     */
    static function clearValue (string $value)
    {
        self::{$value} = false;
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