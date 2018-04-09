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
    
    /**
     * Список информации о картах.
     * 
     * @var array
     */
    private static $objectsInfo = [];
    
    /**
     * Значение, которые необходимы для работы.
     * 
     * @var array
     */
    private static $mapValue    = ['Name', 'LevelName', 'GameType', 'LastPlayed', 'allowCommands', 'hardcore'];
    
    /**
     * Поиск карт.
     */
    static function find ()
    {
        // Проверка на существование папки saves
        if (fs::exists(Path::getPathMinecraft() . '\\saves\\')) {
        
            uiLater(function () {
                app()->getForm(StartForm)->setStatus(Language::translate('word.maps') . '...');
            });
        
            // Поиск файлов maps
            $files = new File(Path::getPathMinecraft() . '\\saves\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isDir($file->getPath()))
                    $objects[] = ['path' => $file->getPath()];
            }
            
            // Если maps нет
            if (empty($objects)) return;
            
            foreach ($objects as $object) {
                
                try {
                    
                    // Проверка, map это или нет
                    if (fs::exists($object['path'] . '\\level.dat') && fs::exists($object['path'] . '\\session.lock') && fs::exists($object['path'] . '\\region\\')) {
                        
                        $objectInfo = false;
                        
                        // Получение данных NBT
                        $NBT = new NBT($object['path'] . '\\level.dat');
                        $listInfo = $NBT->getList();
                        foreach (self::$mapValue as $value)
                            if ($listInfo[$value]) $objectInfo['info'][$value] = $listInfo[$value];
                        
                        // Если все нужные данные вернулись
                        if ($objectInfo['info']['LevelName'] && $objectInfo['info']['LastPlayed']) {
                            
                            // Путь к map
                            $objectInfo['path']['map'] = $object['path'];
                            
                            // Проверка, есть ли icon у map
                            if (fs::exists($object['path'] . '\\icon.png'))
                                $objectInfo['path']['icon'] = $object['path'] . '\\icon.png';
                            
                            // Добавление map в список
                            self::$objectsInfo[] = $objectInfo;
                            
                            // Создание item map
                            DesignMaps::addItem($objectInfo);
                        }
                        
                    }
                    
                } catch (Exception $error) {
                    
                }
                
            }
            
        }
    }
    
    /**
     * Добавить карту.
     * 
     * @param File $object
     */
    static function add (File $object)
    {
        // Проверка
        if (fs::isDir($object->getPath())) {
        
            // Поиск файлов saves
            $files = new File(Path::getPathMinecraft() . '\\saves\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isDir($file->getPath()) && $file->getName() == $object->getName()) {
                    app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.exist'));
                    return;
                }
            }
                
            try {
                
                // Проверка, map это или нет
                if (fs::exists($object->getPath() . '\\level.dat') && fs::exists($object->getPath() . '\\session.lock') && fs::exists($object->getPath() . '\\region\\')) {
                    
                    // Создание NBT
                    $NBT = new NBT($object->getPath() . '\\level.dat');
                    $listInfo = $NBT->getList();
                    foreach (self::$mapValue as $value)
                        if ($listInfo[$value]) $objectInfo['info'][$value] = $listInfo[$value];
                    
                    // Если все нужные данные вернулись
                    if ($objectInfo['info']['LevelName'] && $objectInfo['info']['LastPlayed']) {
                        
                        // Путь к map
                        $objectInfo['path']['map'] = $object->getPath();
                        
                        // Создание папки saves, если нет
                        if (!fs::exists(Path::getPathMinecraft() . '\\saves\\'))
                            fs::makeDir(Path::getPathMinecraft() . '\\saves\\');
                        
                        // Копирование map
                        if (!fs::makeDir($objectInfo['path']['map']) && !Dir::copy($object->getPath(), $objectInfo['path']['map'])) {
                            app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.not.setup'));
                            return;
                        }
                        
                        // Проверка, есть ли icon у map
                        if (fs::exists($objectInfo['path']['map'] . '\\icon.png'))
                            $objectInfo['path']['icon'] = $objectInfo['path']['map'] . '\\icon.png';
                        
                        // Добавление map в список
                        self::$objectsInfo[] = $objectInfo;
                        
                        // Создание item map
                        DesignMaps::addItem($objectInfo);
                        
                        // Сообщение о успешном добавлении карты
                        app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.added'));
                    } else {
                        app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.not.read'));
                    }
                    
                } else {
                    app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.incorrect'));
                }
                
            } catch (Exception $error) {
                app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.unknown.error'));
                return;
            }
            
        } else {
            app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.select.file'));
        }
    }
    
    /**
     * Сохранить карту.
     * 
     * @param array $mapInfo
     */
    /*public static function saveMap (array $mapInfo)
    {
        $values = ['LevelName', 'GameType', 'allowCommands', 'hardcore'];
        foreach ($mapInfo['info'] as $key => $value) {
            if ($key == 'LevelName' || $key == 'GameType' || $key == 'allowCommands' || $key == 'hardcore')
                uiLaterAndWait(function () use ($mapInfo, $key, $value) {
                    NBT::setValue($mapInfo['path']['map'] . '\\level.dat\\Data\\' . $key, '"' . $value . '"');
                });
        }
    }*/
    
    /**
     * Удалить карту.
     * 
     * @param int $index
     */
    static function delete (int $index)
    {
        // Удаление map
        if (Dir::delete(self::$objectsInfo[$index]['path']['map'])) {
        
            // Удаление save из списка
            unset(self::$objectsInfo[$index]);
            
            // Сортировка
            sort(self::$objectsInfo, SORT_NUMERIC);
            
            // Действия
            app()->getForm(MainForm)->boxMaps->items->removeByIndex($index);
            
            // Успех!
            app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.delete.success'));
        } else app()->getForm(MainForm)->toast(Language::translate('mainform.toast.maps.delete.not.success'));
    }
    
    /**
     * Удалить icon.
     * 
     * @param int $index
     */
    /*public static function deleteIcon (int $index) : bool
    {
        // Проверка
        if (fs::exists(AddonCraft::$listMaps[$index]['path']['icon'])) {
        
            // Удаление icon файл
            if (fs::delete(AddonCraft::$listMaps[$index]['path']['icon'])) {
            
                // Удаление icon из списка
                unset(AddonCraft::$listMaps[$index]['path']['icon']);
                
                // Действия
                app()->getForm(MainForm)->{'imageMaps'.++$index}->image = new UXImage('res://.data/img/map_icon.png');
                
                // Успех!
                app()->getForm(MainForm)->toast(Language::translate('editmapform.toast.delete.success'));
                return true;
            }
        } else app()->getForm(MainForm)->toast(Language::translate('editmapform.toast.delete.not.success'));
        return false;
    }*/
    
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