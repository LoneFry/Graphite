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
 * File        : /^/lib/Record.php
 *                core database active record class file
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

/* 
 * Record class - used as a base class for Active Record Model classes
 * an example extension is at bottom of file
 */
abstract class Record {
	//protected static $table;//name of table
	//protected static $pkey;//name of primary key column
	protected static $query;//overrideable select query used by load()
	protected static $dateFormat='Y-m-d H:i:s';//default date format
	//protected static $vars=array();//record definition
	protected $vals=array();//instance values of vars defined in $vars
	protected $DBvals=array();//instance DB values of vars defined in $vars
	protected $loaded=false;//update() won't run unless this is set by load()

	/* constructor accepts three prototypes:
	 * Record(true) will create an instance with default values
	 * Record(array()) will create an instance with supplied values
	 * record(array(),true) will create a record with supplied values
	 */
	public function __construct($a=null,$b=null){
		// Ensure that a pkey is defined in subclasses
		if(!isset(static::$pkey) || !isset(static::$vars[static::$pkey])){
			throw new Exception('Record class defined with no pkey, or pkey not registered');
		}
		if(!isset(static::$table)){
			throw new Exception('Record class defined with no table');
		}

		//initialize the values arrays with null values as some tests depend
		foreach(static::$vars as $k => $v){
			$this->vals[$k]=$this->DBvals[$k]=null;
		}
		
		// This fakes constructor overriding
		if(true===$a){
			$this->defaults();
		}elseif(is_numeric($a)){
			$this->setAll(array(static::$pkey=>$a));
		}else{
			if(true===$b)$this->defaults();
			if(is_array($a))$this->setAll($a);
		}
		
		//Set the query that would be used by load()
		if(''==static::$query){
			$keys=array_keys(static::$vars);
			static::$query='SELECT t.`'.join('`, t.`',$keys).'` FROM `'.static::$table.'` t';
			G::msg(static::$query);
		}
	}
	
	/* return the pkey, which is a protected static var
	 */
	public static function getPkey(){
		return static::$pkey;
	}
	
	/* return an array of all registered values, checking 
	 *  1. for a method specific to each var's key (name)
	 *  2. for a method specific to each var's type
	 *  3. the raw value
	 */
	public function getAll(){
		$a=array();
		foreach(static::$vars as $k => $v){
			if(method_exists($this,$k)){
				$a[$k]=$this->$k();
			}elseif(method_exists($this,'_'.$v['type'])){
				$func='_'.$v['type'];
				$a[$k]=$this->$func($k);
			}else{
				$a[$k]=$this->vals[$k];
			}
		}
		return $a;
	}

	/* receive an array and set all registered values, checking
	 *  1. for a method specific to each var's key (name)
	 *  2. for a method specific to each var's type
	 * and failing otherwise
	 */
	public function setAll($a,$guard=false){
		foreach(static::$vars as $k => $v){
			if(!isset($a[$k])){
				continue;//field not passed
			}
			if($guard && isset($v['guard']) && $v['guard']){
				continue;
			}
			$this->__set($k,$a[$k]);
/*			if(method_exists($this,$k)){
				$this->$k($a[$k]);
			}elseif(method_exists($this,'_'.$v['type'])){
				$func='_'.$v['type'];
				$this->$func($k,$a[$k]);
			}else{
				$trace = debug_backtrace();
				trigger_error('Undefined property type via setAll(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			}*/
		}
	}
	
