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
 * File        : /^/lib/mysqli_.php
 *                mysqli query-logging wrapper
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

/* 
 * mysqli_ class - extend mysqli to add querylogging
 */
class mysqli_ extends mysqli {
	//to log the queries
	private static $aQueries=array(array(0));
	//common prefix used by app tables, for reference
	private static $tabl='';
	//whether to log
	private static $log=false;
	
	public function __construct($host,$user,$pass,$db,$port=null,$sock=null,$tabl='',$log=false){
		parent::__construct($host,$user,$pass,$db,$port,$sock);
		self::$tabl=$this->escape_string($tabl);
		self::$log=(bool)$log;
	}
	public function query($query){
		if(false===self::$log){
			return parent::query($query);
		}
		//start time
		$t=microtime(true);
		//Call mysqli's query() method
		$result=parent::query($query);
		//[0][0] totals the time of all queries
		self::$aQueries[0][0]+=$t=microtime(true)-$t;
		//get the last few functions on the call stack
		$cf=debug_backtrace();
		//assemble log: query time, query, call stack, rows affected/selected
		$s=$cf[0]['file'].':'.$cf[0]['line'].':';
		for($i=1;$i < count($cf);$i++)$s.=$cf[$i]['function'].' - ';
		$t=array($t,$query,$s,$this->affected_rows);
		//if there was an error, log that too
		if(''!=$this->error)$t[]=$this->error;
		if($this->errno)$t[]=$this->errno;
		//append to log
		self::$aQueries[]=$t;
		//return result as normal
		return $result;
	}
	public function getQueries(){return self::$aQueries;}
	
	public function __get($k){
		switch($k){
			case 'tabl':return self::$tabl;
			case 'log':return self::$log;
			default:
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
				return null;
		}
	}
} 
?>