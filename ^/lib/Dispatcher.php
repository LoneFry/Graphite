<?php
/**
 * Dispatcher - Core dispatcher - directs request to appropriate Controller
 * File : /^/lib/Dispatcher.php
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
 * Dispatcher class - dispatches Controllers to perform requested Actions
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class Dispatcher {
    protected $controller        = 'Default';
    protected $controllerPath    = '';
    protected $controller404     = 'Default';
    protected $controller404Path = '';
    protected $includePath       = array();
    protected $argv              = array();

    /**
     * Dispatcher Constructor
     *
     * @param array $cfg Configuration array
     */
    function __construct($cfg) {
        //set hard default for controller paths
        $this->controllerPath = $this->controller404Path =
            SITE.CORE.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR;

        //Check for and validate location of Controllers
        if (isset(G::$G['includePath'])) {
            foreach (explode(';', G::$G['includePath']) as $v) {
                $s = realpath(SITE.$v.'/controllers');
                if (file_exists($s) && '' != $v) {
                    $this->includePath[] = $s.DIRECTORY_SEPARATOR;
                }
            }
        }
        if (0 == count($this->includePath)) {
            $this->includePath[] = $this->controller404Path;
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
            //argv should contain the rest of the request path, action at [0]
            $this->argv = $a;
            array_shift($a);

            //If we have other argv, pair them up and add them to the _GET array
            //Yes, this will result in redundancy: paired and unpaired; intentional
            //I wonder if this belongs elsewhere
            if (0 < count($this->argv)) {
                while (count($a) > 0) {
                    $k = urldecode(array_shift($a));
                    $v = urldecode(array_shift($a));
                    //Don't let pairings overwrite existing (numeric) indexes
                    if (!isset($this->argv[$k])) {
                        $this->argv[$k] = $v;
                    }
                }
                //add argv to _GET array without overriding
                $_GET = $_GET + $this->argv;
            }
        } else {
            //If Path was not passed, check for individual configs
            if (isset($cfg['controller'])) {
                $this->controller($cfg['controller']);
            }
            if (isset($cfg['params'])) {
                $this->argv = $cfg['params'];
            }
            if (isset($cfg['action'])) {
                array_unshift($this->argv, $cfg['action']);
            }
            // passing an argv config will override the params and action configs
            if (isset($cfg['argv'])) {
                $this->argv = $cfg['argv'];
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
     * Perform specified action in specified Controller
     *
     * @param array $argv Arguments list to pass to action
     *
     * @return mixed
     */
    public function Act($argv = null) {
        if (null === $argv) {
            $argv = $this->argv;
        }
        require_once LIB.'/Controller.php';
        require_once $this->controllerPath.$this->controller.'Controller.php';
        $Controller = $this->controller.'Controller';
        $Controller = new $Controller($argv);
        if (method_exists($Controller, 'do_'.$Controller->action)) {
            return $Controller->act();
        }

        // else use 404 controller
        require_once $this->controller404Path.$this->controller404.'Controller.php';
        $Controller = $this->controller404.'Controller';
        $Controller = new $Controller($argv);
        return $Controller->act();
    }
}
