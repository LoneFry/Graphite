<?php
/**
 * DataBroker - Core Data Broker between applications and Data Providers
 * File : /^/lib/DataBroker.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * DataBroker class - Delegates data requests to appropriate DataProvider
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class DataBroker implements IDataProvider {
    /** @var array $Providers A cache of lazy-loaded DataProviders */
    protected static $Providers = array();

    /** @var array $ProviderDict A Map of models to DataProviders */
    protected static $ProviderDict = array();

    /**
     * Set Broker dictionary
     *
     * @param array $dict Array associating Models to DataProviders
     *                    in the form 'Model' => 'DataProvider
     *
     * @return void
     */
    public static function setDict(array $dict) {
        self::$ProviderDict = $dict;
    }

    /**
     * Search for records of type $class according to search params $params
     * Order results by $orders and limit results by $count, $start
     *
     * @param string $class  Name of Model to search for
     * @param array  $params Values to search against
     * @param array  $orders Order(s) of results
     * @param int    $count  Number of rows to fetch
     * @param int    $start  Number of rows to skip
     *
     * @return array Found records
     */
    public function fetch($class, array $params, array $orders = array(), $count = null, $start = 0) {
        return self::getDataProviderForClass($class)->{__FUNCTION__}($class, $params, $orders, $count, $start);
    }

    /**
     * Load data for passed model
     *
     * @param PassiveRecord &$Model Model to load, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function load(PassiveRecord &$Model) {
        return self::getDataProviderForClass($Model)->{__FUNCTION__}($Model);
    }

    /**
     * Load data for passed model by its primary key value
     *
     * @param PassiveRecord &$Model Model to load, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function select(PassiveRecord &$Model) {
        return self::getDataProviderForClass($Model)->{__FUNCTION__}($Model);
    }

    /**
     * Load data for passed model by its set values
     *
     * @param PassiveRecord &$Model Model to load, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function fill(PassiveRecord &$Model) {
        return self::getDataProviderForClass($Model)->{__FUNCTION__}($Model);
    }

    /**
     * Save data for passed model
     *
     * @param PassiveRecord &$Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function save(PassiveRecord &$Model) {
        return self::getDataProviderForClass($Model)->{__FUNCTION__}($Model);
    }

    /**
     * Save data for passed model
     *
     * @param PassiveRecord &$Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function insert(PassiveRecord &$Model) {
        return self::getDataProviderForClass($Model)->{__FUNCTION__}($Model);
    }

    /**
     * Save data for passed model
     *
     * @param PassiveRecord &$Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function update(PassiveRecord &$Model) {
        return self::getDataProviderForClass($Model)->{__FUNCTION__}($Model);
    }

    /**
     * Get Data Provider For provided Class
     *
     * @param string $class Name of class
     *
     * @return null|IDataProvider
     */
    public static function getDataProviderForClass($class) {
        if (is_object($class) && is_a($class, 'PassiveRecord')) {
            $class = get_class($class);
        }

        if (!is_string($class)) {
            return null;
        }

        // Walk up the class hierarchy looking for a class with an assigned DataProvider
        do {
            // If the class has an assigned DataProvider
            if (isset(static::$ProviderDict[$class])) {
                // If the assigned DataProvider is not instantiated yet
                if (!isset(static::$Providers[static::$ProviderDict[$class]])) {
                    // Instantiate DataProvider of class `static::$ProviderDict[$class]`
                    static::$Providers[static::$ProviderDict[$class]] = G::build(static::$ProviderDict[$class]);
                }
                return static::$Providers[static::$ProviderDict[$class]];
            }
        } while (false !== $class = get_parent_class($class));

        // The specified class does not have an assigned DataProvider
        // If only someone had assigned one to PassiveRecord or DataModel
        return null;
    }
}
