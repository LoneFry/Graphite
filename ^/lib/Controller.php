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
 * File        : /^/controllers/Controller.php
 *                Controller base class
 *
 * Controllers are dispatched by the Dispatcher
 ****************************************************************************/

/**
 * Controller class - used as a base class for MVC Controller classes
 * a trivial example extension is in /^/controllers/DefaultController.php
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
	 * @return void
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
	 * @return void
	 */
	public function act($argv = null) {
		if (null === $argv) {
			$argv = $this->argv;
		}
		$func = 'do_'.$this->action;
		$this->$func($argv);
	}

	/**
	 * __set magic method
	 *
	 * @param string $name  property to set
	 * @param string $value value to use
	 *
	 * @return void
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
	 * @return void
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
