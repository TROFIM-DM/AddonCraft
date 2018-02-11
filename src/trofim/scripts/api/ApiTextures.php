<?php
namespace trofim\scripts\api;

use gui;
use Exception;
use php\compress\ZipFile;
use facade\Json;
use trofim;
use std;

/**
 * Класс для работы с API текстур-паков.
 */
class ApiTextures 
{
    
    /**
     * Поиск текстур-паков.
     */
    public static function findTextures () {
    
        /* Проверка на существование папки resourcepacks */
        if (fs::exists(AddonCraft::getPathMinecraft() . '\\resourcepacks\\')) {
        
            /* Поиск файлов resourcepacks */
            $fileTextures = new File(AddonCraft::getPathMinecraft() . '\\resourcepacks\\');
            foreach ($fileTextures->findFiles() as $file) {
                if (fs::isFile($file->getPath()) && fs::ext($file->getName()) == 'zip')
                    $listTextures[] = ['path' => $file->getPath(), 'hash' => sha1_file($file->getPath())];
            }
            
            /* Если resourcepacks нет */
            if (empty($listTextures)) return false;
            
            /* Список подключенных resourcepacks */
            $enabledTextures = self::getEnabledTextures();
            
            foreach ($listTextures as $texture) {
            
                try {
                        
                    /* Создание ZIP */
                    $zipFile = new ZipFile($texture['path']);
                    
                    /* Проверка на наличие файла pack.mcmeta */
                    if ($zipFile->has('pack.mcmeta')) {
                        
                        /* Путь к Temp для resourcepack'a */
                        $pathTemp = AddonCraft::getAppTemp() . '\\' . fs::nameNoExt($texture['path']) . '\\';
                        
                        /* Разархивирование pack.mcmeta */
                        $zipFile->read('pack.mcmeta', function ($entry, MiscStream $stream) use ($pathTemp) {
                            fs::makeDir($pathTemp);
                            fs::copy($stream, $pathTemp . 'pack.mcmeta'); 
                        });
                        
                        /* Получение содержимого pack.mcmeta */
                        if (fs::exists($pathTemp . 'pack.mcmeta'))
                            $textureInfo = Json::decode(Stream::getContents($pathTemp . 'pack.mcmeta'));
                        else return false;
                        
                        /* Если файл pack.mcmeta успешно прочитан */
                        if (isset($textureInfo['pack']['pack_format'])) {
                            
                            /* Добавление названия для resourcepack'a */
                            $textureInfo['pack']['name'] = fs::nameNoExt($texture['path']);
                            
                            /* Добавление путей до resourcepack'a */
                            $textureInfo['path']['texture'] = $texture['path'];
                            $textureInfo['path']['temp'] = $pathTemp;
                            
                            /* Проверка, есть ли logo у resourcepack'a */
                            if ($zipFile->has('pack.png')) {
                                
                                /* Разархивирование logo resourcepack'a */
                                $zipFile->read('pack.png', function ($entry, MiscStream $stream) use (&$textureInfo) {
                                    $pathLogo = $textureInfo['path']['temp'] . 'logo.png';
                                    if (fs::copy($stream, $pathLogo))
                                        $textureInfo['path']['logo'] = $pathLogo;
                                });
                            }
                            
                            /* Проверка на подключение resourcepack'a */
                            if ($enabledTextures && in_array(fs::name($texture['path']), $enabledTextures))
                                $textureInfo['enabled'] = true;
                            
                            /* Создание файла с hash-суммой */
                            Stream::putContents($pathTemp . 'hash', $texture['hash']);
                            
                            /* Добавление resourcepack'a в список */
                            AddonCraft::$listTextures[fs::name($textureInfo['path']['texture'])] = $textureInfo;
                            
                            /* Создание item resourcepack */
                            DesignTextures::addItemTexture($textureInfo);
                            
                            /* Регистрация файлов для запрета на изменение */
                            AddonCraft::registerFile($textureInfo['path']['texture']);
                            AddonCraft::registerFile($pathTemp . 'pack.mcmeta');
                            if (isset($textureInfo['path']['logo'])) AddonCraft::registerFile($textureInfo['path']['logo']);
                        }
                    }
                    
                    unset($zipFile);
                    
                } catch (Exception $error) {
                    
                }
                
            }
        }
        
    }
    