	/* set each null registered value to its registered default
	 */
	public function defaults(){
		foreach(static::$vars as $k => $v){
			if(null!=$this->vals[$k] || null==static::$vars[$k]['def']){
				continue;
			}
			if(method_exists($this,$k)){
				$this->$k(static::$vars[$k]['def']);
			}elseif(method_exists($this,'_'.$v['type'])){
				$func='_'.$v['type'];
				$this->$func($k,static::$vars[$k]['def']);
			}else{
				$trace = debug_backtrace();
				trigger_error('Undefined property type via defaults(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			}
			
//			$this->vals[$k]=static::$vars[$k]['def'];
		}
	}
	
	/*
	 * Override this function to perform custom actions AFTER load
	 */
	public function onload(){}

	/* SELECT the record from the database using static::$query 
	 * use sprintf() to embed the registered pkey
	 * returns values selected that are not registered variables, typ. array()
	 */
	public function load(){
		// Fail if pkey has no value
		if(null===$this->vals[static::$pkey]){
			return false;
		}
		
		// embed pkey value into instance SELECT query, then run
		$query=static::$query." WHERE t.`".static::$pkey."`='%d'";
		$query=sprintf($query,$this->vals[static::$pkey]);
		if(false===$result=G::$m->query($query)){
			return false;
		}
		if(0==$result->num_rows){
			$result->close();
			return false;
		}
		$row=$result->fetch_assoc();
		$result->close();

		//data from DB should not be filtered with $this->setAll($row);
		foreach(static::$vars as $k => $v){
			$this->vals[$k]=$this->DBvals[$k]=$row[$k];
			unset($row[$k]);
		}
		$this->onload();
		$this->loaded=true;
		return $row;
	}
	
	/* SELECT the record from the database using static::$query 
	 * add all set values to the WHERE clause, otherwise like load()
	 */
	public function fill(){
		// embed pkey value into instance SELECT query, then run
		$query='';
		foreach(static::$vars as $k => $v){
			if(null!==$this->vals[$k]){
				$query.=" AND t.`$k`='".G::$m->escape_string($this->vals[$k])."'";
			}
		}
		
		//if no fields were set, return false
		if(''==$query){
			return null;
		}

		$query=static::$query." WHERE ".substr($query,4)
			.' GROUP BY `'.static::$pkey.'`'
			.' LIMIT 1';
		if(false===$result=G::$m->query($query)){
			return false;
		}
		if(0==$result->num_rows){
			$result->close();
			return false;
		}
		$row=$result->fetch_assoc();
		$result->close();

		//data from DB should not be filtered with $this->setAll($row);
		foreach(static::$vars as $k => $v){
			$this->vals[$k]=$this->DBvals[$k]=$row[$k];
			unset($row[$k]);
		}
		$this->onload();
		$this->loaded=true;
		return $row;
	}
	
	/* SELECT all the records from the database using static::$query 
	 * add all set values to the WHERE clause, returns collection
	 */
	public function search($count=null,$start=0,$order=null,$desc=false){
		// embed pkey value into instance SELECT query, then run
		$query='';
		foreach(static::$vars as $k => $v){
			if(null!==$this->vals[$k]){
				$query.=" AND t.`$k`='".G::$m->escape_string($this->vals[$k])."'";
			}
		}
		
		//if no fields were set, return false
		if(''==$query && $count==null){
			return null;
		}

		$query=static::$query." WHERE 1 "
			.' GROUP BY `'.static::$pkey.'`'
			.(array_key_exists($order,static::$vars) ? ' ORDER BY '.$order.' '.($desc?'desc':'asc'):'')
			.(is_numeric($count) && is_numeric($start) ? ' LIMIT '.$start.','.$count:'')
			;
		if(false===$result=G::$m->query($query)){
			return false;
		}
		if(0==$result->num_rows){
			$result->close();
			return array();
		}
		$a=array();
		while($row=$result->fetch_assoc()){
			$a[$row[static::$pkey]]=new static($row);
		}
		$result->close();

		return $a;
	}
	
	/* commit object to database
	 *  if pkey is not set, assume INSERT query, else UPDATE
	 */
	public function save(){
		if(null==$this->vals[static::$pkey]){
			return $this->insert();
		}
		return $this->update();
	}

	/*
	 * Override this function to perform custom actions BEFORE insert
	 * This will not run if insert() does not commit to DB
	 */
	public function oninsert(){}

	/* build INSERT query for set values, run and store insert_id
	 * set value detection based on DBval, null for new (unloaded) records
	 * $save flag set if any field changed, typically pkey set for insert()
	 *
	 * returns new pkey value (insert_id)
	 * (uses MySQL specific INSERT ... SET ... syntax)
	 */
	public function insert(){
		$query='INSERT INTO `'.static::$table.'` SET ';
		$save=false;
		foreach(static::$vars as $k => $v){
			if($this->vals[$k]!=$this->DBvals[$k]){
				$save=true;
			}
		}
		//if save is still false, no fields were set, this is unexpected
		if(false===$save){
			return null;
		}
		$this->oninsert();
		foreach(static::$vars as $k => $v){
			if($this->vals[$k]!=$this->DBvals[$k]){
				$query.='`'.$k."`='".G::$M->escape_string($this->vals[$k])."',";
			}
		}
		

		$query=substr($query,0,-1);
		if(false===G::$M->query($query)){
			return false;
		}
		$this->vals[static::$pkey]=G::$M->insert_id;

		//Subsequent to successful DB commit, update DBvals
		foreach(static::$vars as $k => $v){
			$this->DBvals[$k]=$this->vals[$k];
		}

		return $this->vals[static::$pkey];
	}

	/*
	 * Override this function to perform custom actions BEFORE update
	 * This will not be called if update() does not commit to DB
	 */
	public function onupdate(){}

	/* build UPDATE query for changed values, run 
	 * set value detection based on DBval, set in load()
	 * $save flag set if any field changed
	 */
	public function update(){
		//refuse to save unload()ed records to protected data integrity
		if($this->loaded!==true){
			throw new Exception('Record class refuses to update record which was not loaded: '.get_called_class().':'.$this->vals[static::$pkey]);
		}

		$query='UPDATE `'.static::$table.'` SET ';
		$save=false;
		foreach(static::$vars as $k => $v){
			if($this->vals[$k]!=$this->DBvals[$k]){
				$save=true;
			}
		}
		//if save is still false, no fields were set, this is unexpected
		if(false===$save){
			return null;
		}
		$this->onupdate();
		foreach(static::$vars as $k => $v){
			if($this->vals[$k]!=$this->DBvals[$k]){
				$query.='`'.$k."`='".G::$M->escape_string($this->vals[$k])."',";
			}
		}

		$query=substr($query,0,-1)
			." WHERE `".static::$pkey."`='".G::$M->escape_string($this->vals[static::$pkey])."' LIMIT 1";
		if(false===G::$M->query($query)){
			return false;
		}

		//Subsequent to successful DB commit, update DBvals
		foreach(static::$vars as $k => $v){
			$this->DBvals[$k]=$this->vals[$k];
		}

		return true;
	}
	
	/*
	 * Override this function to perform custom actions BEFORE delete
	 * This will not be called if update() does not commit to DB
	 */
	public function ondelete(){}

	/* delete a record 
	 */
	public function delete(){
		// Fail if pkey has no value
		if(null===$this->vals[static::$pkey]){
			return false;
		}
		$this->ondelete();
		$query='DELETE FROM `'.static::$table.'` '
			." WHERE `".static::$pkey."`='".G::$M->escape_string($this->vals[static::$pkey])."' LIMIT 1";
		if(false===G::$M->query($query)){
			return false;
		}
		return true;
	}

	/* __set magic method called when trying to set a var which is not available
	 * this will passoff the set to
	 *  1. a method specific to the var's key (name)
	 *  2. a method specific to the var's type
	 */
	public function __set($k,$v){
		if(method_exists($this,$k)){
			return $this->$k($v);
		}
		if(!isset(static::$vars[$k]['type'])){//$k is a valid var, with a type?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(!method_exists($this,'_'.static::$vars[$k]['type'])){
			$trace = debug_backtrace();
			trigger_error('Undefined property type via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		$func='_'.static::$vars[$k]['type'];
		$this->$func($k,$v);
	}

	/* __get magic method called when trying to get a var which is not available
	 * this will passoff the get to
	 *  1. a method specific to the var's key (name)
	 *  2. a method specific to the var's type
	 */
	public function __get($k){
		if(method_exists($this,$k)){
			return $this->$k();
		}
		if(!isset(static::$vars[$k])){//$k is a valid var, with a val?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __get(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(!method_exists($this,'_'.static::$vars[$k]['type'])){
			return $this->vals[$k];
		}
		$func='_'.static::$vars[$k]['type'];
		return $this->$func($k);
	}
	
	/* __isset magic method restores the normal operation of isset()
	 */
	public function __isset($k){
		return array_key_exists($k,static::$vars) && array_key_exists($k,$this->vals) && null!=$this->vals[$k];
	}
	
	/* __unset magic method restores the normal operation of unset()
	 */
	public function __unset($k){
		$this->vals[$k]=null;
	}
	
	/*************************************************************************
	 * Start Type specific combined Getter/Setter functions
	 *
	 * The following group of functions receive at key, and optionally a val
	 * If the key is not registered, error and return null
	 * If a value is passed, filter it according to its registry
	 * return the value for the key, formatted if appropriate by type
	 *
	 * numeric min/max violations rejected in strict mode, clamped otherwise
	 ************************************************************************/
	 
	/* Integers
	 * other numeric types rejected in strict mode, casted otherwise
	 */
	protected function _i($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(static::$vars[$k]['strict']){
				if(is_numeric($v) && (int)$v==$v
					 && (!isset(static::$vars[$k]['min']) || !is_numeric(static::$vars[$k]['min']) || $v>=static::$vars[$k]['min'])
					 && (!isset(static::$vars[$k]['max']) || !is_numeric(static::$vars[$k]['max']) || $v<=static::$vars[$k]['max'])
				){
					$this->vals[$k]=(int)$v;
				}
			}else{
				if(isset(static::$vars[$k]['min']) && is_numeric(static::$vars[$k]['min']))$v=max($v,static::$vars[$k]['min']);
				if(isset(static::$vars[$k]['max']) && is_numeric(static::$vars[$k]['max']))$v=min($v,static::$vars[$k]['max']);
				$this->vals[$k]=(int)$v;
			}
		}
		return $this->vals[$k];
	}
	
	/* Floats
	 * other numeric types rejected in strict mode, casted otherwise
	 */
	protected function _f($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(static::$vars[$k]['strict']){
				if(is_numeric($v) && (float)$v==$v
					 && (!isset(static::$vars[$k]['min']) || !is_numeric(static::$vars[$k]['min']) || $v>=static::$vars[$k]['min'])
					 && (!isset(static::$vars[$k]['max']) || !is_numeric(static::$vars[$k]['max']) || $v<=static::$vars[$k]['max'])
				){
					$this->vals[$k]=(float)$v;
				}
			}else{
				if(isset(static::$vars[$k]['min']) && is_numeric(static::$vars[$k]['min']))$v=max($v,static::$vars[$k]['min']);
				if(isset(static::$vars[$k]['max']) && is_numeric(static::$vars[$k]['max']))$v=min($v,static::$vars[$k]['max']);
				$this->vals[$k]=(float)$v;
			}
		}
		return $this->vals[$k];
	}
	
	/* Enumerations
	 * Unregistered values fail in strict mode, defaulted to first otherwise
	 */
	protected function _e($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(!isset(static::$vars[$k]['values']) || !is_array(static::$vars[$k]['values'])){
				$trace = debug_backtrace();
				trigger_error('Enum values not found for var: '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			}else
	
			if(in_array($v,static::$vars[$k]['values'])){
				$this->vals[$k]=$v;
			}elseif(!static::$vars[$k]['strict']){
				$this->vals[$k]=static::$vars[$k]['values'][0];
			}
		}
		return $this->vals[$k];
	}

	/* DateTimes
	 * processed as a timestamp, stored as a datestring 
	 * format based on registered format, defaults to static::$dateFormat
	 */
	protected function _dt($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(isset(static::$vars[$k]['format'])){
				$format=static::$vars[$k]['format'];
			}else{
				$format=static::$dateFormat;
			}
			if(!is_numeric($v)){//don't clobber passed-in typestamps
				$v=strtotime($v);
			}
			$v=(int)$v;
			if(isset(static::$vars[$k]['min']))$min=strtotime(static::$vars[$k]['min']);
			if(isset(static::$vars[$k]['max']))$max=strtotime(static::$vars[$k]['max']);
			if(static::$vars[$k]['strict']){
				if((!isset($min) || $v>=$min) && (!isset($max) || $v<=$max)){
					$this->vals[$k]=date($format,$v);
				}
			}else{
				if(isset($min))$v=max($v,static::$vars[$k]['min']);
				if(isset($max))$v=min($v,static::$vars[$k]['max']);
				$this->vals[$k]=date($format,$v);
			}
		}
		return $this->vals[$k];
	}

	/* Timestamps
	 * min/max treated numericly
	 * Use this type when storing dates in int columns
	 */
	protected function _ts($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(!is_numeric($v)){//don't clobber passed-in typestamps
				$v=strtotime($v);
			}
			$v=(int)$v;
			if(isset(static::$vars[$k]['min']))$min=strtotime(static::$vars[$k]['min']);
			if(isset(static::$vars[$k]['max']))$max=strtotime(static::$vars[$k]['max']);
			if(static::$vars[$k]['strict']){
				if((!isset($min) || $v>=$min) && (!isset($max) || $v<=$max)){
					$this->vals[$k]=$v;
				}
			}else{
				if(isset($min))$v=max($v,static::$vars[$k]['min']);
				if(isset($max))$v=min($v,static::$vars[$k]['max']);
				$this->vals[$k]=$v;
			}
		}
		return $this->vals[$k];
	}
	
