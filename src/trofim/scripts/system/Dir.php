<?php
namespace trofim\scripts\system;

use trofim;
use std;

/**
 * Класс Dir для работы с папками и содержимым.
 * 
 * @author TROFIM
 */
class Dir {

    /**
     * Удалить папку и файлы, лежащие в папке.
     * 
     * @param $pathDir Путь к папке.
     * @param bool
     */
    public static function delete ($pathDir) : bool
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
     * @param $fromDir Путь к папке из которой.
     * @param $toDir Путь к папке в которую.
     * @return bool
     */
    public static function copy ($fromDir, $toDir) : bool
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
    
}