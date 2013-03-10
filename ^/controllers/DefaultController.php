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
 * File        : /^/controllers/DefaultController.php
 *                default controller, leans on Controller's defaults
 ****************************************************************************/

class DefaultController extends Controller {
	protected $action = '404';

	/**
	 * default action for handling 404 errors
	 *
	 * @param array $argv request parameters
	 *
	 * @return void
	 */
	public function do_404($argv) {
		header("HTTP/1.0 404 File Not Found");
		G::$V->_template = '404.php';
		G::$V->_title    = 'Requested Page Not Found';
	}

	/**
	 * default action for handling 500 errors
	 *
	 * @param array $argv request parameters
	 *
	 * @return void
	 */
	public function do_500($argv) {
		header("HTTP/1.0 500 Internal Server Error");
		G::$V->_template = '500.php';
		G::$V->_title    = 'Internal Server Error';
	}
}

