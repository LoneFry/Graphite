<?php
/*****************************************************************************
 * Project     : OverNightBDC 2
 *                Automotive Lead Response
 * Created By  : Tyler Uebele, OvernightBDC, LLC
 *                tyler.uebele@bettercarpeople.com
 * License     : Copyright OvernightBDC, LLC
 *
 * File        : /^/lib/Report.php
 *                Base Class for Report Models
 ****************************************************************************/

require_once LIB.'/DataModel.php';

/**
 * Report class - For reporting that is not conducive to Active Record Model
 */
abstract class Report extends DataModel {
	/**
	 * resulting data produced by load()
	 */
	protected $_data   = array();

	/**
	 * OFFSET of query result set
	 */
	protected $_start  = 0;

	/**
	 * LIMIT of query result set
	 */
	protected $_count  = 10000;

	/**
	 * ORDER BY of query
	 * must be in $this->_orders array
	 */
	protected $_order  = null;

	/**
	 * whitelist of valid ORDER BY values
	 */
	protected $_orders = array();

	/**
	 * constructor accepts three prototypes:
	 * __construct(true) will create an instance with default values
	 * __construct(array()) will create an instance with supplied values
	 * __construct(array(),true) will create a instance with supplied values
	 */
	public function __construct($a=null, $b=null) {
		if (!isset(static::$query) || '' == static::$query) {
			throw new Exception('Report class defined with no query.');
		}
		parent::__construct($a, $b);
	}

	/**
	 * run the report query with defined params and set results in $this->_data
	 */
	public function load() {
		$this->_data = array();
		$query = array();
		foreach (static::$vars as $k =>$v) {
			if (isset($this->vals[$k]) && null != $this->vals[$k]) {
				$query[] = sprintf($v['sql'],
									G::$m->escape_string($this->vals[$k]));
			}
		}
		if (count($query) == 0) {
			$query = sprintf(static::$query, '1');
		} else {
			$query = sprintf(static::$query, implode(' AND ', $query));
		}

		//if an order has been set, add it to the query
		if (null!==$this->_order) {
			$query .= ' ORDER BY `'.$this->_order.'`';
		}

		//add limits also
		$query .= ' LIMIT '.$this->_start.', '.$this->_count;

		if (false === $result = G::$m->query($query)) {
			return false;
		}
		if (0 == $result->num_rows) {
			$result->close();
			return $this->_data;
		}
		while ($row=$result->fetch_assoc()) {
			$this->_data[] = $row;
		}
		$result->close();
		$this->onload();
	}

	/**
	 * return the report results stored in $this->_data
	 */
	public function toArray() {
		return $this->_data;
	}

	/**
	 * return the report results stored in $this->_data, as a JSON packet
	 */
	public function toJSON() {
		return json_encode($this->_data);
	}

	/**
	 * filter and set query params.
	 * handle start,count,order, pass the rest upwards
	 */
	public function __set($k, $v) {
		if ('_start' == $k && is_numeric($v)) {
			return $this->_start = (int)$v;
		}
		if ('_count' == $k && is_numeric($v)) {
			return $this->_count = (int)$v;
		}
		if ('_order' == $k && in_array($v, $this->_orders)) {
			return $this->_count = $v;
		}

		return parent::__set($k, $v);
	}
}
