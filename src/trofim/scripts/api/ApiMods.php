<?php
namespace trofim\scripts\api;

use framework;
use Exception;
use trofim;
use facade\Json;
use php\compress\ZipFile;
use std;

/**
 * Класс для работы с API модов.
 */
class ApiMods 
{

    /**
     * Поиск модов.
     */
    public static function findMods () {
    
        /*Проверка на существование папки mods*/
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\mods\\')) {
        
            /*Поиск файлов mods*/
            $fileMods = new File(AddonCraft::getPathMinecraft() . '\\mods\\');
            foreach ($fileMods->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getPath()) == 'jar')
                    $mods[] = ['path' => $file->getPath(), 'mode' => 'enabled', 'hash' => sha1_file($file->getPath())];
            }
            
            /*Поиск файлов disabled_mods*/
            $disabledMods = new File(AddonCraft::getAppPath() . '\\disabled\\mods\\');
            foreach ($disabledMods->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getPath()) == 'jar')
                    $mods[] = ['path' => $file->getPath(), 'mode' => 'disabled', 'hash' => sha1_file($file->getPath())];
            }
            
            if (empty($mods)) return false;
            
            foreach ($mods as $mod) {
                
                try {
                       
                    /*Создание ZIP*/
                    $zipFile = new ZipFile($mod['path']);
                    
                    /*Проверка на наличие файла mcmod.info*/
                    if ($zipFile->has('mcmod.info')) {
                    
                        /*Путь к Temp для mod'а'*/
                        $pathTemp = AddonCraft::getAppTemp() . '\\' . fs::nameNoExt($mod['path']) . '\\';
                        
                        /*Разархивирование mcmod.info*/
                        $zipFile->read('mcmod.info', function ($entry, MiscStream $stream) use ($pathTemp) {
                            fs::makeDir($pathTemp);
                            fs::copy($stream, $pathTemp . 'mcmod.info'); 
                        });
                        
                        /*Получение содержимого mcmod.info*/
                        if (fs::exists($pathTemp . 'mcmod.info'))
                            $modInfo = Json::decode(Stream::getContents($pathTemp . 'mcmod.info'));
                        else return false;
                        
                        /*Если файл mcmod.info успешно прочитан*/
                        if (isset($modInfo)) {
                            
                            /*Замена modInfo*/
                            if ($modInfo['modList']) $modInfo = $modInfo['modList'];
                            
                            /*Добавление режима mod'a*/
                            $modInfo[0]['mode'] = $mod['mode'];
                            
                            /*Добавление путей до mod'a'*/
                            $modInfo[0]['path']['mod'] = $mod['path'];
                            $modInfo[0]['path']['temp'] = $pathTemp;
                            
                            /*Проверка, есть ли logo у mod'a*/
                            if ($modInfo[0]['logoFile'] && $zipFile->has($modInfo[0]['logoFile'])) {
                                
                                /*Разархивирование logo mod'a*/
                                $zipFile->read($modInfo[0]['logoFile'], function ($entry, MiscStream $stream) use (&$modInfo) {
                                    $pathLogo = $modInfo[0]['path']['temp'] . 'logo.png';
                                    if (fs::copy($stream, $pathLogo))
                                        $modInfo[0]['path']['logo'] = $pathLogo;
                                });
                            }
                            
                            /*Создание файла с hash-суммой*/
                            Stream::putContents($pathTemp . 'hash', $mod['hash']);
                            
                            /*Добавление mod'а в список*/
                            AddonCraft::$listMods[] = $modInfo[0];
                            
                            /*Создание item mod*/
                            DesignMods::addItemMod($modInfo[0]);
                            
                            /*Регистрация файлов для запрета на изменение*/
                            AddonCraft::registerFile($modInfo[0]['path']['mod']);
                            AddonCraft::registerFile($pathTemp . 'mcmod.info');
                            AddonCraft::registerFile($pathTemp . 'hash');
                            if (isset($modInfo[0]['path']['logo'])) AddonCraft::registerFile($modInfo[0]['path']['logo']);
                        }
                    }
                    
                } catch (Exception $error) {
                    
                }
                
            }
        }
        
    }

    /**
     * Добавление мода.
     * 
     * @param $MOD
     */
    public static function addMod ($MOD) {
        
        /*Проверки*/
        if (fs::isFile($MOD->getPath()) && fs::ext($MOD->getPath()) == 'jar') {
        
            /*Поиск файлов в папке mods*/
            $filesMods = new File(AddonCraft::getPathMinecraft() . '\\mods\\');
            foreach ($filesMods->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'jar' &&
                    fs::name($file->getPath()) == fs::name($MOD->getPath())) {
                    app()->form(MainForm)->toast('Такой мод уже добавлен!');
                    return false;
                }
            }
                
            /*Поиск файлов в папке disabled*/
            $disabledMods = new File(AddonCraft::getAppPath() . '\\disabled\\mods\\');
            foreach ($disabledMods->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'jar' &&
                    fs::name($file->getPath()) == fs::name($MOD->getPath())) {
                    app()->form(MainForm)->toast('Такой мод уже добавлен!');
                    return false;
                }
            }
        
            try {
                
                /*Создание ZIP*/
                $zipFile = new ZipFile($MOD->getPath());
                
                /*Проверка на наличие файла mcmod.info*/
                if ($zipFile->has('mcmod.info')) {
                
                    /*Путь к Temp для mod'а*/
                    $pathTemp = AddonCraft::getAppTemp() . '\\' . fs::nameNoExt($MOD->getName()) . '\\';
                    
                    /*Разархивирование mcmod.info*/
                    $zipFile->read('mcmod.info', function ($entry, MiscStream $stream) use ($pathTemp) {
                        fs::makeDir($pathTemp);
                        fs::copy($stream, $pathTemp . 'mcmod.info'); 
                    });
                    
                    /*Получение содержимого mcmod.info*/
                    if (fs::exists($pathTemp . 'mcmod.info'))
                        $modInfo = Json::decode(Stream::getContents($pathTemp . 'mcmod.info'));
                    else {
                        app()->form(MainForm)->toast('Не удалось считать' . "\n" . 'информацию о моде!');
                        return false;
                    }
                    
                    /*Если файл mcmod.info успешно прочитан*/
                    if (isset($modInfo)) {
                    
                        /*Путь добавленного mod'a*/
                        $pathMod = AddonCraft::getPathMinecraft() . '\\mods\\' . $MOD->getName();
                        
                        /*Создание папки mods, если нет*/
                        if (!fs::exists(AddonCraft::getPathMinecraft() . '\\mods\\'))
                            mkdir(AddonCraft::getPathMinecraft() . '\\mods\\');
                        
                        /*Копирование mod'a*/
                        if (!fs::copy($MOD->getPath(), $pathMod)) {
                            app()->form(MainForm)->toast('Не удалось установить мод!');
                            return false;
                        }
                        
                        /*Замена modInfo*/
                        if (isset($modInfo['modList'])) $modInfo = $modInfo['modList'];
                        
                        /*Добавление путей до mod'a'*/
                        $modInfo[0]['path']['mod'] = $pathMod;
                        $modInfo[0]['path']['temp'] = $pathTemp;
                        
                        /*Добавление режима mod'a*/
                        $modInfo[0]['mode'] = 'enabled';
                        
                        /*Проверка, есть ли logo у mod'a*/
                        if ($modInfo[0]['logoFile'] && $zipFile->has($modInfo[0]['logoFile'])) {
                            
                            /*Разархивирование logo mod'a*/
                            $zipFile->read($modInfo[0]['logoFile'], function ($entry, MiscStream $stream) use (&$modInfo) {
                                $pathLogo = $modInfo[0]['path']['temp'] . 'logo.png';
                                if (fs::copy($stream, $pathLogo))
                                    $modInfo[0]['path']['logo'] = $pathLogo;
                            });
                        }
                        
                        /*Создание файла с hash-суммой*/
                        Stream::putContents($pathTemp . 'hash', sha1_file($MOD->getPath()));
                        
                        /*Добавление mod'а в список*/
                        AddonCraft::$listMods[] = $modInfo[0];
                    
                        /*Создание item mod*/
                        DesignMods::addItemMod($modInfo[0]);
                        
                        /*Регистрация файлов для запрета на изменение*/
                        AddonCraft::registerFile($modInfo[0]['path']['mod']);
                        AddonCraft::registerFile($pathTemp . 'mcmod.info');
                        if (isset($modInfo[0]['path']['logo'])) AddonCraft::registerFile($modInfo[0]['path']['logo']);
                        
                        /*Сообщение о успешном добавлении мода*/
                        app()->form(MainForm)->toast('Мод успешно добавлен!');
                    }
                } else {
                    app()->form(MainForm)->toast('[Мод] Выбран неверный файл!');
                }
                
            } catch (Exception $error) {
                app()->form(MainForm)->toast('Не удалось установить мод!' . "\n" . 'Произошла неизвестная ошибка...');
                return false;
            }
            
        } else {
            app()->form(MainForm)->toast('Выберите файл - мод!');
        }
        
    }
    
    /**
     * Изменение режима для мода.
     * 
     * @param $index
     */
    public static function setMode ($index) {
    
        /*Если mod включен*/
        if (AddonCraft::$listMods[$index]['mode'] == 'enabled') {
        
            /*Путь отключенного mod'a*/
            $pathMod = AddonCraft::getAppPath() . '\\disabled\\mods\\' . fs::name(AddonCraft::$listMods[$index]['path']['mod']);
            
            /*Создаем папки*/
            fs::makeDir(AddonCraft::getAppPath() . '\\disabled\\');
            fs::makeDir(AddonCraft::getAppPath() . '\\disabled\\mods\\');

            /*Разрегистрация mod'a && перемещение mod'a*/
            if (AddonCraft::unRegisterFile(AddonCraft::$listMods[$index]['path']['mod']) && fs::move(AddonCraft::$listMods[$index]['path']['mod'], $pathMod)) {
            
                /*Изменение информации о mod*/
                AddonCraft::$listMods[$index]['path']['mod'] = $pathMod;
                AddonCraft::$listMods[$index]['mode'] = 'disabled';
                
                /*Действия*/
                app()->form(MainForm)->boxMods->items[$index]->children[1]->children[0]->style = '-fx-text-fill: red;';
                app()->form(MainForm)->setModeMod(false);
                app()->form(MainForm)->infoMod_name->style = '-fx-text-fill: red;';
                
                /*Регистрация mod'a*/
                AddonCraft::registerFile(AddonCraft::$listMods[$index]['path']['mod']);
            } else app()->form(MainForm)->toast('Не удалось отключить мод!');
        }
        
        /*Если mod отключен*/
        else if (AddonCraft::$listMods[$index]['mode'] == 'disabled') {
        
            /*Путь включенного mod'a*/
            $pathMod = AddonCraft::getPathMinecraft() . '\\mods\\' . fs::name(AddonCraft::$listMods[$index]['path']['mod']);
            
            /*Разрегистрация mod'a && перемещение mod'a*/
            if (AddonCraft::unRegisterFile(AddonCraft::$listMods[$index]['path']['mod']) && fs::move(AddonCraft::$listMods[$index]['path']['mod'], $pathMod)) {
            
                /*Изменение информации о mod*/
                AddonCraft::$listMods[$index]['path']['mod'] = $pathMod;
                AddonCraft::$listMods[$index]['mode'] = 'enabled';
                
                /*Действия*/
                app()->form(MainForm)->boxMods->items[$index]->children[1]->children[0]->style = '-fx-text-fill: white;';
                app()->form(MainForm)->setModeMod(true);
                app()->form(MainForm)->infoMod_name->style = '-fx-text-fill: white;';
                
                /*Регистрация mod'a*/
                AddonCraft::registerFile(AddonCraft::$listMods[$index]['path']['mod']);
            } else app()->form(MainForm)->toast('Не удалось подключить мод!');
        }
    }
    
    /**
     * Удаление мода.
     * 
     * @param $index
     */
    public static function deleteMod ($index) {
    
        /*Разрегистрация mod'a && удаление mod'a*/
        if (AddonCraft::unRegisterFile(AddonCraft::$listMods[$index]['path']['mod']) && fs::delete(AddonCraft::$listMods[$index]['path']['mod'])) {
        
            /*Удаление mod'a из списка*/
            unset(AddonCraft::$listMods[$index]);
            
            /*Сортировка*/
            sort(AddonCraft::$listMods, SORT_NUMERIC);
            
            /*Действия*/
            Animation::fadeOut(app()->form(MainForm)->panelInfoMod, 25);
            app()->form(MainForm)->boxInfoMod->items->clear();
            app()->form(MainForm)->boxMods->items->removeByIndex($index);
            
            /*Успех!*/
            app()->form(MainForm)->toast('Мод успешно удален!');
        } else app()->form(MainForm)->toast('Не удалось удалить мод!');
    }

}