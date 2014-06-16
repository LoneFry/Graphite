<?php
/**
 * Report - Base Class for Report Models
 * File : /^/lib/Report.php
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
        $query = array();

        foreach (static::$vars as $k  => $v) {
            if (isset($this->vals[$k]) && null !== $this->vals[$k]) {
                if ('a' === $v['type']) {
                    $arr = array();

                    $arr = unserialize($this->$k);

                    foreach ($arr as $kk => $vv) {
                        $arr[$kk] = G::$m->escape_string($vv);
                    }

                    $query[] = sprintf($v['sql'],
                                    "'".implode("', '", $arr)."'");
                } else {
                    $query[] = sprintf($v['sql'],
                                    G::$m->escape_string($this->vals[$k]));
                }
            }
        }
        if (count($query) == 0) {
            $query = sprintf(static::$query, '1');
        } else {
            $query = sprintf(static::$query, implode(' AND ', $query));
        }

        // if an order has been set, add it to the query
        if (null !== $this->_order) {
            $query .= ' ORDER BY '.$this->_order
                .($this->_asc ? ' ASC' : ' DESC');
        }

        // add limits also
        $query .= ' LIMIT '.$this->_start.', '.$this->_count;

        if (false === $result = G::$m->query($query)) {
            return false;
        }
        while ($row = $result->fetch_assoc()) {
            $this->_data[] = $row;
        }
        $result->close();
        $this->onload();
        return true;
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
     * @param array  $params Values to search against
     * @param array  $orders Order(s) of results
     * @param int    $count  Number of rows to fetch
     * @param int    $start  Number of rows to skip
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
}
