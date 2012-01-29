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
 * File        : /^/lib/Dispatcher.php
 *                core Dispatcher
 *                dispatches Controllers to perform requested Actions
 ****************************************************************************/

class Dispatcher {
	protected $controller        = 'Default';
	protected $controllerPath    = '';
	protected $controller404     = 'Default';
	protected $controller404Path = '';
	protected $action       = '';
	protected $includePath  = array();
	protected $params       = array();

	/**
	 * Dispatcher Constructor
	 *
	 * @param array $cfg Configuration array
	 */
	function __construct($cfg) {
		//set hard default for controller paths
		$this->controllerPath = $this->controller404Path = SITE.CORE.'/controllers/';

		//Check for and validate location of Controllers
		if (isset(G::$G['includePath'])) {
			foreach (explode(';', G::$G['includePath']) as $v) {
				$s = realpath(SITE.$v.'/controllers');
				if (file_exists($s)) {
					$this->includePath[] = $s.'/';
				}
			}
		}
		if (0 == count($this->includePath)) {
			$this->includePath[] = SITE.CORE.'/controllers/';
		}

		//set config default first, incase passed path is not found
		if (isset($cfg['controller404'])) {
			$this->controller404($cfg['controller404']);
		}
		//Path based requests take priority, check for path and parse
		if (isset($cfg['path'])) {
			$a = explode('/', trim($cfg['path'], '/'));
			if (count($a) > 0) {
				$this->controller(urldecode(array_shift($a)));
			}
			if (count($a) > 0) {
				$this->action(urldecode(array_shift($a)));
			}
			$this->params = $a;//what's left of the request path

			//If we have other params, pair them up and add them to the _GET array
			//Yes, this will result in redundancy: paired and unpaired; intentional
			//I wonder if this belongs elsewhere
			if (0 < count($this->params)) {
				$a = $this->params;
				while (count($a) > 0) {
					$this->params[urldecode(array_shift($a))] = urldecode(array_shift($a));
				}
				//add params to _GET array without overriding
				$_GET = $_GET + $this->params;
			}
		} else {
			//If Path was not passed, check for individual configs
			if (isset($cfg['controller'])) {
				$this->controller($cfg['controller']);
			}
			if (isset($cfg['action'])) {
				$this->action($cfg['action']);
			}
			if (isset($cfg['params'])) {
				$this->params=$cfg['params'];
			}
		}
	}

	/**
	 * Set and return 404 controller name
	 * Verifies Controller file exists in configured location
	 *
	 * @return string name of 404 controller
	 */
	public function controller404() {
		if (0 < count($a = func_get_args())) {
			foreach ($this->includePath as $v) {
				$s = realpath($v.$a[0].'Controller.php');
				if (false !== strpos($s, $v) && file_exists($s)) {
					$this->controller404 = $a[0];
					$this->controller404Path = $v;
					break;
				}
			}
		}
		return $this->controller404;
	}

	/**
	 * Set and return controller name
	 * Verifies Controller file exists in configured location
	 *
	 * @return string name of requested controller
	 */
	public function controller() {
		if (0 < count($a = func_get_args())) {
			foreach ($this->includePath as $v) {
				$s = realpath($v.$a[0].'Controller.php');
				if (false !== strpos($s, $v) && file_exists($s)) {
					$this->controller = $a[0];
					$this->controllerPath = $v;
					break;
				} else {
					$this->controller = $this->controller404;
					$this->controllerPath = $this->controller404Path;
				}
			}
		}
		return $this->controller;
	}

	/**
	 * Set action if exists in chosen controller, else set controller to 404
	 *
	 * @return void
	 */
	public function action() {
		if (0 < count($a = func_get_args())) {
			require_once LIB.'/Controller.php';
			require_once $this->controllerPath.$this->controller.'Controller.php';
			if (method_exists($this->controller.'Controller', 'do_'.$a[0])) {
				$this->action = $a[0];
			} else {
				$this->controller = $this->controller404;
				$this->controllerPath = $this->controller404Path;
			}
		}
	}

	/**
	 * Perform specified action in specified Controller
	 *
	 * @return void
	 */
	public function Act() {
		require_once LIB.'/Controller.php';
		require_once $this->controllerPath.$this->controller.'Controller.php';
		$Controller = $this->controller.'Controller';
		$Controller = new $Controller($this->action, $this->params);
		$Controller->act($this->params);
	}
}
