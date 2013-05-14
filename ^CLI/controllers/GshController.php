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
 * File        : /^CLI/controllers/GshController.php
 *                Graphite Shell Command Line Interface Controller
 ****************************************************************************/

require_once dirname(__DIR__).'/lib/CLIController.php';

class GshController extends CLIController {
	protected $action = 'sh';
	protected $role   = 'Gsh';

	/**
	 * Run the passed command in the open CLI session
	 *
	 * @param string $command The command to run, from the Gsh command prompt
	 *
	 * @return void
	 */
	protected function _cli_run($command) {
		if ('' == $command) {
			return '';
		}
		self::$_CLI['history'][] = $command;
		$argv = explode(' ', trim($command));

		if ('404' == $this->action($argv[0])) {
			$this->_println($command);
			$this->_println('Command not found: '.$argv[0]);
		} elseif ('sh' == $this->action) {
			// do nothing
		} else {
			$this->_println($command);
			if (get_class() == G::$G['CLI'][$this->action][0].'Controller') {
				$ret = $this->act($argv);
			} else {
				$C = new Dispatcher(array(
						'controller' => G::$G['CLI'][$this->action][0],
						'argv' => $argv,
						'controller404' => 'Gsh',
						));
				if ($C->controller() == G::$G['CLI'][$this->action][0]) {
					$ret = $C->Act();
				} else {
					$this->_println('Command not found: '.$this->action);
				}
			}
			if ($ret) {
				$this->_println($ret);
			}
		}
		$this->_cli_prompt();
		$r = self::$_CLI['result'];

		return $r;
	}

	/**
	 * If parent::action() returns 404, check for command in list
	 *
	 * @return void
	 */
	public function action() {
		if (0<count($a = func_get_args())) {
			if ('404' == parent::action($a[0])
				&& isset(G::$G['CLI'][$a[0]][0])
			) {
				$this->action = $a[0];
			}
		}
		return $this->action;
	}

	/**
	 * Controller action to use as entry point for Gsh
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_sh($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return $this->do_403($argv);
		}
		G::$V->_template = 'CLI.php';
		$r = '';
		$this->_cli_load();
		if (isset($_POST['prompt'])) {
			foreach (explode(';', $_POST['prompt']) as $command) {
				$r .= $this->_cli_run($command);
			}
		}
		$this->_cli_save();
		if (isset($_GET['a'])) {
			die($r);
		}

		G::$V->_script('/^CLI/js/CLI.js');
		G::$V->_link('stylesheet', 'text/css', '/^CLI/css/CLI.css');

		$refreshers = array();
		foreach (G::$G['CLI'] as $k => $v) {
			if (isset($v[2]) && $v[2]) {
				$refreshers[] = $k;
			}
		}
		G::$V->refreshers = $refreshers;
	}

	/**
	 * Controller action to clear the CLI buffer
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_clear($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return $this->do_403($argv);
		}
		self::$_CLI['buffer'] = '';
		$this->_println('Graphite Shell (Gsh) Command Line Interface');
		$this->_println('Type `help` for a list of commands.');
	}

	/**
	 * Controller action to print the date
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_date($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return $this->do_403($argv);
		}
		$this->_println(date('r'));
	}

	/**
	 * Controller action to echo inputs
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_echo($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return $this->do_403($argv);
		}
		array_shift($argv);
		$this->_println(implode(' ', $argv));
	}

	/**
	 * Controller action to print a help message
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_help($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return $this->do_403($argv);
		}
		$this->_println('Graphite Shell (Gsh) Command Line Interface');
		$this->_println();
		$this->_println('Available Commands:');

		foreach (G::$G['CLI'] as $k => $v) {
			$this->_println('  '.str_pad($k, 7).' - '.$v[1]);
		}
	}

	/**
	 * Controller action to print the arguments array as recieved and parsed
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_argv($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return $this->do_403($argv);
		}
		$this->_println(print_r($argv, 1));
	}

	/**
	 * Controller action for non-existent commands
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_404($argv=array()) {
		if (!G::$S->roleTest($this->role)) {
			return parent::do_404($argv);
		}
		$this->_println('Command not found: '.$this->action());
	}

	/**
	 * Controller action for unauthorized sessions
	 *
	 * @param array $argv Argument list passed from Dispatcher
	 *
	 * @return void
	 */
	public function do_403($argv=array()) {
		if (isset($_GET['a'])) {
			if (!G::$S->Login) {
				$this->_println('Your session has expired.  Log in and try again.');
			} elseif (!G::$S->roleTest($this->role)) {
				$this->_println('You are not authorized to run commands in this shell.');
			}
			exit;
		} else {
			if (!G::$S->roleTest($this->role)) {
				return parent::do_403($argv);
			}
		}
	}
}
