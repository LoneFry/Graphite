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
    public static $_aQueries = array(array(0));

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
     * Expose for mocking.
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
        self::$_tabl = $this->escape_string($tabl);
        self::$_log = $log;
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
        if (!self::$_log) {
            // For testing, don't actually run the query
            return false; // parent::query($query);
        }

        // get the last few functions on the call stack
        $d = debug_backtrace();
        // assemble call stack
        $s = $d[0]['file'].':'.$d[0]['line'];
        if (isset($d[1])) {
            $s .= ' - '.(isset($d[1]['class']) ? $d[1]['class'].$d[1]['type'] : '').$d[1]['function'];
        }
        // query as sent to database
        $q = '/* '.$this->escape_string(substr($s, strrpos($s, '/'))).' */ '.$query;

        // start time
        $t = microtime(true);

        // Call mysqli's query() method, with call stack in comment
        // For testing, don't actually run the query
        $result = false; // parent::query($q);
        // [0][0] totals the time of all queries
        self::$_aQueries[0][0] += $t = microtime(true) - $t;

        // finish assembling the call stack
        for ($i = 2; $i < count($d); $i++) {
            $s .= ' - '.(isset($d[$i]['class']) ? $d[$i]['class'].$d[$i]['type'] : '').$d[$i]['function'];
        }
        // assemble log: query time, query, call stack, rows affected/selected
        $t = array($t, $query, $s, $this->affected_rows);

        // append to log
        self::$_aQueries[] = $t;

        // return result as normal
        return $result;
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
        // Query will always fail in this mock, return false
        if (false === $result = $this->query($query)) {
            return false;
        }
    }

    /**
     * return logged queries
     *
     * @return array query log
     */
    public function getQueries() {
        return self::$_aQueries;
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

    /**
     * Add slashes to string as a testing alternative to escape_string
     *
     * @param string $s string to alter
     *
     * @return string altered string
     */
    public function escape_string($s) {
        return addslashes($s);
    }
}