    /**
     * Добавление текстур-пака.
     * 
     * @param $TEXTURE
     */
    public static function addTexture ($TEXTURE) {
        
        /* Проверки */
        if (fs::isFile($TEXTURE->getPath()) && fs::ext($TEXTURE->getPath()) == 'zip') {
        
            /* Поиск файлов в папке resourcepacks */
            $filesTextures = new File(AddonCraft::getPathMinecraft() . '\\resourcepacks\\');
            foreach ($filesTextures->findFiles() as $file) {
                if (fs::isFile($file->getPath()) &&
                    fs::ext($file->getPath()) == 'zip' &&
                    fs::name($file->getPath()) == fs::name($TEXTURE->getPath())) {
                    app()->form(MainForm)->toast('Такой текстур-пак уже добавлен!');
                    return false;
                }
            }
            
            try {
                
                /* Создание ZIP */
                $zipFile = new ZipFile($TEXTURE->getPath());
                
                /* Проверка на наличие файла pack.mcmeta */
                if ($zipFile->has('pack.mcmeta')) {
                
                    /* Путь к Temp для resourcepack'a */
                    $pathTemp = AddonCraft::getAppTemp() . '\\' . fs::nameNoExt($TEXTURE->getName()) . '\\';
                    
                    /* Разархивирование pack.mcmeta */
                    $zipFile->read('pack.mcmeta', function ($entry, MiscStream $stream) use ($pathTemp) {
                        fs::makeDir($pathTemp);
                        fs::copy($stream, $pathTemp . 'pack.mcmeta'); 
                    });
                    
                    /* Получение содержимого pack.mcmeta */
                    if (fs::exists($pathTemp . 'pack.mcmeta'))
                        $textureInfo = Json::decode(Stream::getContents($pathTemp . 'pack.mcmeta'));
                    else {
                        app()->form(MainForm)->toast('Не удалось считать' . "\n" . 'информацию о текстур-паке!');
                        return false;
                    }
                    
                    /* Если файл pack.mcmeta успешно прочитан */
                    if (isset($textureInfo)) {
                        
                        /* Путь добавленного resourcepack'a */
                        $pathTexture = AddonCraft::getPathMinecraft() . '\\resourcepacks\\' . $TEXTURE->getName();
                        
                        /* Создание папки resourcepacks, если нет */
                        if (!fs::exists(AddonCraft::getPathMinecraft() . '\\resourcepacks\\'))
                            mkdir(AddonCraft::getPathMinecraft() . '\\resourcepacks\\');
                        
                        /* Добавление resourcepack'a */
                        if (!fs::copy($TEXTURE->getPath(), $pathTexture)) {
                            app()->form(MainForm)->toast('Не удалось установить текстур-пак!');
                            return false;
                        }
                        
                        /* Добавление названия для resourcepack'a */
                        $textureInfo['pack']['name'] = fs::nameNoExt($TEXTURE->getPath());
                        
                        /* Добавление путей до resourcepack'a */
                        $textureInfo['path']['texture'] = $pathTexture;
                        $textureInfo['path']['temp'] = $pathTemp;
                        
                        /* Проверка, есть ли logo у resourcepack'a */
                        if ($zipFile->has('pack.png')) {
                            
                            /* Разархивирование logo resourcepack'a */
                            $zipFile->read('pack.png', function ($entry, MiscStream $stream) use (&$textureInfo) {
                                $pathLogo = $textureInfo['path']['temp'] . 'logo.png';
                                if (fs::copy($stream, $pathLogo))
                                    $textureInfo['path']['logo'] = $pathLogo;
                            });
                        }
                        
                        /* Список подключенных resourcepacks */
                        $enabledTextures = self::getEnabledTextures();
                        
                        /* Проверка на подключение resourcepack'a */
                        if ($enabledTextures && in_array(fs::name($texture['path']), $enabledTextures))
                            $textureInfo['enabled'] = true;
                        
                        /* Создание файла с hash-суммой */
                        Stream::putContents($pathTemp . 'hash', sha1_file($TEXTURE->getPath()));
                        
                        /* Добавление resourcepack'a в список */
                        AddonCraft::$listTextures[fs::name($textureInfo['path']['texture'])] = $textureInfo;
                        
                        /* Создание item resourcepack */
                        DesignTextures::addItemTexture($textureInfo);
                        
                        /* Регистрация файлов для запрета на изменение */
                        AddonCraft::registerFile($textureInfo['path']['texture']);
                        AddonCraft::registerFile($pathTemp . 'pack.mcmeta');
                        if (isset($textureInfo['path']['logo'])) AddonCraft::registerFile($textureInfo['path']['logo']);
                        
                        /* Сообщение о успешном добавлении текстур-пака */
                        app()->form(MainForm)->toast('Текстур-пак успешно добавлен!');
                    }
                } else {
                    app()->form(MainForm)->toast('[Текстур-пак] Выбран неверный файл!');
                }
                
                unset($zipFile);
                
            } catch (Exception $error) {
                app()->form(MainForm)->toast('Не удалось установить текстур-пак!' . "\n" . 'Произошла неизвестная ошибка...');
                return false;
            }
            
        } else {
            app()->form(MainForm)->toast('Выберите файл - текстур-пак!');
        }
        
    }
    
