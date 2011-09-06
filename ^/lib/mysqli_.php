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
	//whether connection succeeded
	private $open=false;
	
	public function __construct($host,$user,$pass,$db,$port=null,$sock=null,$tabl='',$log=false){
		parent::__construct($host,$user,$pass,$db,$port,$sock);
		if (!mysqli_connect_error()){
			$this->open=true;
			self::$tabl=$this->escape_string($tabl);
		}
		self::$log=$log;
	}
	
	//Destructor that closes connection
	public function __destruct(){
		$this->close();
		//mysqli::__destruct does not exist, yet...
		method_exists('mysqli','__destruct') && parent::__destruct();
	}

	public function close(){
		if($this->open){
			parent::close();
			$this->open=false;
		}
	}
	
	public function query($query){
		if(!self::$log){
			return parent::query($query);
		}

		//get the last few functions on the call stack
		$d=debug_backtrace();
		//assemble call stack
		$s=$d[0]['file'].':'.$d[0]['line'];
		if(0 < count($d)){
			$s.=' - '.(isset($d[1]['class'])?$d[1]['class'].$d[1]['type']:'').$d[1]['function'];
		}
		//query as sent to database
		$q='/* '.$this->escape_string(substr($s,strrpos($s,'/'))).' */ '.$query;

		//start time
		$t=microtime(true);
		//Call mysqli's query() method, with call stack in comment
		$result=parent::query($q);
		//[0][0] totals the time of all queries
		self::$aQueries[0][0]+=$t=microtime(true)-$t;

		//finish assembling the call stack
		for($i=2;$i < count($d);$i++){
			$s.=' - '.(isset($d[$i]['class'])?$d[$i]['class'].$d[$i]['type']:'').$d[$i]['function'];
		}
		//assemble log: query time, query, call stack, rows affected/selected
		$t=array($t,$query,$s,$this->affected_rows);
		//if there was an error, log that too
		if($this->errno){
			$t[]=$this->error;
			$t[]=$this->errno;
			//report error on PHP error log
			if(self::$log >= 2){
				trigger_error(print_r($t,1));
			}
		}
		//append to log
		self::$aQueries[]=$t;
		//return result as normal
		return $result;
	}
	public function getQueries(){return self::$aQueries;}
	
	public function __get($k){
		switch($k){
			case 'tabl':return self::$tabl;
			case 'table':return self::$tabl;
			case 'log':return self::$log;
			default:
				$d=debug_backtrace();
				trigger_error('Undefined property via __get(): '.$k.' in '.$d[0]['file'].' on line '.$d[0]['line'],E_USER_NOTICE);
				return null;
		}
	}
} 
?>