<?php
/**
 * Controller - Base class for all controllers
 * File : /^/lib/Controller.php
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
 * Controller class - used as a base class for MVC Controller classes
 * A trivial example extension is in /^/controllers/DefaultController.php
 * Controllers are dispatched by the Dispatcher
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Dispatcher.php
 * @see      /^/controllers/DefaultController.php
 */
abstract class Controller {
	protected $action = '';
	protected $argv   = array();

	/**
	 * Controller constructor
	 *
	 * @param array $argv request parameters
	 *
	 * @return void
	 */
	public function __construct($argv = array()) {
		if (is_array($argv)) {
			$this->argv = $argv;
			if (isset($argv[0]) && '' != $argv[0]) {
				$this->action($argv[0]);
			}
		}
	}

	/**
	 * default action for handling 403 errors
	 *
	 * @param array $argv request parameters
	 *
	 * @return mixed
	 */
	public function do_403($argv) {
		header("HTTP/1.0 403 Forbidden");
		G::$V->_template = '403.php';
		G::$V->_title    = 'Permission Denied';
	}

	/**
	 * Getter/Setter for assigning action
	 *
	 * @return string The current value of $this->action
	 */
	public function action() {
		if (0 < count($a = func_get_args())) {
			if (method_exists($this, 'do_'.$a[0])) {
				$this->action = $a[0];
			} elseif (method_exists($this, 'do_404')) {
				$this->action = '404';
			} else {
				$this->action = '';
			}
		}
		return $this->action;
	}

	/**
	 * perform previously specified action
	 *
	 * @param array $argv Arguments list to pass to action
	 *
	 * @return mixed
	 */
	public function act($argv = null) {
		if (null === $argv) {
			$argv = $this->argv;
		}
		$func = 'do_'.$this->action;
		
		// non-numeric $_GET keys override $argv keys
		foreach ($_GET as $key => $val) {
			if (!is_numeric($key)) {
				$argv[$key] = $val;
			}
		}

		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				$params = $_GET;
				break;
			case 'POST':
				$params = $_POST;
				break;
			default:
				$params = array();
				break;
		}

		$this->$func($argv, $params);

	}

	/**
	 * __set magic method
	 *
	 * @param string $name  property to set
	 * @param string $value value to use
	 *
	 * @return mixed
	 */
	function __set($name, $value) {
		switch ($name) {
			case 'action':
				return $this->action($value);
			default:
				$trace = debug_backtrace();
				trigger_error('Undefined property via __set(): '.$name.' in '
							  .$trace[0]['file'].' on line '.$trace[0]['line'],
							  E_USER_NOTICE);
		}
	}

	/**
	 * __get magic method
	 *
	 * @param string $name property to get
	 *
	 * @return mixed
	 */
	function __get($name) {
		switch ($name) {
			case 'action':
				return $this->action;
			default:
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$name.' in '
							  .$trace[0]['file'].' on line '.$trace[0]['line'],
							  E_USER_NOTICE);
		}
	}
}
