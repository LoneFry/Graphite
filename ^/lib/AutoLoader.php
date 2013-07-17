<?php
/**
 * AutoLoader
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <apt142@gmail.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 *
 * @see      http://jes.st/2011/phpunit-bootstrap-and-autoloading-classes/
 */


/**
 * AutoLoader
 *
 * Caches a directory list and then uses that list to auto include files as
 * necessary.
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <apt142@gmail.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

class AutoLoader {

    static private $classNames = array();

    /**
     * Index the file path based on the file name minus the .php extension.
     *
     * The intent is to cache a list of file/location pairs so it doesn't need
     * to search the directory repeatedly.
     *
     * @return void
     */
    public static function registerDirectory() {
        // Grab the items in reverse so the first in the array overwrites at
        // the end if there is a conflict.
        $dirs = array_reverse(explode(';', G::$G['includePath']));
        foreach ($dirs as $dir) {
            exec('find ' . SITE . $dir
                . ' -path "*/controllers/*" -name "*.php"'
                . ' -o -path "*/lib/*"  -name "*.php"'
                . ' -o -path "*/models/*" -name "*.php"'
                . ' -o -path "*/reports/*" -name "*.php"',
                $output
            );
            foreach ($output as $file) {
                $className = substr(
                    $file,
                    strrpos($file, '/') + 1,
                    strrpos($file, '.') - strrpos($file, '/') - 1
                );
                self::$classNames[$className] = $file;
            }
        }
    }

    /**
     * Adds a path after the initial init.
     *
     * @param string $path      Path to include
     * @param bool   $overwrite Optional. Flags to overwrite existing.
     *
     * @return void
     */
    public static function addDirectory($path, $overwrite = false) {
        exec('find ' . escapeshellarg($path) . ' -name "*.php"', $output);
        foreach ($output as $file) {
            $className = substr(
                $file, strrpos($file, '/') + 1,
                strrpos($file, '.') - strrpos($file, '/') - 1
            );
            // If it is already registered don't overwrite
            if (true == $overwrite || !isset(self::$classNames[$className])) {
                self::$classNames[$className] = $file;
            }
        }
    }

    /**
     * Locates a class and loads it.
     *
     * @param string $className Class you are trying to load.
     *
     * @return void
     */
    public static function loadClass($className) {
        if (isset(self::$classNames[$className])) {
            require_once self::$classNames[$className];
        }
    }

}

AutoLoader::registerDirectory();

