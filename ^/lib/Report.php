<?php
/**
 * Report - Base Class for Report Models
 * File : /^/lib/Report.php
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * Report class - For reporting that is not conducive to Active Record Model
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 * @see      /^/lib/DataModel.php
 */
abstract class Report extends DataModel {
    /** @var array resulting data produced by load() */
    protected $_data   = array();

    /** @var int OFFSET of query result set */
    protected $_start  = 0;

    /** @var int LIMIT of query result set */
    protected $_count  = 10000;

    /** @var string ORDER BY of query; must be in $this->_orders array */
    protected $_order  = null;

    /** @var array Whitelist of valid ORDER BY values */
    protected $_orders = array();

    /** @var bool ASC/DESC specifier of ORDER BY; true is ASC, false is DESC */
    protected $_asc = true;

    /** @var array $vars List of parameters, defined in subclasses */
    // protected static $vars = array();

    protected static $mySql = null;

    /**
     * Constructor accepts three prototypes:
     * __construct(true) will create an instance with default values
     * __construct(array()) will create an instance with supplied values
     * __construct(array(),true) will create a instance with supplied values
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b set defaults
     *
     * @throws Exception
     */
    public function __construct($a = null, $b = null) {
        if (!isset(static::$query) || '' == static::$query) {
            throw new Exception('Report class defined with no query.');
        }
        parent::__construct($a, $b);
    }

    /**
     * Override this function to perform custom actions AFTER load
     *
     * @return void
     */
    public function onload() {
    }

    /**
     * Run the report query with defined params and set results in $this->_data
     *
     * @return bool false on failure
     */
    public function load() {
        $this->_data = array();
        // Build the WHERE clause of the report query based on set params
        $query = sprintf(static::$query, $this->_buildWhere());

        // if an order has been set, add it to the query
        if (null !== $this->_order) {
            $query .= ' ORDER BY '.$this->_order
                .($this->_asc ? ' ASC' : ' DESC');
        }

        // add limits also
        $query .= ' LIMIT '.$this->_start.', '.$this->_count;

        // Run the query against the DB connection specified in $this->_source
        $result = $this->_runQueryOnSource($query);

        // We Failed!
        if (false === $result) {
            return false;
        }

        // Get the data into an array
        if ($result !== true) {
            while ($row = $result->fetch_assoc()) {
                $this->_data[] = $row;
            }
            $result->close();
        }

        $this->onload();
        return true;
    }

    /**
     * Run the report query with defined params and set results in $this->_data
     *
     * @return bool false on failure
     */
    public function loadCount() {
        $count = false;
        // Build the COUNT query based on set params
        $query = sprintf(static::$countQuery, $this->_buildWhere());
        // Run the query against the DB connection specified in $this->_source
        $result = $this->_runQueryOnSource($query);
        // Get the count scalar!
        if (is_object($result)) {
            $count = $result->fetch_array()[0];
            $result->close();
        }

        return $count;
    }

    /**
     * Build a WHERE clause based on set params
     *
     * @return string Query WHERE clause
     */
    protected function _buildWhere() {
        $where = array();
        foreach (static::$vars as $field => $props) {
            if (isset($this->vals[$field]) && null !== $this->vals[$field]) {
                if ('a' === $props['type']) {
                    $inList = $this->implodeArray(unserialize($this->$field));
                    $where[] = sprintf($props['sql'], $inList);
                } elseif ('b' == static::$vars[$field]['type']) {
                    $where[] = sprintf($props['sql'], $this->vals[$field] ? "b'1'" : "b'0'");
                } else {
                    $where[] = sprintf($props['sql'], G::$m->escape_string($this->vals[$field]));
                }
            }
        }
        if (count($where) == 0) {
            $where = '1';
        } else {
            $where = implode(' AND ', $where);
        }

        return $where;
    }

    /**
     * Run specified query on MySQL connection indicated in $this->_source
     *
     * @param string $query Query to run
     *
     * @return mixed MySQLi result object
     */
    protected function _runQueryOnSource($query) {
        $source = $this->getSource();
        $MySql = mysqli_::buildForSource($source);
        if (null != $MySql) {
            $result = $MySql->query($query);
        } else if ($source === 'writer') {
            $result = G::$M->query($query);
        } else {
            $result = G::$m->query($query);
        }

        return $result;
    }

