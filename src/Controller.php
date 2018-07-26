<?php
/**
 * Controller - Base class for all controllers
 * File : /^/lib/Controller.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core;

use Graphite\core\data\IDataProvider;

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
    /** @var string Default action */
    protected $action = '';
    /** @var string Which REQUEST_METHOD to act under */
    protected $method = '';
    /** @var array Argument list passed from Dispatcher */
    protected $argv = array();
    /** @var IDataProvider $DB */
    protected $DB;
    /** @var  View $View */
    protected $View;

    const MSGID_PARAM_NAME = 'MSGID';

    /**
     * Controller constructor
     *
     * @param array         $argv Argument list passed from Dispatcher
     * @param IDataProvider $DB   DataProvider to use with Controller
     * @param View          $View Graphite View helper
     */
    public function __construct(array $argv = array(), IDataProvider $DB = null, View $View = null) {
        $this->method = $_SERVER['REQUEST_METHOD'];
        // check for header "X-HTTP-Method-Override"
        if ('POST' == $this->method && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['X-HTTP-Method-Override'])) {
                $this->method = $headers['X-HTTP-Method-Override'];
            } elseif (isset($_POST['_X-HTTP-Method-Override'])) {
                $this->method = $_POST['_X-HTTP-Method-Override'];
                unset($_POST['_X-HTTP-Method-Override']);
            }
        }

        // Set the action AFTER the method to support method actions
        if (is_array($argv)) {
            $this->argv = $argv;
            if (isset($argv[0]) && '' != $argv[0]) {
                $this->action($argv[0]);
            }
        }

        $this->DB = $DB;
        if (null === $View) {
            $View = G::build(View::class, G::$G['VIEW']);
        }
        $this->View = $View;
    }

    /**
     * default action for handling 403 errors
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_403(array $argv = array(), array $request = array()) {
        header("HTTP/1.0 403 Forbidden");
        $this->action = '403';
        $this->View->_template = '403.php';
        $this->View->_header   = 'bookends/public.header.php';
        $this->View->_footer   = 'bookends/public.footer.php';
        $this->View->_title    = 'Permission Denied';
        $this->View->setTemplate('subheader', '');

        return $this->View;
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
            } elseif (method_exists($this, $this->method.'_'.$a[0])) {
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
        // Check for request_method-specific action method
        if (method_exists($this, $this->method.'_'.$this->action)) {
            $func = $this->method.'_'.$this->action;
        } else {
            $func = 'do_'.$this->action;
        }

        // non-numeric $_GET keys override $argv keys
        foreach ($_GET as $key => $val) {
            if (!is_numeric($key)) {
                $argv[$key] = $val;
            }
        }

        switch ($this->method) {
            case 'GET':
                $params = $_GET;
                break;
            case 'POST':
                $params = $_POST;
                break;
            default:
                parse_str(php_getRawInputBody(), $params);
                $GLOBALS['_'.$this->method] = $params;
                break;
        }

        if (!empty($argv[self::MSGID_PARAM_NAME])) {
            G::loadMsg($argv[self::MSGID_PARAM_NAME]);
        }

        return $this->$func($argv, $params);
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
            case 'method':
                $this->method = $value;
                return $this->method;
            default:
                $trace = debug_backtrace();
                trigger_error('Undefined property via __set(): '.$name.' in '
                              .$trace[0]['file'].' on line '.$trace[0]['line'],
                              E_USER_NOTICE);
                break;
        }

        return null;
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
            case 'method':
                return $this->method;
            default:
                $trace = debug_backtrace();
                trigger_error('Undefined property via __get(): '.$name.' in '
                              .$trace[0]['file'].' on line '.$trace[0]['line'],
                              E_USER_NOTICE);
                break;
        }

        return null;
    }

    /**
     * Redirects page to url
     *
     * @param string $url              URL to redirect to.
     * @param bool   $retainMessageLog Flag on whether to retain the message log on redirect
     *
     * @return void
     */
    protected function _redirect($url, $retainMessageLog = true) {
        $messages = G::msg();

        if (!empty($messages) && $retainMessageLog === true) {
            $hash = G::storeMsg();
            $url = updateQueryString($url, self::MSGID_PARAM_NAME, $hash);
        }

        header("HTTP/1.1 303 See Other");
        header("Location: ".$url);
        G::close();
        die();
    }
}
