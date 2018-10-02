<?php
namespace trofim\scripts\api;

use std, trofim, gui, framework;
use Exception;
use php\compress\ZipFile;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с API версий.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class ApiVersions 
{
    
    /**
     * Список информации о версиях.
     * 
     * @var array
     */
    private static $objectsInfo = [];
    
    /**
     * Поиск версий.
     */
    static function find ()
    {
        // Проверка на существование папки versions
        if (fs::exists(Path::getPathMinecraft() . '\\versions\\')) {
        
            uiLater(function () {
                app()->getForm(StartForm)->setStatus(L::translate('word.versions') . '...');
            });
        
            // Поиск файлов versions
            $files = new File(Path::getPathMinecraft() . '\\versions\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isDir($file->getPath()))
                    $objects[] = ['path' => $file->getPath()];
            }
            
            // Если versions нет
            if (empty($objects)) return;
            
            foreach ($objects as $object) {
                
                try {
                    
                    // Путь до файлов
                    $pathToFile = $object['path'] . '\\' . fs::name($object['path']);
                    $pathTemp = Path::getAppTemp() . fs::name($object['path']);

                    // Проверка, version это или нет
                    if (fs::exists($pathToFile . '.json') &&
                        fs::exists($pathToFile . '.jar') &&
                        fs::exists($object['path'] . '\\natives')) {
                        
                        // Получение информации о version
                        $objectJSON = Json::decode(Stream::getContents($pathToFile . '.json'));
                        
                        // Если файл *.json успешно прочитан
                        if (isset($objectJSON)) {
                            
                            // Внесение важной информации
                            $objectInfo = ['info' => ['id' => $objectJSON['id'], 'allTime' => ['time' => $objectJSON['time'], 'releaseTime' => $objectJSON['releaseTime']]],
                                           'path' => ['folder' => $object['path'], 'file' => $pathToFile, 'temp' => $pathTemp]];
                            if ($objectJSON['jar'])
                                $objectInfo['info']['jar'] = $objectJSON['jar'];
                            
                            // Добавление version в список
                            self::$objectsInfo[] = $objectInfo;
                            
                            // Создание item version
                            app()->getForm(MainForm)->boxVersions->items->add($objectInfo['info']['id']);
                        }
                    }
                    
                } catch (Exception $error) {
                    
                }
                
            }
        }
    }
    
    /**
     * Проверка версий на целостность.
     * 
     * @param int $index
     */
    static function exists (int $index)
    {
        $objectInfo = self::$objectsInfo[$index];
        
        (new Thread(function () use ($objectInfo) {
            if (!fs::exists($objectInfo['path']['temp']) ||
                (fs::time($objectInfo['path']['file'] . '.jar') > fs::time($objectInfo['path']['temp']))) {
                    uiLater(function () use (&$progress) {
                        $progress = new Progress(app()->getForm(MainForm)->panelVersions);
                        if (app()->getForm(MainForm)->panelInfoVersion->visible)
                            app()->getForm(MainForm)->panelInfoVersion->visible = false;
                    });
                    fs::makeDir($objectInfo['path']['temp']);
                    $zipFile = new ZipFile($objectInfo['path']['file'] . '.jar');
                    $zipFile->readAll(function (array $stat, MiscStream $stream) use ($objectInfo, &$progress, &$i) {
                        if (!$stat['directory'] && str::startsWith($stat['name'], "assets/")) {
                            uiLater(function () use (&$progress, &$i) {
                                $progress->setText(L::translate('mainform.versions.progress.unzip', ++$i));
                            });
                            fs::ensureParent($objectInfo['path']['temp'] . '\\' . $stat['name']); 
                            fs::copy($stream, $objectInfo['path']['temp'] . '\\' . $stat['name']);
                        }
                    });
                    uiLater(function () use (&$progress) {
                        $progress->free();
                    });
            }
            uiLater(function () use ($objectInfo) {
                DesignVersions::showInfo($objectInfo);
            });
        }))->start();
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