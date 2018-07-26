<?php
/**
 * PassiveReport - Base Class for Report Models
 * File : /^/lib/PassiveReport.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core\data;

/**
 * PassiveReport class - For reporting that runs through a DataProvider
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/DataModel.php
 */
abstract class PassiveReport extends DataModel {
    /** @var array resulting data produced by load() */
    protected $_data = array();

    /** @var int OFFSET of query result set */
    protected $_start = 0;

    /** @var int LIMIT of query result set */
    protected $_count = 10000;

    /** @var array ORDER BY of query; must be in $this->_orders array */
    protected $_orders = array();

    /** @var array Whitelist of valid ORDER BY values */
    protected $_orderables = array();

    /**
     * Override this function to perform custom actions AFTER load
     *
     * @return void
     */
    public function onload() {
    }

    /**
     * Return the model field list
     *
     * @return array Vars array representing table schema
     */
    public static function getParamList() {
        return static::$vars;
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
     * Return the report results stored in $this->_data, as a JSON packet
     *
     * @return string JSON encoded report result data
     */
    public function toJSON() {
        return json_encode($this->_data);
    }

    /**
     * Getter for _count
     *
     * @return int
     */
    public function getCount() {
        return $this->_count;
    }

    /**
     * Getter for _start
     *
     * @return int
     */
    public function getStart() {
        return $this->_start;
    }

    /**
     * Getter for _orders
     * Filters orders through orderables whitelist
     * If no orders are passed, use defaults
     *
     * @param array $orders Orders to consider
     *
     * @return array
     */
    public function getOrders(array $orders = array()) {
        if (empty($orders)) {
            $orders = $this->_orders;
        }
        foreach ($orders as $key => $val) {
            if (!in_array($key, $this->_orderables)) {
                unset($orders[$key]);
            }
        }

        return $orders;
    }

    /**
     * Set the data externally from DataProvider
     * TODO figure out how to make this not dumb
     *
     * @param array $data Data from DataProvider
     *
     * @return void
     */
    public function setData($data) {
        $this->_data = $data;
    }
}
