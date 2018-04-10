<?php
namespace trofim\scripts\api;

use std, framework, trofim, gui;
use Exception;
use php\compress\ZipFile;
use trofim\scripts\lang\Language as L;

/**
 * Класс для работы с API текстур.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class ApiTextures 
{
    
    /**
     * Список информации о текстурах.
     * 
     * @var array
     */
    private static $objectsInfo = [];
    
    /**
     * Поиск текстур-паков.
     */
    static function find ()
    {
        // Проверка на существование папки resourcepacks
        if (fs::exists(Path::getPathMinecraft() . '\\resourcepacks\\')) {
            
            uiLater(function () {
                app()->getForm(StartForm)->setStatus(L::translate('word.textures') . '...');
            });
            
            // Поиск файлов textures
            $files = new File(Path::getPathMinecraft() . '\\resourcepacks\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getName()) == 'zip')
                    $objects[] = ['path' => $file->getPath()];
            }
            
            // Если textures нет
            if (empty($objects)) return;
            
            // Список подключенных textures
            $enabledTextures = self::getEnabledTextures();
            
            foreach ($objects as $object) {
            
                try {
                        
                    // Создание ZIP
                    $zipFile = new ZipFile($object['path']);
                    
                    // Проверка на наличие файла pack.mcmeta
                    if ($zipFile->has('pack.mcmeta')) {
                        
                        // Путь к Temp для texture
                        $pathTemp = Path::getAppTemp() . '\\' . fs::nameNoExt($object['path']) . '\\';
                        
                        // Разархивирование pack.mcmeta
                        $zipFile->read('pack.mcmeta', function ($entry, MiscStream $stream) use ($pathTemp) {
                            fs::makeDir($pathTemp);
                            fs::copy($stream, $pathTemp . 'pack.mcmeta'); 
                        });
                        
                        // Получение содержимого pack.mcmeta
                        if (fs::exists($pathTemp . 'pack.mcmeta')) {
                            $objectInfo = false;
                            $objectInfo['info'] = Json::decode(Stream::getContents($pathTemp . 'pack.mcmeta'))['pack'];
                            $objectInfo['info']['name'] = fs::nameNoExt($object['path']);
                        }
                        else return;

                        // Если файл pack.mcmeta успешно прочитан
                        if (isset($objectInfo['info']['pack_format'])) {
                            
                            // Добавление названия и путей для texture
                            $objectInfo = array_merge($objectInfo, ['path' => ['texture' => $object['path'],
                                                                               'temp' => $pathTemp]]);

                            // Проверка, есть ли logo у texture
                            if ($zipFile->has('pack.png')) {
                                
                                // Разархивирование logo texture
                                $zipFile->read('pack.png', function ($entry, MiscStream $stream) use (&$objectInfo) {
                                    $pathLogo = $objectInfo['path']['temp'] . 'logo.png';
                                    if (fs::copy($stream, $pathLogo))
                                        $objectInfo['path']['logo'] = $pathLogo;
                                });
                            }
                            
                            // Проверка на подключение texture
                            if ($enabledTextures && in_array(fs::name($object['path']), $enabledTextures))
                                $objectInfo['enabled'] = true;

                            // Создание файла с hash-суммой
                            Stream::putContents($pathTemp . 'hash', sha1_file($object['path']));
                            
                            // Добавление texture в список
                            self::$objectsInfo[fs::name($object['path'])] = $objectInfo;
                            
                            // Создание item texture
                            DesignTextures::addItem($objectInfo);
                            
                            // Регистрация файлов для запрета на изменение
                            FileSystem::registerFile($objectInfo['path']['texture']);
                            FileSystem::registerFile($pathTemp . 'pack.mcmeta');
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
     * Добавить текстур-пак.
     * 
     * @param File $object
     */
    static function add (File $object)
    {
        // Проверки
        if (fs::isFile($object->getPath()) && fs::ext($object->getPath()) == 'zip') {
        
            // Поиск файлов в папке resourcepacks
            $files = new File(Path::getPathMinecraft() . '\\resourcepacks\\');
            foreach ($files->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'zip' &&
                    $file->getName() == $object->getName()) {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.exist'));
                    return;
                }
            }
            
            try {
                
                // Создание ZIP
                $zipFile = new ZipFile($object->getPath());
                
                // Проверка на наличие файла pack.mcmeta
                if ($zipFile->has('pack.mcmeta')) {
                
                    // Путь к Temp для texture
                    $pathTemp = Path::getAppTemp() . '\\' . fs::nameNoExt($object->getName()) . '\\';
                    
                    // Разархивирование pack.mcmeta
                    $zipFile->read('pack.mcmeta', function ($entry, MiscStream $stream) use ($pathTemp) {
                        fs::makeDir($pathTemp);
                        fs::copy($stream, $pathTemp . 'pack.mcmeta'); 
                    });
                    
                    // Получение содержимого pack.mcmeta
                    if (fs::exists($pathTemp . 'pack.mcmeta')) {
                        $objectInfo['info'] = Json::decode(Stream::getContents($pathTemp . 'pack.mcmeta'))['pack'];
                        $objectInfo['info']['name'] = fs::nameNoExt($object->getName());
                    }
                    else {
                        app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.not.read'));
                        return;
                    }
                    
                    // Если файл pack.mcmeta успешно прочитан
                    if (isset($objectInfo['info']['pack_format'])) {
                        
                        // Путь добавленного texture
                        $pathTexture = Path::getPathMinecraft() . '\\resourcepacks\\' . $object->getName();
                        
                        // Создание папки texture, если нет
                        if (!fs::exists(Path::getPathMinecraft() . '\\resourcepacks\\'))
                            fs::makeDir(Path::getPathMinecraft() . '\\resourcepacks\\');
                        
                        // Добавление texture
                        if (!fs::copy($object->getPath(), $pathTexture)) {
                            app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.not.setup'));
                            return;
                        }
                        
                        // Добавление названия и путей для texture
                        $objectInfo = array_merge($objectInfo, ['path' => ['texture' => $pathTexture,
                                                                           'temp' => $pathTemp]]);
                        
                        // Проверка, есть ли logo у texture
                        if ($zipFile->has('pack.png')) {
                            
                            // Разархивирование logo texture
                            $zipFile->read('pack.png', function ($entry, MiscStream $stream) use (&$objectInfo) {
                                $pathLogo = $objectInfo['path']['temp'] . 'logo.png';
                                if (fs::copy($stream, $pathLogo))
                                    $objectInfo['path']['logo'] = $pathLogo;
                            });
                        }
                        
                        // Список подключенных textures
                        $enabledTextures = self::getEnabledTextures();
                        
                        // Проверка на подключение texture
                        if ($enabledTextures && in_array($object->getName(), $enabledTextures))
                            $objectInfo['enabled'] = true;
                        
                        // Создание файла с hash-суммой
                        Stream::putContents($pathTemp . 'hash', sha1_file($object->getPath()));
                        
                        // Добавление texture в список
                        self::$objectsInfo[$object->getName()] = $objectInfo;
                        
                        // Создание item texture
                        DesignTextures::addItem($objectInfo);
                        
                        // Регистрация файлов для запрета на изменение
                        FileSystem::registerFile($objectInfo['path']['texture']);
                        FileSystem::registerFile($pathTemp . 'pack.mcmeta');
                        if (isset($objectInfo['path']['logo'])) FileSystem::registerFile($objectInfo['path']['logo']);
                        
                        // Сообщение о успешном добавлении текстур-пака
                        app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.added'));
                    }
                } else {
                    app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.incorrect'));
                }
                unset($zipFile);
                
            } catch (Exception $error) {
                app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.unknown.error'));
                return;
            }
            
        } else {
            app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.select.file'));
        }
    }
    
    /**
     * Подключить текстур-пак.
     * 
     * @param string $nameTexture
     * @param UXMaterialButton $buttonMode
     */
    static function enabled (string $nameTexture, UXMaterialButton $buttonMode)
    {
        // Получение активных textures
        $enabledTextures = self::getEnabledTextures();
        
        // Проверки
        if (!$enabledTextures || !in_array($nameTexture, $enabledTextures)) {
            
            // Подключение texture
            $enabledTextures[] = $nameTexture;
            
            // Изменение настроек Minecraft
            app()->getForm(MainForm)->iniOptions->set('resourcePacks', '["' . implode('", "', $enabledTextures) . '"]');
            
            // Сохранение настроек Minecraft
            if (Minecraft::setMinecraftOptions(app()->getForm(MainForm)->iniOptions->toArray())) {
            
                // Включен texture
                self::$objectsInfo[$nameTexture]['enabled'] = true;
                
                // Действия
                app()->getForm(MainForm)->boxEnTextures->items->add(app()->getForm(MainForm)->boxTextures->selectedItem);
                app()->getForm(MainForm)->boxTextures->items->removeByIndex(app()->getForm(MainForm)->boxTextures->selectedIndex);
                $buttonMode->graphic->free();
                $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/icon/line-16.png'));
            }
        } else app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.not.enabled'));
    }
    
    /**
     * Отключить текстур-пак.
     * 
     * @param string $nameTexture
     * @param UXMaterialButton $buttonMode
     */
    static function disabled (string $nameTexture, UXMaterialButton $buttonMode)
    {
        // Получение активных textures
        $enabledTextures = self::getEnabledTextures();
        
        // Проверки
        if (($key = array_search($enabledTextures, $nameTexture, false)) !== false) {
            
            // Отключение texture
            unset($enabledTextures[$key]);
            
            // Сортировка
            sort($enabledTextures, SORT_NUMERIC);
            
            // Изменение настроек Minecraft
            app()->getForm(MainForm)->iniOptions->set('resourcePacks', '["' . implode('", "', $enabledTextures) . '"]');
            
            // Сохранение настроек Minecraft
            if (Minecraft::setMinecraftOptions(app()->getForm(MainForm)->iniOptions->toArray())) {
                
                // Отключен texture
                unset(self::$objectsInfo[$nameTexture]['enabled']);
                
                // Действия
                app()->getForm(MainForm)->boxTextures->items->add(app()->getForm(MainForm)->boxEnTextures->selectedItem);
                app()->getForm(MainForm)->boxEnTextures->items->removeByIndex(app()->getForm(MainForm)->boxEnTextures->selectedIndex);
                $buttonMode->graphic->free();
                $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/icon/add-16.png'));
            }
        } else app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.not.disabled'));
    }
    
    /**
     * Удалить текстур-пак.
     * 
     * @param string $nameTexture
     */
    static function delete (string $nameTexture)
    {
        $objectInfo = self::$objectsInfo[$nameTexture];
        
        // Разрегистрация resourcepack'a && удаление resourcepack'a
        if (FileSystem::unRegisterFile($objectInfo['path']['texture']) && fs::delete($objectInfo['path']['texture'])) {
        
            // Подключен || Отключен
            if (isset($objectInfo['enabled']))
                app()->getForm(MainForm)->boxEnTextures->items->removeByIndex(app()->getForm(MainForm)->boxEnTextures->selectedIndex);
            else app()->getForm(MainForm)->boxTextures->items->removeByIndex(app()->getForm(MainForm)->boxTextures->selectedIndex);
            
            // Удаление resourcepack'a из списка
            unset(self::$objectsInfo[$nameTexture]);
            
            // Успех!
            app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.delete.success'));
        } else app()->getForm(MainForm)->toast(L::translate('mainform.toast.textures.delete.not'));
    }
    
    /**
     * Получить список подключенных текстур-паков.
     */
    static function getEnabledTextures ()
    {
        $enabledTextures = Json::decode(app()->getForm(MainForm)->iniOptions->get('resourcePacks'));
        return empty($enabledTextures) ? false : $enabledTextures;
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