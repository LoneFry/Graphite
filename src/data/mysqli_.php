<?php
/**
 * mysqli_ - mysqli query-logging wrapper
 * File : /^/lib/mysqli_.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core\data;

use \mysqli;
use Graphite\core\G;
use Graphite\core\Profiler;

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

    /** @var bool Whether connection has readonly credentials */
    public $readonly = false;

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
        if (method_exists(parent::class, __FUNCTION__)) {
            parent::{__FUNCTION__}();
        }
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
     * Returns true if a connection is open
     *
     * @return bool
     */
    public function isOpen() {
        return $this->_open;
    }

    /**
     * Wrapper for mysqli::escape_string() that checks our open status
     *
     * @param string $escapestr String to escape
     *
     * @return string Escaped string
     */
    public function escape_string($escapestr) {
        if (false === $this->_open) {
            return false;
        }

        return parent::escape_string($escapestr);
    }

    /**
     * Wrapper for mysqli::query() that logs queries
     *
     * @param string $query      Query to run
     * @param int    $resultMode See mysqli::query()
     *
     * @return mixed Passes return value from mysqli::query()
     */
    public function query($query, $resultMode = \MYSQLI_STORE_RESULT) {
        if (false === $this->_open) {
            return false;
        }
        // If we're flagged readonly, just don't bother with DML
        // THIS IS NOT A SECURITY FEATURE, DO NOT RELY ON IT FOR SECURITY
        $skipQuery = $this->readonly
            && !in_array(strtolower(substr(ltrim($query), 0, 6)), ['select', 'explai', 'descri', 'show t']);
        if (!self::$_log) {
            return $skipQuery ? false : parent::query($query, $resultMode);
        }

        // get the last few functions on the call stack
        $trace = debug_backtrace();
        // assemble call stack
        $stack = $trace[0]['file'].':'.$trace[0]['line'];
        if (isset($trace[1])) {
            $stack .= ' - '.(
                isset($trace[1]['class'])
                    ? (isset($trace[1]['object'])
                        ? get_class($trace[1]['object'])
                        : $trace[1]['class']
                    ).$trace[1]['type']
                    : ''
                ).$trace[1]['function'];
        }
        // query as sent to database
        $query_stacked = '/* '.$this->escape_string(substr($stack, strrpos($stack, '/'))).' */ '.$query;

        if ($skipQuery) {
            $result = false;
            $time = '-';
        } else {
            // Start Profiler for 'query'
            Profiler::getInstance()->mark(__METHOD__);
            // start time
            $time = microtime(true);
            // Call mysqli's query() method, with call stack in comment
            $result = parent::query($query_stacked, $resultMode);
            // [0][0] totals the time of all queries
            self::$_aQueries[0][0] += $time = microtime(true) - $time;
            // Pause Profiler for 'query'
            Profiler::getInstance()->stop(__METHOD__);
        }
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
            'time'       => $time,
            'error'      => '',
            'errno'      => '',
            'stack'      => $stack,
            'rows'       => $result == false ? 0 : $this->affected_rows,
            '$host_info' => $this->host_info,
            'query'      => $query,
        );
        // if there was an error, log that too
        if ($this->errno) {
            $log['error'] = $this->error;
            $log['errno'] = $this->errno;
            // report error on PHP error log
            // unless it's a read-only mode error, we don't care about those.
            if (self::$_log >= 2
                && !(1290 == $this->errno
                    && 'The MySQL server is running with the --read-only' == substr($this->error, 0, 48))
            ) {
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
        $row = $result->fetch_assoc();
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

    /**
     * Build and return a MySQL connection object for the specified source
     *
     * @param string $source name of source in the configs
     *
     * @return mysqli_|null
     */
    public static function buildForSource($source) {
        if ($source !== 'default'
            && isset(G::$G['db'][$source]['host'])
            && isset(G::$G['db'][$source]['user'])
            && isset(G::$G['db'][$source]['pass'])
            && isset(G::$G['db'][$source]['name'])
        ) {
            $MySql = G::build(
                self::class,
                G::$G['db'][$source]['host'],
                G::$G['db'][$source]['user'],
                G::$G['db'][$source]['pass'],
                G::$G['db'][$source]['name'],
                null,
                null,
                G::$G['db']['tabl'],
                G::$G['db']['log']
            );
            if ($MySql->isOpen()) {
                return $MySql;
            }
            trigger_error('Falling back to primary on secondary db query.');
        }

        // Fail
        return null;
    }
}
