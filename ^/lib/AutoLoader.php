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
    /** @var array Registry of known class names */
    protected static $classNames = array();

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
                $className = basename($file, '.php');
                static::$classNames[$className] = $file;
            }
        }
    }

    /**
     * Scan directory for classes to include
     *
     * @param string $dir The directory to scan
     *
     * @return array List of class files found
     */
    public static function getDirListing($dir) {
        // Clean up path and prepare to prepend it to each result
        $dir    = realpath(SITE.$dir).DIRECTORY_SEPARATOR;
        $output = array();
        foreach (scandir($dir) as $path) {
            // Only scan directories expected to have classes
            if (!in_array($path, array('controllers', 'lib', 'models', 'reports'))) {
                continue;
            }
            $output = array_merge($output, self::findPhpFiles($dir.$path.DIRECTORY_SEPARATOR));
        }

        return $output;
    }

    /**
     * Recursive wrapper to scandir that returns absolute paths of php files.
     *
     * @param string $dir The directory to scan
     *
     * @return array List of class files found
     */
    public static function findPhpFiles($dir) {
        // Clean up path and prepare to prepend it to each result
        $dir = realpath($dir).DIRECTORY_SEPARATOR;
        // convert return values of scandir() to full paths
        $files = array_map(function ($val) use ($dir) {
            return $dir.$val;
        }, scandir($dir));
        $output = array();
        while (!empty($files)) {
            $file = array_shift($files);
            if (in_array(basename($file), array('.', '..'))) {
                continue;
            }
            if (is_dir($file)) {
                // Add full paths of subdirectories to the list of paths to scan
                $files = array_merge($files, array_map(function ($val) use ($file) {
                    return $file.DIRECTORY_SEPARATOR.$val;
                }, scandir($file)));
            } elseif ('.php' == substr($file, -4)) {
                // Add php files to the list of paths to return
                $output[] = $file;
            }
        }

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
        $output = self::findPhpFiles($path);
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
        $className = basename($path, '.php');
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

    /**
     * Adds a class and file to the lookup table
     *
     * @param string $className Class you are trying to load.
     * @param string $path      File containing class
     *
     * @return void
     */
    public static function addClass($className, $path) {
        static::$classNames[$className] = $path;
    }
}