    /**
     * Подключение текстур-пака.
     * 
     * @param $nameTexture
     * @param $buttonMode
     */
    public static function enabledTexture ($nameTexture, $buttonMode) {
    
        /* Получение активных textures */
        $enabledTextures = self::getEnabledTextures();
        
        /* Проверки */
        if (!$enabledTextures || !in_array($nameTexture, $enabledTextures)) {
            
            /* Подключение texture */
            $enabledTextures[] = $nameTexture;
            
            /* Изменение настроек Minecraft */
            app()->form(MainForm)->iniOptions->set('resourcePacks', '["' . implode('", "', $enabledTextures) . '"]');
            
            /* Сохранение настроек Minecraft */
            if (AddonCraft::setMinecraftOptions(app()->form(MainForm)->iniOptions->toArray())) {
            
                /* Включен texture */
                AddonCraft::$listTextures[$nameTexture]['enabled'] = true;
                
                /* Действия */
                app()->form(MainForm)->boxEnTextures->items->add(app()->form(MainForm)->boxTextures->selectedItem);
                app()->form(MainForm)->boxTextures->items->removeByIndex(app()->form(MainForm)->boxTextures->selectedIndex);
                $buttonMode->graphic->free();
                $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/line.png'));
                
            } else app()->form(MainForm)->toast('Не удалось подключить текстур-пак!');
        } else app()->form(MainForm)->toast('Не удалось подключить текстур-пак!');
    }
    
    /**
     * Отключение текстур-пака.
     * 
     * @param $nameTexture
     * @param $buttonMode
     */
    public static function disabledTexture ($nameTexture, $buttonMode) {
    
        /* Получение активных textures */
        $enabledTextures = self::getEnabledTextures();
        
        /* Проверки */
        if (($key = array_search($enabledTextures, $nameTexture, false)) !== false) {
            
            /* Отключение texture */
            unset($enabledTextures[$key]);
            
            /* Сортировка */
            sort($enabledTextures, SORT_NUMERIC);
            
            /* Изменение настроек Minecraft */
            app()->form(MainForm)->iniOptions->set('resourcePacks', '["' . implode('", "', $enabledTextures) . '"]');
            
            /* Сохранение настроек Minecraft */
            if (AddonCraft::setMinecraftOptions(app()->form(MainForm)->iniOptions->toArray())) {
                
                /* Отключен texture */
                unset(AddonCraft::$listTextures[$nameTexture]['enabled']);
                
                /* Действия */
                app()->form(MainForm)->boxTextures->items->add(app()->form(MainForm)->boxEnTextures->selectedItem);
                app()->form(MainForm)->boxEnTextures->items->removeByIndex(app()->form(MainForm)->boxEnTextures->selectedIndex);
                $buttonMode->graphic->free();
                $buttonMode->graphic = new UXImageView(new UXImage('res://.data/img/add.png'));
                
            } else app()->form(MainForm)->toast('Не удалось подключить текстур-пак!');
        } else app()->form(MainForm)->toast('Не удалось отключить текстур-пак!');
    }
    
    /**
     * Удаление текстур-пака.
     * 
     * @param $nameTexture
     */
    public static function deleteTexture ($nameTexture) {
    
        /* Разрегистрация resourcepack'a && удаление resourcepack'a */
        if (AddonCraft::unRegisterFile(AddonCraft::$listTextures[$nameTexture]['path']['texture']) && fs::delete(AddonCraft::$listTextures[$nameTexture]['path']['texture'])) {
        
            /* Подключен || Отключен */
            if (isset(AddonCraft::$listTextures[$nameTexture]['enabled']))
                app()->form(MainForm)->boxEnTextures->items->removeByIndex(app()->form(MainForm)->boxEnTextures->selectedIndex);
            else app()->form(MainForm)->boxTextures->items->removeByIndex(app()->form(MainForm)->boxTextures->selectedIndex);
            
            /* Удаление resourcepack'a из списка */
            unset(AddonCraft::$listTextures[$nameTexture]);
            
            /* Успех! */
            app()->form(MainForm)->toast('Текстур-пак успешно удален!');
        } else app()->form(MainForm)->toast('Не удалось удалить текстур-пак!');
    }
    
    /**
     * Получение списка подключенных текстур-паков.
     */
    public static function getEnabledTextures () {
        $enabledTextures = Json::decode(app()->form(MainForm)->iniOptions->get('resourcePacks'));
        return empty($enabledTextures) ? false : $enabledTextures;
    }
    
}