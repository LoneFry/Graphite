<?php
/**
 * mysqli_ - mysqli query-logging wrapper
 * File : /^/lib/mysqli_.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */


/**
 * mysqli_ class - extend mysqli to add query logging
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class mysqli_ {
    /**
     * to log the queries
     */
    private static $_aQueries = array(array(0));

    /**
     * common prefix used by app tables, for reference
     */
    private static $_tabl = '';

    /**
     * whether to log
     */
    private static $_log = false;

    /**
     * whether connection succeeded
     */
    private $_open = false;


    /**
     * Explose for mocking.
     */
    public $errno = 0;
    public $error = '';

    /**
     * mysqli_ constructor
     *
     * @param string $host pass through to mysqli - hostname of DB server
     * @param string $user pass through to mysqli - DB username
     * @param string $pass pass through to mysqli - DB password
     * @param string $db   pass through to mysqli - DB name
     * @param string $port pass through to mysqli
     * @param string $sock pass through to mysqli
     * @param string $tabl table prefix
     * @param bool   $log  whether to enable query logging
     */
    public function __construct($host = null, $user = null, $pass = null,
                                $db = null, $port = null, $sock = null,
                                $tabl = '', $log = false) {

    }

    /**
     * Destructor that closes connection
     */
    public function __destruct() {

    }

    /**
     * Prevents double closing
     *
     * @return void
     */
    public function close() {

    }

    /**
     * wrapper for mysqli::query() that logs queries
     *
     * @param string $query Query to run
     *
     * @return mixed Passes return value from mysqli::query()
     */
    public function query($query) {

    }

    /**
     * wrapper for mysqli::query() that returns an array of rows
     *
     * @param string $query    Query to run
     * @param string $keyField Name of field to index returned array by.
     *
     * @return array Array of rows returned by query
     */
    public function queryToArray($query, $keyField = null) {

    }

    /**
     * return logged queries
     *
     * @return array query log
     */
    public function getQueries() {

    }

    /**
     * getter for read-only properties
     *
     * @param string $k property to get
     *
     * @return mixed requested property value
     */
    public function __get($k) {
        switch ($k) {
            case 'tabl':
                return self::$_tabl;
            case 'table':
                return self::$_tabl;
            case 'log':
                return self::$_log;
            case 'open':
                return $this->_open;
            default:
                // $d = debug_backtrace();
                // trigger_error('Undefined property via __get(): '.$k
                //   .' in '.$d[0]['file'].' on line '
                //   .$d[0]['line'], E_USER_NOTICE);
                return null;
        }
    }
}
