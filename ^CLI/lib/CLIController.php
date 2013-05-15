<?php
/**
 * CLI Controller - Command Line Interface Controller base class
 * File : /^CLI/lib/CLIController.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  CLI
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once LIB.'/Controller.php';

/**
 * CLIController class - Command Line Interface Controller base class
 *  Contains methods and properties of use to Controllers which
 *  expose actions to the CLI, Gsh
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
abstract class CLIController extends Controller {
	protected static $_CLI = array(
		'history' => array(),
		'buffer'  => '',
		'result'  => '',
		'loaded'  => false,
		);

	/**
	 * Load the CLI session from $_SESSION, or initialize if not found
	 *
	 * @return void
	 */
	protected function _cli_load() {
		if (self::$_CLI['loaded']) {
			return;
		}
		if (isset($_SESSION['_CLI'])) {
			self::$_CLI = $_SESSION['_CLI'];
		} else {
			$this->do_clear();
		}
		$this->_cli_prompt();
		self::$_CLI['loaded'] = true;
		self::$_CLI['result'] = '';
	}

	/**
	 * Save the CLI session to $_SESSION
	 *
	 * @return void
	 */
	protected function _cli_save() {
		$_SESSION['_CLI'] = self::$_CLI;
		G::$V->CLI_buffer = self::$_CLI['buffer'];
	}

	/**
	 * Print a prompt to the CLI buffer
	 *
	 * @return void
	 */
	protected function _cli_prompt() {
		if (substr(self::$_CLI['buffer'], -5) != '&gt; ') {
			$this->_print('&gt; ');
		}
	}

	/**
	 * Print provided string to the CLI buffer
	 *
	 * @param string $s string to print
	 *
	 * @return void
	 */
	protected function _print($s = '') {
		self::$_CLI['buffer'] = substr(self::$_CLI['buffer'], -4096).$s;
		self::$_CLI['result'] .= $s;
	}

	/**
	 * print provided string to the CLI buffer, append a newline
	 *
	 * @param string $s string to print
	 *
	 * @return void
	 */
	protected function _println($s = '') {
		self::$_CLI['buffer'] = substr(self::$_CLI['buffer'], -4096).$s."\n";
		self::$_CLI['result'] .= $s."\n";
	}
}
