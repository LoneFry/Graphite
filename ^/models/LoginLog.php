<?php
/**
 * LoginLog - AR class for logging log-ins
 * File : /^/models/LoginLog.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once LIB.'/Record.php';

/**
 * LoginLog class - AR class for logging log-ins
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Record.php
 */
class LoginLog extends Record {
	protected static $table = 'LoginLog';
	protected static $pkey  = 'pkey';
	protected static $query = 'SELECT t.`pkey`, t.`login_id`, t.`ip`, t.`ua`, t.`iDate` FROM `LoginLog` t';

	/**
	 * prime() initialized static values, call below class definition
	 *
	 * @return void
	 */
	public static function prime() {
		self::$table = G::$G['db']['tabl'].'LoginLog';
		self::$query = 'SELECT t.`pkey`, t.`login_id`, t.`ip`, t.`ua`, t.`iDate` FROM `'.self::$table.'` t';
		self::$vars['ip']['def'] = $_SERVER['REMOTE_ADDR'];
		self::$vars['iDate']['def'] = NOW;
	}
	protected static $vars = array(
		'pkey' =>          array('type' => 'i' ,'min' => 1),
		'login_id' =>      array('type' => 'i' ,'min' => 0),
		'ip' =>            array('type' => 'ip'),
		'ua' =>            array('type' => 's' ,'max' => 255),
		'iDate' =>         array('type' => 'ts','min' => 0)
	);
}
LoginLog::prime();
