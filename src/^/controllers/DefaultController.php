<?php
/**
 * Default Controller - leans on Controller's defaults
 * File : /^/controllers/DefaultController.php
 *
 * PHP version 5.6
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
    protected $action = '200';

    /**
     * Default action for handling 404 errors
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_404(array $argv = array(), array $request = array()) {
        header("HTTP/1.0 404 File Not Found");
        $this->action = '404';
        $this->View->_template = '404.php';
        $this->View->_header   = 'header.php';
        $this->View->_footer   = 'footer.php';
        $this->View->_title    = 'Requested Page Not Found';
        $this->View->setTemplate('subheader', '');

        return $this->View;
    }

    /**
     * Default action for handling 500 errors
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_500(array $argv = array(), array $request = array()) {
        header("HTTP/1.0 500 Internal Server Error");
        $this->action = '500';
        $this->View->_template = '500.php';
        $this->View->_header   = 'header.php';
        $this->View->_footer   = 'footer.php';
        $this->View->_title    = 'Internal Server Error';
        $this->View->setTemplate('subheader', '');

        return $this->View;
    }

    /**
     * Default action for handling 200 OK
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_200(array $argv = array(), array $request = array()) {
        $this->action = '200';
        $this->View->_template = '200.php';
        $this->View->_header   = 'header.php';
        $this->View->_footer   = 'footer.php';
        $this->View->_title    = 'Default OK Page';
        $this->View->setTemplate('subheader', '');

        return $this->View;
    }
}
