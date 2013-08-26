<?php
/**
 * Default Controller - leans on Controller's defaults
 * File : /^/controllers/DefaultController.php
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
 * DefaultController class - leans on Controller's defaults
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class DefaultController extends Controller {
    /** @var string Default action */
    protected $action = '404';

    /**
     * Default action for handling 404 errors
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_404($argv = array(), $request = array()) {
        header("HTTP/1.0 404 File Not Found");
        G::$V->_template = '404.php';
        G::$V->_title    = 'Requested Page Not Found';
    }

    /**
     * Default action for handling 500 errors
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_500($argv = array(), $request = array()) {
        header("HTTP/1.0 500 Internal Server Error");
        G::$V->_template = '500.php';
        G::$V->_title    = 'Internal Server Error';
    }
}
