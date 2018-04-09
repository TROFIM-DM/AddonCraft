<?php
namespace trofim\scripts\system;

use trofim, std;

/**
 * Класс для работы с папками и содержимым.
 * 
 * @author TROFIM
 * @url https://github.com/TROFIM-YT/AddonCraft
 */
class Dir {

    /**
     * Удалить папку и файлы, лежащие в папке.
     * 
     * @param string $pathDir Путь к папке.
     * @return bool
     */
    public static function delete (string $pathDir) : bool
    {
        if ($dir = new File($pathDir)->findFiles()) {
            foreach ($dir as $path)
                $list[] = $path->getPath();
            
            if (isset($list)) {
                foreach ($list as $file)
                    (fs::isDir($file)) ? self::delete($file): fs::delete($file);
            }
        }
        return fs::delete($pathDir);
    }
    
    /**
     * Копировать и переместить папку и файлы, находящиеся в папке.
     * (Папка должна существовать).
     * 
     * @param string $fromDir Путь к папке из которой.
     * @param string $toDir Путь к папке в которую.
     * @return bool
     */
    public static function copy (string $fromDir, string $toDir) : bool
    {
        if (fs::exists($fromDir = fs::abs($fromDir)) && fs::exists($toDir = fs::abs($toDir))) {
            if ($dir = new File($fromDir)->findFiles()) {
                foreach ($dir as $path)
                    $list[] = $path->getPath();
                    
                if (isset($list)) {
                    foreach ($list as $file) {
                        $path = $toDir . '\\' . fs::name($file);
                        if (fs::isDir($file)) {
                            fs::makeDir($path);
                            self::copy($file, $path);
                        } else fs::copy($file, $path);
                    }
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Переместить папку и файлы, находящиеся в папке.
     * 
     * @param string $fromDir Путь к папке откуда.
     * @param string $toDir Путь к папке куда.
     * @return bool
     */
    public static function move (string $fromDir, string $toDir) : bool
    {
        return fs::move($fromDir, $toDir);
    }
    
    /**
     * Проверить папка ли это.
     * 
     * @param string $pathDir Путь к папке.
     * @return bool
     */
    public static function isDir (string $pathDir) : bool
    {
        return fs::isDir($pathDir);
    }
    
}