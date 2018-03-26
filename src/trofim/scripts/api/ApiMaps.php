<?php
namespace trofim\scripts\api;

use std, trofim, gui;
use Exception;

/**
 * Класс для работы с API карт.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class ApiMaps
{
    
    private static $mapValue = ['Name', 'LevelName', 'GameType', 'LastPlayed', 'allowCommands', 'hardcore'];
    
    /**
     * Поиск карт.
     */
    public static function findMaps ()
    {
        
        // Проверка на существование папки saves
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\saves\\')) {
        
            uiLater(function () {
                app()->form(StartForm)->setStatus(Language::translate('word.maps') . '...');
            });
        
            // Поиск файлов saves
            $fileMaps = new File(AddonCraft::getPathMinecraft() . '\\saves\\');
            foreach ($fileMaps->findFiles() as $file) {
                if (fs::isDir($file->getPath()))
                    $maps[] = ['path' => $file->getPath()];
            }
            
            // Если saves нет
            if (empty($maps)) return;
            
            foreach ($maps as $map) {
                
                try {
                    
                    // Проверка, карта это или нет
                    if (fs::exists($map['path'] . '\\level.dat') && fs::exists($map['path'] . '\\session.lock') && fs::exists($map['path'] . '\\region\\')) {
                        
                        $mapInfo = false;
                        
                        // Получение данных NBT
                        $NBT = new NBT($map['path'] . '\\level.dat');
                        $listInfo = $NBT->getList();
                        unset($NBT);
                        foreach (self::$mapValue as $value)
                            if ($listInfo[$value]) $mapInfo['info'][$value] = $listInfo[$value];
                        
                        // Если все данные вернулись
                        if ($mapInfo['info']['LevelName'] && $mapInfo['info']['LastPlayed']) {
                            
                            // Путь к save
                            $mapInfo['path']['map'] = $map['path'];
                            
                            // Проверка, есть ли icon у save
                            if (fs::exists($map['path'] . '\\icon.png'))
                                $mapInfo['path']['icon'] = $map['path'] . '\\icon.png';
                            
                            // Добавление save в список
                            AddonCraft::$listMaps[] = $mapInfo;
                            
                            // Создание item save
                            DesignMaps::addItem($mapInfo);
                        }
                        
                    }
                    
                } catch (Exception $error) {
                    
                }
                
            }
            
        }
        
    }
    
    /**
     * Добавление карт.
     * 
     * @param $MAP
     */
    public static function addMap ($MAP)
    {
        
        if (fs::isDir($MAP->getPath())) {
        
            // Поиск файлов saves
            $fileMaps = new File(AddonCraft::getPathMinecraft() . '\\saves\\');
            foreach ($fileMaps->findFiles() as $file) {
                if (fs::isDir($file->getPath()) && $file->getName() == $MAP->getName()) {
                    app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.exist'));
                    return;
                }
            }
                
            try {
                
                // Проверка, карта это или нет
                if (fs::exists($MAP->getPath() . '\\level.dat') && fs::exists($MAP->getPath() . '\\session.lock') && fs::exists($MAP->getPath() . '\\region\\')) {
                    
                    // Создание NBT
                    $NBT = new NBT($MAP->getPath() . '\\level.dat');
                    $listInfo = $NBT->getList();
                    foreach (self::$mapValue as $value)
                        if ($listInfo[$value]) $mapInfo['info'][$value] = $listInfo[$value];
                    
                    // Если все данные вернулись
                    if ($mapInfo['info']['LevelName'] && $mapInfo['info']['LastPlayed']) {
                        
                        // Путь к save
                        $mapInfo['path']['map'] = $MAP->getPath() . '\\';
                        
                        // Создание папки saves, если нет
                        if (!fs::exists(AddonCraft::getPathMinecraft() . '\\saves\\'))
                            fs::makeDir(AddonCraft::getPathMinecraft() . '\\saves\\');
                        
                        // Копирование save
                        if (!fs::makeDir($mapInfo['path']['map']) && !Dir::copy($MAP->getPath(), $mapInfo['path']['map'])) {
                            app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.not.setup'));
                            return;
                        }
                        
                        // Проверка, есть ли icon у save
                        if (fs::exists($mapInfo['path']['map'] . '\\icon.png'))
                            $mapInfo['path']['icon'] = $mapInfo['path']['map'] . '\\icon.png';
                        
                        // Добавление save в список
                        AddonCraft::$listMaps[] = $mapInfo;
                        
                        // Создание item save
                        DesignMaps::addItem($mapInfo);
                        
                        // Сообщение о успешном добавлении карты
                        app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.added'));
                    } else {
                        app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.not.read'));
                    }
                    
                } else {
                    app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.incorrect'));
                }
                
            } catch (Exception $error) {
                app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.unknown.error'));
                return;
            }
            
        } else {
            app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.select.file'));
        }
        
    }
    
    public static function saveMap (array $mapInfo)
    {
        $values = ['LevelName', 'GameType', 'allowCommands', 'hardcore'];
        foreach ($mapInfo['info'] as $key => $value) {
            if ($key == 'LevelName' || $key == 'GameType' || $key == 'allowCommands' || $key == 'hardcore')
                uiLaterAndWait(function () use ($mapInfo, $key, $value) {
                    NBT::setValue($mapInfo['path']['map'] . '\\level.dat\\Data\\' . $key, '"' . $value . '"');
                });
        }
    }
    
    /**
     * Удаление карты.
     * 
     * @param $index
     */
    public static function deleteMap ($index)
    {
    
        // Удаление save
        if (Dir::delete(AddonCraft::$listMaps[$index]['path']['map'])) {
        
            // Удаление save из списка
            unset(AddonCraft::$listMaps[$index]);
            
            // Сортировка
            sort(AddonCraft::$listMaps, SORT_NUMERIC);
            
            // Действия
            app()->form(MainForm)->boxMaps->items->removeByIndex($index);
            
            // Успех!
            app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.delete.success'));
        } else app()->form(MainForm)->toast(Language::translate('mainform.toast.maps.delete.not.success'));
        
    }
    
    /**
     * Удаление icon.
     * 
     * @param $index
     */
    public static function deleteIcon ($index) : bool
    {
        
        // Проверка
        if (fs::exists(AddonCraft::$listMaps[$index]['path']['icon'])) {
        
            // Удаление icon файл
            if (fs::delete(AddonCraft::$listMaps[$index]['path']['icon'])) {
            
                // Удаление icon из списка
                unset(AddonCraft::$listMaps[$index]['path']['icon']);
                
                //Действия
                app()->form(MainForm)->{'imageMaps'.++$index}->image = new UXImage('res://.data/img/map_icon.png');
                
                // Успех!
                app()->form(MainForm)->toast(Language::translate('editmapform.toast.delete.success'));
                return true;
            }
        } else app()->form(MainForm)->toast(Language::translate('editmapform.toast.delete.not.success'));
        return false;
    }
    
}