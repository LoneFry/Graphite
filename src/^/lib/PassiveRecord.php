<?php
/**
 * PassiveRecord - core database record class
 * File : /^/lib/PassiveRecord.php
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * PassiveRecord class - used as a base class for Record Model classes
 *  for use with a DataProvider
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 * @see      /^/lib/DataModel.php
 */
abstract class PassiveRecord extends DataModel implements JsonSerializable {
    /** @var array Instance DB values of vars defined in $vars */
    protected $DBvals = array();

    // Should be defined in subclasses
    // protected static $table;// name of table
    // protected static $pkey;// name of primary key column
    // protected static $vars = array();// record definition

    /**
     * Constructor accepts four prototypes:
     *  Record(true) will create an instance with default values
     *  Record(int) will create an instance with pkey set to int
     *  Record(array()) will create an instance with supplied values
     *  record(array(),true) will create a record with supplied values
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b set defaults
     *
     * @throws Exception
     */
    public function __construct($a = null, $b = null) {
        // Ensure that a pkey is defined in subclasses
        if (!isset(static::$pkey) || !isset(static::$vars[static::$pkey])) {
            throw new Exception('Record class defined with no pkey, or pkey not registered');
        }
        if (!isset(static::$table)) {
            throw new Exception('Record class defined with no table');
        }

        // initialize the values arrays with null values as some tests depend
        foreach (static::$vars as $k => $v) {
            $this->DBvals[$k] = $this->vals[$k] = null;
        }

        // This fakes constructor overriding
        if (true === $a) {
            $this->defaults();
        } elseif (is_numeric($a)) {
            $this->setAll(array(static::$pkey => $a));
        } else {
            if (true === $b) {
                $this->defaults();
            }
            if (is_array($a)) {
                $this->setAll($a);
            }
        }
    }

    /**
     * Return the pkey, which is a protected static var
     *
     * @return string Model's primary key
     */
    public static function getPkey() {
        return static::$pkey;
    }

    /**
     * Return the query, which is a protected static var
     *
     * @return string Model's SELECT query
     */
    public static function getQuery() {
        return static::$query;
    }

    /**
     * Return the table, which is a protected static var
     *
     * @param string $joiner Request a joiner table by specifying which table
     *                        to join with
     *
     * @return string Model's table name
     */
    public static function getTable($joiner = null) {
        // If no joiner is specified, we just want the table name
        if (null == $joiner) {
            return static::$table;
        }

        // If a known joiner is specified, return it
        if (isset(static::$joiners) && isset(static::$joiners[$joiner])) {
            return static::$joiners[$joiner];
        }

        // If a plausible joiner is specified, derive it
        if (preg_match('/^[\w\d]+$/i', $joiner)) {
            return static::$table.'_'.$joiner;
        }

        // An invalid joiner was requested, that's an error
        trigger_error('Requested invalid joiner table');

        return null;
    }

    /**
     * Return the model field list
     *
     * @return array Vars array representing table schema
     */
    public static function getFieldList() {
        return static::$vars;
    }

    /**
     * Return array of values changed since last DB load/save
     *
     * @return array Changed values
     */
    public function getDiff() {
        $diff = array();
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k]
                || (null === $this->vals[$k]) != (null === $this->DBvals[$k])
                || (true === $this->vals[$k]) != (true === $this->DBvals[$k])
                || (false === $this->vals[$k]) != (false === $this->DBvals[$k])
            ) {
                $diff[$k] = $this->vals[$k];
            }
        }

        return $diff;
    }

    /**
     * Return whether record was altered
     *
     * @return bool True if altered, False if not
     */
    public function hasDiff() {
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k]
                || (null === $this->vals[$k]) != (null === $this->DBvals[$k])
                || (true === $this->vals[$k]) != (true === $this->DBvals[$k])
                || (false === $this->vals[$k]) != (false === $this->DBvals[$k])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets DBvals to match current vals
     *
     * @return mixed Array of unregistered values on success, false on failure
     */
    public function unDiff() {
        foreach (static::$vars as $key => $ignore) {
            $this->DBvals[$key] = $this->vals[$key];
        }
    }

    /**
     * Override this function to perform custom actions AFTER load
     *
     * @param array $row Unregistered values selected in load()
     *
     * @return void
     */
    public function onload(array $row = array()) {
    }

    /**
     * Override this function to perform custom actions BEFORE insert
     * This will not be called if insert() does not attempt commit to DB
     *
     * @return void
     */
    public function oninsert() {
    }

    /**
     * Override this function to perform custom actions BEFORE update
     * This will not be called if update() does not attempt commit to DB
     *
     * @return void
     */
    public function onupdate() {
    }

    /**
     * Override this function to perform custom actions BEFORE delete
     * This will not be called if update() does not attempt commit to DB
     *
     * @return void
     */
    public function ondelete() {
    }

    /**
     * "Load" object from array, sets DBvals as if loaded from database
     *  if pkey is not passed, fail
     *
     * @param array $row values
     *
     * @return mixed Array of unregistered values on success, false on failure
     */
    public function load_array(array $row = array()) {
        if (!isset($row[static::$pkey]) || null === $row[static::$pkey]) {
            return false;
        }
        $row = $this->setAll($row, false);
        foreach (static::$vars as $k => $v) {
            $this->DBvals[$k] = $this->vals[$k];
        }
        $this->onload($row);

        return $row;
    }

    /**
     * Produce meaningful array representation of Model
     *
     * @return array
     */
    public function toArray() {
        return $this->getAll();
    }

    /**
     * Instruct json_encode to only encode the array cast
     *
     * @return string json_encode'd array of values
     */
    public function jsonSerialize() {
        return $this->toArray();
    }
}
