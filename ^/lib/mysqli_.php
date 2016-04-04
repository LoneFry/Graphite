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
    /** @var array Log of queries, run times, errors */
    private static $_aQueries = array(array(0));

    /** @var string Common prefix used by app tables, for reference */
    private static $_tabl = '';

    /** @var bool Whether to log */
    private static $_log = false;

    /** @var bool Whether connection succeeded */
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
     * Wrapper for mysqli::query() that logs queries
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
        $trace = debug_backtrace();
        // assemble call stack
        $stack = $trace[0]['file'].':'.$trace[0]['line'];
        if (isset($trace[1])) {
            $stack .= ' - '.(isset($trace[1]['class'])? (isset($trace[1]['object']) ? get_class($trace[1]['object'])
                        : $trace[1]['class']).$trace[1]['type']:'').$trace[1]['function'];
        }
        // query as sent to database
        $query_stacked = '/* '.$this->escape_string(substr($stack, strrpos($stack, '/'))).' */ '.$query;

        // Start Profiler for 'query'
        Profiler::getInstance()->mark(__METHOD__);
        // start time
        $time = microtime(true);
        // Call mysqli's query() method, with call stack in comment
        $result = parent::query($query_stacked);
        // [0][0] totals the time of all queries
        self::$_aQueries[0][0] += $time = microtime(true) - $time;
        // Pause Profiler for 'query'
        Profiler::getInstance()->stop(__METHOD__);

        // finish assembling the call stack
        for ($i = 2; $i < count($trace); $i++) {
            $stack .= ' - '.(
                isset($trace[$i]['class'])
                    ? (isset($trace[$i]['object'])
                        ? get_class($trace[$i]['object'])
                        : $trace[$i]['class']
                    ).$trace[$i]['type']
                    : ''
                ).$trace[$i]['function'];
        }
        // assemble log: query time, query, call stack, rows affected/selected
        $log = array(
            'time' => $time,
            'error' => '',
            'errno' => '',
            'stack' => $stack,
            'rows' => $this->affected_rows,
            'query' => $query,
        );
        // if there was an error, log that too
        if ($this->errno) {
            $log['error'] = $this->error;
            $log['errno'] = $this->errno;
            // report error on PHP error log
            if (self::$_log >= 2) {
                // @codingStandardsIgnoreStart
                trigger_error(print_r($log, 1));
                // @codingStandardsIgnoreEnd
            }
        }
        // append to log
        self::$_aQueries[] = $log;
        // return result as normal
        return $result;
    }

    /**
     * Wrapper for mysqli::query() that returns an array of rows
     *
     * @param string $query    Query to run
     * @param string $keyField Name of field to index returned array by.
     *
     * @return array|bool Array of rows returned by query|false on error
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
     * Return logged queries
     *
     * @return array query log
     */
    public function getQueries() {
        return self::$_aQueries;
    }

    /**
     * Getter for read-only properties
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
                trigger_error('Undefined property via __get(): '.$k.' in '.$d[0]['file'].' on line '.$d[0]['line'],
                    E_USER_NOTICE);

                return null;
        }
    }
}
