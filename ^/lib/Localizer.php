<?php
/**
 * Localizer
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 *
 * @see      http://jes.st/2011/phpunit-bootstrap-and-autoloading-classes/
 */


/**
 * Localizer
 *
 * Interface for translating values into a local accepted unit/language.
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class Localizer {

    /** @var string Current language to do includes off of. */
    private static $language = 'en_us';

    /** @var array  Array of Language keys to reference. */
    private static $langKeys = array();


    /**
     * Sets the language to be used from here on out.
     *
     * @param string $lang Language to start using.
     *
     * @return void
     */
    public static function setLanguage($lang) {
        self::$language = $lang;
        self::$langKeys = [];
        self::getLib('Global');
    }


    /**
     * Imports the requested language library
     *
     * @param string $libName Library name to import
     *
     * @return bool
     */
    public static function getLib($libName) {
        $filename = SITE.'/^/lang/'.self::$language.'/'.$libName.'.php';

        if (file_exists($filename)) {
            // @codeCoverageIgnoreStart
            $newKeys = include $filename;
            // @codeCoverageIgnoreStop
            self::$langKeys = array_merge($newKeys, self::$langKeys);
            return true;
        }
        return false;
    }

    /**
     * Translates a key into a language specific string.
     * This function can take additional parameters to fill in specific details.
     *
     * @param string $langKey Language Key to look up.
     *
     * @return string
     */
    public static function translate($langKey) {
        $args = func_get_args();
        array_shift($args);

        if (isset(self::$langKeys[$langKey])) {
            $rawCopy = self::$langKeys[$langKey];
        } else {
            $rawCopy = $langKey;
        }

        return vsprintf($rawCopy, $args);
    }


}