<?php
/*****************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /^/models/ContactLog.php
 *                AR class for submissions to the Contact form
 ****************************************************************************/

require_once LIB.'/Record.php';

/*
 * Login class - for managing site users, including current user.
 * see Record.php for details.
 */
class ContactLog extends Record {
	protected static $table='ContactLog';
	protected static $pkey='id';
	protected static $query='SELECT t.`id`, t.`from`, t.`date`, t.`subject`, t.`to`, t.`body`, t.`IP`, t.`login_id`, t.`flagDismiss` FROM `ContactLog` t';

	public static function prime(){
		self::$table=G::$G['db']['tabl'].'ContactLog';
		self::$query='SELECT t.`id`, t.`from`, t.`date`, t.`subject`, t.`to`, t.`body`, t.`IP`, t.`login_id`, t.`flagDismiss` FROM `'.self::$table.'` t';
		self::$vars['IP']['def']=$_SERVER['REMOTE_ADDR'];
		self::$vars['date']['def']=time();
	}
	protected static $vars=array(
		'id'=>           array('type'=>'i' ,'strict'=>false,'def'=>null,'min'=>1),
		'from'=>         array('type'=>'em','strict'=>false,'def'=>null,'max'=>255),
		'date'=>         array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),
		'subject'=>      array('type'=>'s' ,'strict'=>false,'def'=>null,'max'=>255),
		'to'=>           array('type'=>'em','strict'=>false,'def'=>null,'max'=>255),
		'body'=>         array('type'=>'s' ,'strict'=>false,'def'=>null,'max'=>65535),
		'IP'=>           array('type'=>'ip','strict'=>false,'def'=>null),
		'login_id'=>     array('type'=>'i' ,'strict'=>true ,'def'=>0,'min'=>1),
		'flagDismiss'=>  array('type'=>'b' ,'strict'=>false,'def'=>0)
	);
}
ContactLog::prime();