    /**
     * Run the report query with defined params and returns results
     *
     * @param string $query  Query to run
     * @param array  $params Key indexed parameters to supply to the query.
     *
     * @return array|bool false on failure
     */
    public function runQuery($query = '', $params = array()) {
        // Look for all {$variable} instances and replace with actual values.
        foreach ($params as $param => $value) {
            // Implode an array value
            if (is_array($value)) {
                $value = $this->implodeArray($value);
            }

            $query = str_replace('{' . $param . '}', $value, $query);
        }

        $MySql = $this->getMySql();
        $result = $MySql->query($query);

        $data = array();
        if (!is_bool($result)) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->close();
        } else {
            $data = $result;
        }
        return $data;
    }

    /**
     * Return the report results stored in $this->_data
     *
     * @return array Report result data
     */
    public function toArray() {
        return $this->_data;
    }

    /**
     * Run report according to search params $params
     * Order results by $orders and limit results by $count, $start
     *
     * @param array $params Values to search against
     * @param array $orders Order(s) of results
     * @param int   $count  Number of rows to fetch
     * @param int   $start  Number of rows to skip
     *
     * @return array Found records
     */
    public function fetch(array $params = array(), array $orders = array(), $count = null, $start = 0) {
        if (count($params)) {
            $this->setAll($params);
        }
        if (count($orders)) {
            $fields = array_keys($orders);
            $this->_order = array_shift($fields);
            $this->_asc = array_shift($orders);
        }
        if (null !== $count) {
            $this->_count = $count;
        }
        if (null !== $start) {
            $this->_start = $start;
        }
        $this->load();

        return $this->toArray();
    }

    /**
     * Run report according to search params $params
     * Return count of rows
     *
     * @param array $params Values to search against
     *
     * @return int Found records
     */
    public function count(array $params = array()) {
        if (count($params)) {
            $this->setAll($params);
        }
        if (empty(static::$countQuery)) {
            $query = static::$query;
            static::$query = static::$countQuery;
            $this->_order = null;
            $this->_asc = true;
            $this->_count = 10000;
            $this->_start = 0;
            $this->load();
            static::$query = $query;
            return count($this->_data);
        }

        return $this->loadCount();
    }

    /**
     * Return the report results stored in $this->_data, as a JSON packet
     *
     * @return string JSON encoded report result data
     */
    public function toJSON() {
        return json_encode($this->_data);
    }

    /**
     * Filter and set query params.
     * Handle start,count,order, pass the rest upwards
     *
     * @param string $k Parameter to set
     * @param mixed  $v Value to use
     *
     * @return mixed Set Value on success, null on failure
     */
    public function __set($k, $v) {
        if ('_start' == $k) {
            if (is_numeric($v)) {
                $this->_start = (int)$v;
            }
            return $this->_start;
        }
        if ('_count' == $k) {
            if (is_numeric($v)) {
                $this->_count = (int)$v;
            }
            return $this->_count;
        }
        if ('_order' == $k) {
            if (in_array($v, $this->_orders)) {
                $this->_order = '`'.$v.'`';
            }
            return $this->_order;
        }
        if ('_asc' == $k) {
            return $this->_asc = ('asc' == $v || true === $v || 1 == $v);
        }

        return parent::__set($k, $v);
    }

    /**
     * Gets appropriate MySql Object based on source.
     * Falls back to default if it cannot open it.
     *
     * @return mixed
     */
    public function getMySql() {
        if (self::$mySql === null) {
            $source = $this->getSource();
            self::$mySql = ifset(G::$M->buildForSource($source), G::$m);
        }
        return self::$mySql;
    }

    /**
     * Implodes an array in a sql safe way
     *
     * @param array $array Array to implode
     *
     * @return string
     */
    private function implodeArray($array) {
        foreach ($array as $index => $value) {
            $array[$index] = G::$m->escape_string($value);
        }

        return "'" . implode("', '", $array) . "'";
    }
}