	/* Strings
	 * min/max applies to string length
	 *  violations rejected in strict mode, clipped otherwise
	 */
	protected function _s($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(static::$vars[$k]['strict']){
				if((!isset(static::$vars[$k]['min']) || !is_numeric(static::$vars[$k]['min']) || strlen($v)>=static::$vars[$k]['min'])
					&& (!isset(static::$vars[$k]['max']) || !is_numeric(static::$vars[$k]['max']) || strlen($v)<=static::$vars[$k]['max'])
				){
					$this->vals[$k]=$v;
				}
			}else{
				if((!isset(static::$vars[$k]['min']) || !is_numeric(static::$vars[$k]['min']) || strlen($v)>=static::$vars[$k]['min'])){
					$this->vals[$k]=isset(static::$vars[$k]['max'])?$v=substr($v,0,static::$vars[$k]['max']):$v;
				}
			}
		}
		return $this->vals[$k];
	}
	
	/* Emails
	 * treated like strings, but added filter for email validation
	 */
	protected function _em($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(static::$vars[$k]['strict']){
				if((!isset(static::$vars[$k]['min']) || !is_numeric(static::$vars[$k]['min']) || strlen($v)>=static::$vars[$k]['min'])
					&& (!isset(static::$vars[$k]['max']) || !is_numeric(static::$vars[$k]['max']) || strlen($v)<=static::$vars[$k]['max'])
				){
					if(false!==$v=filter_var($v,FILTER_VALIDATE_EMAIL)){
						$this->vals[$k]=$v;
					}
				}
			}else{
				if((!isset(static::$vars[$k]['min']) || !is_numeric(static::$vars[$k]['min']) || strlen($v)>=static::$vars[$k]['min'])){
					$v=isset(static::$vars[$k]['max'])?$v=substr($v,0,static::$vars[$k]['max']):$v;
					if(false!==$v=filter_var($v,FILTER_VALIDATE_EMAIL)){
						$this->vals[$k]=$v;
					}
				}
			}
		}
		return $this->vals[$k];
	}
	
	/* IP addresses
	 * for storing IPv4 addresses in 32bit int columns 
	 * stored as in, converted on return
	 */
	protected function _ip($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(is_numeric($v)){//support entry of converted IPs
				$v=long2ip($v);
			}
			if(filter_var($v,FILTER_VALIDATE_IP)){
				$this->vals[$k]=ip2long($v);
			}
		}
		return long2ip($this->vals[$k]);
	}
	
	/* Boolean / Bit
	 * for storing simple yes/no // true/false values
	 * compatible with either int or bit MySQL types
	 * stored as and returned as PHP boolean
	 */
	protected function _b($k){
		if(!isset(static::$vars[$k])){//$k is a valid var?
			$trace = debug_backtrace();
			trigger_error('Undefined property via __set(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
			return null;
		}
		if(1<count($a=func_get_args())){$v=$a[1];
			if(static::$vars[$k]['strict']){
				$tmp=(1==ord($v)?true:(0==ord($v)?false:filter_var($v,FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)));
				if(null!==$tmp)$this->vals[$k]=$tmp;
			}else{
				$this->vals[$k]=1==ord($v)||filter_var($v,FILTER_VALIDATE_BOOLEAN);
			}
		}
		return $this->vals[$k];
	}

	/*************************************************************************
	 * END Type specific combined Getter/Setter functions
	 ************************************************************************/
}


