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
class mysqli_ extends mysqli {
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
    public function __construct($host, $user, $pass, $db, $port = null,
                                $sock = null, $tabl = '', $log = false) {
        parent::__construct($host, $user, $pass, $db, $port, $sock);
        if (!mysqli_connect_error()) {
            $this->_open = true;
            self::$_tabl = $this->escape_string($tabl);
        }
        self::$_log = $log;
    }

    /**
     * Destructor that closes connection
     */
    public function __destruct() {
        $this->close();
        // mysqli::__destruct does not exist, yet...
        method_exists('mysqli', '__destruct') && parent::__destruct();
    }

    /**
     * Prevents double closing
     *
     * @return void
     */
    public function close() {
        if ($this->_open) {
            parent::close();
            $this->_open = false;
        }
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
            return parent::query($query);
        }

        // get the last few functions on the call stack
        $d = debug_backtrace();
        // assemble call stack
        $s = $d[0]['file'].':'.$d[0]['line'];
        if (isset($d[1])) {
            $s .= ' - '.(isset($d[1]['class'])?$d[1]['class'].$d[1]['type']:'').$d[1]['function'];
        }
        // query as sent to database
        $q = '/* '.$this->escape_string(substr($s, strrpos($s, '/'))).' */ '.$query;

        // start time
        $t = microtime(true);
        // Call mysqli's query() method, with call stack in comment
        $result = parent::query($q);
        // [0][0] totals the time of all queries
        self::$_aQueries[0][0] += $t = microtime(true)-$t;

        // finish assembling the call stack
        for ($i = 2; $i < count($d); $i++) {
            $s .= ' - '.(isset($d[$i]['class'])?$d[$i]['class'].$d[$i]['type']:'').$d[$i]['function'];
        }
        // assemble log: query time, query, call stack, rows affected/selected
        $t = array($t, $query, $s, $this->affected_rows);
        // if there was an error, log that too
        if ($this->errno) {
            $t[] = $this->error;
            $t[] = $this->errno;
            // report error on PHP error log
            if (self::$_log >= 2) {
                trigger_error(print_r($t, 1));
            }
        }
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
        // If query fails, return false
        if (false === $result = $this->query($query)) {
            return false;
        }

        // If query returns no rows, return empty array
        if (0 == $this->affected_rows) {
            $result->close();

            return array();
        }

        // We have rows, fetch them all into a new array to return
        $data = array();
        // Get the first row to verify the keyField
        $row  = $result->fetch_assoc();
        if (null !== $keyField && !isset($row[$keyField])) {
            trigger_error('Invalid keyField specified in '.__METHOD__.', falling back to numeric indexing');
            $keyField = null;
        }
        if (null !== $keyField) {
            do {
                $data[$row[$keyField]] = $row;
            } while ($row = $result->fetch_assoc());
        } else {
            do {
                $data[] = $row;
            } while ($row = $result->fetch_assoc());
        }
        $result->close();

        return $data;
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
                $d = debug_backtrace();
                trigger_error('Undefined property via __get(): '.$k.' in '.$d[0]['file'].' on line '.$d[0]['line'], E_USER_NOTICE);
                return null;
        }
    }
}
