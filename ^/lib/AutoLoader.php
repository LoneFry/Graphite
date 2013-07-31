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
    protected static $classNames = array();

    /**
     * Finds the file name sans extension.
     *
     * @param string $path The path to parse
     *
     * @return string
     */
    private static function getFileName($path) {
        return basename($path, '.php');
    }

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
            $output = static::getDirListing($dir);
            foreach ($output as $file) {
                $className = static::getFileName($file);
                static::$classNames[$className] = $file;
            }
        }
    }

    /**
     * Finds all the files that we would load by default.
     *
     * @param string $dir Directory to be index
     *
     * @return mixed
     */
    public static function getDirListing($dir) {
        exec('find ' . SITE . $dir
            . ' -path "*/controllers/*" -name "*.php"'
            . ' -o -path "*/lib/*"  -name "*.php"'
            . ' -o -path "*/models/*" -name "*.php"'
            . ' -o -path "*/reports/*" -name "*.php"',
            $output
        );
        return $output;
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
            static::addFile($file, $overwrite);
        }
    }

    /**
     * Adds a single file after the initial init.
     *
     * @param string $path      File to include
     * @param bool   $overwrite Optional. Flags to overwrite existing.
     *
     * @return void
     */
    public static function addFile($path, $overwrite = false) {
        $className = static::getFileName($path);
        // If it is already registered don't overwrite
        if (true == $overwrite || !isset(static::$classNames[$className])) {
            static::$classNames[$className] = $path;
        }
    }

    /**
     * Gets the path of the classname passed into it.
     *
     * @param string $className Name of class to fetch
     *
     * @return null
     */
    public static function getClass($className) {
        if (isset(static::$classNames[$className])) {
            return static::$classNames[$className];
        }
        return null;
    }

    /**
     * Removes a registered class.
     *
     * @param string $className Class name to remove
     *
     * @return void
     */
    public static function removeClass($className) {
        if (isset(static::$classNames[$className])) {
            unset(static::$classNames[$className]);
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
        if (isset(static::$classNames[$className])) {
            require_once static::$classNames[$className];
        }
    }
}
