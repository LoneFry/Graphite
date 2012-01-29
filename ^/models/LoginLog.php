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
 * File        : /^/models/LoginLog.php
 *                AR class for submissions to the Contact form
 ****************************************************************************/

require_once LIB.'/Record.php';

/**
 * LoginLog class - for managing site users, including current user.
 * see Record.php for details.
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