/*

class Test extends Record {
	//example custom class variable, used by testCustom() below
	protected static $labelRE='^\w[\w\_\-\@\.\d]*$';
	
	//override the constructor to set the class table and pkey name
	//*MUST* set static::$pkey before calling parent::__constuct()
	public function __construct($a=null,$b=null){
		static::$table='Test';
		static::$pkey='test_id';
		parent::__construct($a,$b);
	}
	
	// vars array - all the information required to work with each record field
	//  val		the current value in this object instance
	//  DBval	the current value in the database set in load()
	//  type	the type, which defines which functions operate on it
	//  strict	declare whether or reject or adjust violating values
	//  def		default value, used by defaults() to set sane default values
	//  min		lowest number, earliest date, shortest string length
	//  max		highest number, latest date, longest string length
	//  values	valid choices for an enumeration (e) type variable
	//  format	string used by PHP's date() to format DateTime (dt) values
	protected static $vars=array(
		'test_id'=>		array('type'=>'i' ,'strict'=>false,'def'=>null,'min'=>1),
		'testCustom'=>  array('type'=>'s' ,'strict'=>true ,'def'=>null,'min'=>3,'max'=>255),
		'testName'=>	array('type'=>'s' ,'strict'=>false,'def'=>'[Default Name]','min'=>3,'max'=>255),
		'testEnum'=>	array('type'=>'e' ,'strict'=>false,'def'=>0,'values'=>array(0,1,2)),
		'testIP'=>		array('type'=>'ip','strict'=>false,'def'=>null),
		'testBool'=>	array('type'=>'b' ,'strict'=>false ,'def'=>false),
		'testDate'=>	array('type'=>'dt','strict'=>false,'def'=>null,'min'=>1,'format'=>'Y-m-d H:i:s'),
		'testInt'=>		array('type'=>'i' ,'strict'=>false,'def'=>null,'min'=>1),
		'testFloat'=>	array('type'=>'f' ,'strict'=>false,'def'=>null,'min'=>1),
		'testBit'=>		array('type'=>'b' ,'strict'=>false ,'def'=>false),
		'testEmail'=>	array('type'=>'em','strict'=>false,'def'=>'')
	);
	
	//example custom getter/setter
	// it should be named the same as the registered variable it affects
	// it should be sure to manipulate only $this->vals[$key]
	public function testCustom(){
		if(0<count($a=func_get_args()))
		if(strlen($a[0])>=3 && preg_match('/'.self::$labelRE.'/', $a[0]))$this->vals['testCustom']=substr(trim(strip_tags($a[0])),0,255);
		return $this->vals['testCustom'];
	}
}
*/
