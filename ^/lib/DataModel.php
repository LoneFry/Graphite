<?php
/** **************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /^/lib/DataModel.php
 *                Shared Functionality of Record and Report base classes
 ****************************************************************************/

/**
 * DataModel class - used as a base class for Record and Report data classes
 */
abstract class DataModel {
	/**
	 * overrideable select query used by load()
	 */
	protected static $query;

	/**
	 * default date format
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * instance values of vars defined in $vars
	 */
	protected $vals = array();

	/**
	 * constructor accepts four prototypes:
	 * __construct(true) will create an instance with default values
	 * __construct(int) will create an instance with the pkey set to the int
	 * __construct(array()) will create an instance with supplied values
	 * __construct(array(),true) will create a instance with supplied values
	 *
	 * @param bool|int|array $a pkey value|set defaults|set values
	 * @param bool           $b set defaults
	 */
	public function __construct($a = null, $b = null) {
		//initialize the values array with null values as some tests depend
		foreach (static::$vars as $k => $v) {
			$this->vals[$k] = null;
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
	 * load object from database
	 *
	 * @return void
	 */
	public abstract function load();


	/**
	 * return an array of all registered values, checking
	 *  1. for a method specific to each var's key (name)
	 *  2. for a method specific to each var's type
	 *  3. the raw value
	 *
	 *  @return array Record values
	 */
	public function getAll() {
		$a=array();
		foreach (static::$vars as $k => $v) {
			if (method_exists($this, $k)) {
				$a[$k] = $this->$k();
			} elseif (method_exists($this, '_'.$v['type'])) {
				$func = '_'.$v['type'];
				$a[$k] = $this->$func($k);
			} else {
				$a[$k] = $this->vals[$k];
			}
		}
		return $a;
	}

	/**
	 * receive an array and set all registered values, checking
	 *  1. for a method specific to each var's key (name)
	 *  2. for a method specific to each var's type
	 * and failing otherwise
	 *
	 * @param array $a     associative array of values to set
	 * @param bool  $guard Whether to obey configured guard restrictions
	 *
	 * @return array elements which were not used
	 */
	public function setAll($a, $guard = false) {
		foreach (static::$vars as $k => $v) {
			if (!isset($a[$k])) {
				//field not passed
				continue;
			}
			if ($guard && isset($v['guard']) && $v['guard']) {
				continue;
			}
			$this->__set($k, $a[$k]);
			unset($a[$k]);
		}
		return $a;
	}

	/**
	 * set each null registered value to its registered default
	 *
	 * @return void
	 */
	public function defaults() {
		foreach (static::$vars as $k => $v) {
			if (null !== $this->vals[$k]
				|| !isset(static::$vars[$k]['def'])
				|| null === static::$vars[$k]['def']
			) {
				continue;
			}
			if (method_exists($this, $k)) {
				$this->$k(static::$vars[$k]['def']);
			} elseif (method_exists($this, '_'.$v['type'])) {
				$func = '_'.$v['type'];
				$this->$func($k, static::$vars[$k]['def']);
			} else {
				$trace = debug_backtrace();
				trigger_error('Undefined property type via defaults(): '.$k
					.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
					E_USER_NOTICE);
			}
		}
	}

	/**
	 * __set magic method called when trying to set a var which is not available
	 * this will passoff the set to
	 *  1. a method specific to the var's key (name)
	 *  2. a method specific to the var's type
	 *
	 *  @param string $k property to set
	 *  @param mixed  $v value to use
	 *
	 *  @return mixed set value on success, null on failure
	 */
	public function __set($k, $v) {
		if (null === $v) {
			return $this->vals[$k] = null;
		}
		if (method_exists($this, $k)) {
			return $this->$k($v);
		}

		//$k is a valid var, with a type?
		if (!isset(static::$vars[$k]['type'])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (!method_exists($this, '_'.static::$vars[$k]['type'])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property type via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		$func = '_'.static::$vars[$k]['type'];
		return $this->$func($k, $v);
	}

	/**
	 * __get magic method called when trying to get a var which is not available
	 * this will passoff the get to
	 *  1. a method specific to the var's key (name)
	 *  2. a method specific to the var's type
	 *
	 *  @param string $k property to get
	 *
	 *  @return mixed requested value if found, null on failure
	 */
	public function __get($k) {
		if (method_exists($this, $k)) {
			return $this->$k();
		}

		//$k is a valid var, with a val?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __get(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (!method_exists($this, '_'.static::$vars[$k]['type'])) {
			return $this->vals[$k];
		}
		$func = '_'.static::$vars[$k]['type'];
		return $this->$func($k);
	}

	/**
	 * __isset magic method restores the normal operation of isset()
	 *
	 * @param string $k property to test
	 *
	 * @return bool Return true if set, false otherwise
	 */
	public function __isset($k) {
		return array_key_exists($k, static::$vars)
			&& array_key_exists($k, $this->vals)
			&& null !== $this->vals[$k];
	}

	/**
	 * __unset magic method restores the normal operation of unset()
	 *
	 * @param string $k property to unset
	 *
	 * @return void
	 */
	public function __unset($k) {
		$this->vals[$k] = null;
	}

	/** **********************************************************************
	 * Start Type specific combined Getter/Setter functions
	 *
	 * The following group of functions receive at key, and optionally a val
	 * If the key is not registered, error and return null
	 * If a value is passed, filter it according to its registry
	 * return the value for the key, formatted if appropriate by type
	 *
	 * numeric min/max violations rejected in strict mode, clamped otherwise
	 ************************************************************************/

	/**
	 * Integers
	 * other numeric types rejected in strict mode, casted otherwise
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _i($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				if (is_numeric($v) && (int)$v == $v
					 && (!isset(static::$vars[$k]['min'])
						|| !is_numeric(static::$vars[$k]['min'])
						|| $v >= static::$vars[$k]['min'])
					 && (!isset(static::$vars[$k]['max'])
						|| !is_numeric(static::$vars[$k]['max'])
						|| $v <= static::$vars[$k]['max'])
				) {
					$this->vals[$k] = (int)$v;
				}
			} else {
				if (isset(static::$vars[$k]['min'])
					&& is_numeric(static::$vars[$k]['min'])
				) {
					$v = max($v, static::$vars[$k]['min']);
				}
				if (isset(static::$vars[$k]['max'])
					&& is_numeric(static::$vars[$k]['max'])
				) {
					$v = min($v, static::$vars[$k]['max']);
				}
				$this->vals[$k] = (int)$v;
			}
		}
		return $this->vals[$k];
	}

	/**
	 * Floats
	 * other numeric types rejected in strict mode, casted otherwise
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _f($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				if (is_numeric($v) && (float)$v == $v
					 && (!isset(static::$vars[$k]['min'])
						|| !is_numeric(static::$vars[$k]['min'])
						|| $v >= static::$vars[$k]['min'])
					 && (!isset(static::$vars[$k]['max'])
						|| !is_numeric(static::$vars[$k]['max'])
						|| $v <= static::$vars[$k]['max'])
				) {
					$this->vals[$k] = (float)$v;
				}
			} else {
				if (isset(static::$vars[$k]['min'])
					&& is_numeric(static::$vars[$k]['min'])
				) {
					$v = max($v, static::$vars[$k]['min']);
				}
				if (isset(static::$vars[$k]['max'])
					&& is_numeric(static::$vars[$k]['max'])
				) {
					$v = min($v, static::$vars[$k]['max']);
				}
				$this->vals[$k] = (float)$v;
			}
		}
		return $this->vals[$k];
	}

	/**
	 * Enumerations
	 * Unregistered values fail in strict mode, defaulted to first otherwise
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _e($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			$strict = isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict'];

			if (!isset(static::$vars[$k]['values'])
				|| !is_array(static::$vars[$k]['values'])
			) {
				$trace = debug_backtrace();
				trigger_error('Enum values not found for var: '.$k
					.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
					E_USER_NOTICE);
			} elseif (false !== $i = array_search($v,
										static::$vars[$k]['values'], $strict)
			) {
				$this->vals[$k] = static::$vars[$k]['values'][$i];
			} elseif (!$strict) {
				$this->vals[$k] = static::$vars[$k]['values'][0];
			}
		}
		return $this->vals[$k];
	}

	/**
	 * DateTimes
	 * processed as a timestamp, stored as a datestring
	 * format based on registered format, defaults to static::$dateFormat
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _dt($k) {
		if (!isset(static::$vars[$k])) {//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			if (isset(static::$vars[$k]['format'])) {
				$format = static::$vars[$k]['format'];
			} else {
				$format = static::$dateFormat;
			}
			//don't clobber passed-in typestamps
			if (!is_numeric($v)) {
				$v = strtotime($v);
			}
			$v = (int)$v;
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				if ((!isset(static::$vars[$k]['min'])
						|| !is_numeric(static::$vars[$k]['min'])
						|| $v >= static::$vars[$k]['min'])
					&& (!isset(static::$vars[$k]['max'])
						|| !is_numeric(static::$vars[$k]['max'])
						|| $v <= static::$vars[$k]['max'])
				) {
					$this->vals[$k] = date($format, $v);
				}
			} else {
				if (isset(static::$vars[$k]['min'])
					&& is_numeric(static::$vars[$k]['min'])
				) {
					$v = max($v, static::$vars[$k]['min']);
				}
				if (isset(static::$vars[$k]['max'])
					&& is_numeric(static::$vars[$k]['max'])
				) {
					$v = min($v, static::$vars[$k]['max']);
				}
				$this->vals[$k] = date($format, $v);
			}
		}
		return $this->vals[$k];
	}

	/**
	 * Timestamps
	 * min/max treated numericly
	 * Use this type when storing dates in int columns
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _ts($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			//don't clobber passed-in typestamps
			if (!is_numeric($v)) {
				$v = strtotime($v);
			}
			$v = (int)$v;
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				if ((!isset(static::$vars[$k]['min'])
						|| !is_numeric(static::$vars[$k]['min'])
						|| $v >= static::$vars[$k]['min'])
					&& (!isset(static::$vars[$k]['max'])
						|| !is_numeric(static::$vars[$k]['max'])
						|| $v <= static::$vars[$k]['max'])
				) {
					$this->vals[$k] = $v;
				}
			} else {
				if (isset(static::$vars[$k]['min'])
					&& is_numeric(static::$vars[$k]['min'])
				) {
					$v = max($v, static::$vars[$k]['min']);
				}
				if (isset(static::$vars[$k]['max'])
					&& is_numeric(static::$vars[$k]['max'])
				) {
					$v = min($v, static::$vars[$k]['max']);
				}
				$this->vals[$k] = $v;
			}
		}
		return $this->vals[$k];
	}

	/**
	 * Strings
	 * min/max applies to string length
	 *  violations rejected in strict mode, clipped otherwise
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _s($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				if ((!isset(static::$vars[$k]['min'])
						|| !is_numeric(static::$vars[$k]['min'])
						|| strlen($v) >= static::$vars[$k]['min'])
					&& (!isset(static::$vars[$k]['max'])
						|| !is_numeric(static::$vars[$k]['max'])
						|| strlen($v) <= static::$vars[$k]['max'])
				) {
					$this->vals[$k] = $v;
				}
			} else {
				if ((!isset(static::$vars[$k]['min'])
					|| !is_numeric(static::$vars[$k]['min'])
					|| strlen($v) >= static::$vars[$k]['min'])
				) {
					if (isset(static::$vars[$k]['max'])
						&& is_numeric(static::$vars[$k]['max'])
						&& strlen($v) > static::$vars[$k]['max']
					) {
						$v = substr($v, 0, static::$vars[$k]['max']);
					}
					$this->vals[$k] = $v;
				}
			}
		}
		return $this->vals[$k];
	}

	/**
	 * Emails
	 * treated like strings, but added filter for email validation
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _em($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				if ((!isset(static::$vars[$k]['min'])
						|| !is_numeric(static::$vars[$k]['min'])
						|| strlen($v) >= static::$vars[$k]['min'])
					&& (!isset(static::$vars[$k]['max'])
						|| !is_numeric(static::$vars[$k]['max'])
						|| strlen($v) <= static::$vars[$k]['max'])
				) {
					if (false !== $v = filter_var($v, FILTER_VALIDATE_EMAIL)) {
						$this->vals[$k] = $v;
					}
				}
			} else {
				if ((!isset(static::$vars[$k]['min'])
					|| !is_numeric(static::$vars[$k]['min'])
					|| strlen($v) >= static::$vars[$k]['min'])
				) {
					if (isset(static::$vars[$k]['max'])
						&& is_numeric(static::$vars[$k]['max'])
						&& strlen($v) > static::$vars[$k]['max']
					) {
						$v = substr($v, 0, static::$vars[$k]['max']);
					}
					if (false !== $v = filter_var($v, FILTER_VALIDATE_EMAIL)) {
						$this->vals[$k] = $v;
					}
				}
			}
		}
		return $this->vals[$k];
	}

	/**
	 * IP addresses
	 * for storing IPv4 addresses in 32bit int columns
	 * stored as UNSIGNED int, converted on return
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _ip($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			//support entry of converted IPs
			if (is_numeric($v)) {
				$v = long2ip($v);
			}
			if (filter_var($v, FILTER_VALIDATE_IP)) {
				$this->vals[$k] = ip2long($v);
			}
		}
		return long2ip($this->vals[$k]);
	}

	/**
	 * Boolean / Bit
	 * for storing simple yes/no // true/false values
	 * compatible with either int or bit MySQL types
	 * stored as and returned as PHP boolean
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _b($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$v = $a[1];
			if (isset(static::$vars[$k]['strict'])
				&& static::$vars[$k]['strict']
			) {
				$tmp = (1==ord($v)
					? true
					: (0==ord($v)
						? false
						: filter_var($v, FILTER_VALIDATE_BOOLEAN,
							FILTER_NULL_ON_FAILURE)
						)
					);
				if (null !== $tmp) {
					$this->vals[$k] = $tmp;
				}
			} else {
				$this->vals[$k] = 1 == ord($v)
								  || filter_var($v, FILTER_VALIDATE_BOOLEAN);
			}
		}
		return $this->vals[$k];
	}

	/**
	 * Objects
	 * stores a php object with serialize()
	 * does not verify value is an object, will serialize anything
	 * verifies serialized string does not exceed specified max length
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _o($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			// Do not serialize serialized strings
			if (is_string($a[1])
				&& ($a[1] == serialize(false) || false !== @unserialize($a[1]))
			) {
				$v = $a[1];
			} else {
				$v = serialize($a[1]);
			}
			if (!isset(static::$vars[$k]['max'])
				|| !is_numeric(static::$vars[$k]['max'])
				|| strlen($v) <= static::$vars[$k]['max']
			) {
				$this->vals[$k] = $v;
			}
		}
		return unserialize($this->vals[$k]);
	}

	/**
	 * JSON
	 * stores a value with json_encode()
	 * does not verify value, encode anything, not suitable for objects
	 * verifies encoded string does not exceed specified max length
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _j($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			// Do not serialize serialized strings
			if (is_string($a[1])
				&& ($a[1] == json_encode(null) || null !== @json_decode($a[1]))
			) {
				$v = $a[1];
			} else {
				$v = json_encode($a[1]);
			}
			if (!isset(static::$vars[$k]['max'])
				|| !is_numeric(static::$vars[$k]['max'])
				|| strlen($v) <= static::$vars[$k]['max']
			) {
				$this->vals[$k] = $v;
			}
		}
		return json_decode($this->vals[$k]);
	}

	/**
	 * Arrays
	 * stores a php array with serialize()
	 * supports whitelisting
	 * converts non-arrays to array
	 *
	 * @param string $k property to get/set
	 *
	 * @return mixed current value, if setting, resultant value
	 */
	protected function _a($k) {
		//$k is a valid var?
		if (!isset(static::$vars[$k])) {
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k
				.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],
				E_USER_NOTICE);
			return null;
		}
		if (1 < count($a = func_get_args())) {
			$strict = isset(static::$vars[$k]['strict']) && static::$vars[$k]['strict'];

			// Do not serialize serialized strings
			if (is_string($a[1]) && $a[1] == serialize(false)) {
				$v = false;
			} elseif (is_string($a[1]) && false !== $v = @unserialize($a[1])) {
				//$v = unserialize($a[1]); // set in above conditional
			} else {
				$v = $a[1];
			}
			if(!is_array($v)) {
				$v = array($v);
			}

			//IF we have a whitelist, filter supplied value
			if (isset(static::$vars[$k]['values'])
				&& is_array(static::$vars[$k]['values'])
				&& count(static::$vars[$k]['values'])
			) {
				$tmp = array();

				foreach($v as $kk => $vv) {
					if (in_array($vv, static::$vars[$k]['values'])) {
						$tmp[$kk] = $vv;
					} elseif ($strict) {
						$tmp = $this->vals[$k];
						break;
					}
				}
				$v = $tmp;
			}
			$v = serialize($v);

			//IF value does not exceed column length
			if (!isset(static::$vars[$k]['max'])
				|| !is_numeric(static::$vars[$k]['max'])
				|| strlen($v) <= static::$vars[$k]['max']
			) {
				$this->vals[$k] = $v;
			}
		}
		return $this->vals[$k];
	}

	/** **********************************************************************
	 * END Type specific combined Getter/Setter functions
	 ************************************************************************/
}
