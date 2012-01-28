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

require_once LIB.'/DataModel.php';

/* 
 * Record class - used as a base class for Active Record Model classes	
 * an example extension is at bottom of file
 */
abstract class Record extends DataModel{
	protected $DBvals=array();//instance DB values of vars defined in $vars
	
	//Should be defined in subclasses
	//protected static $table;//name of table
	//protected static $pkey;//name of primary key column
	//protected static $vars=array();//record definition

	/* constructor accepts four prototypes:
	 * Record(true) will create an instance with default values
	 * Record(int) will create an instance with pkey set to int
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
			$this->DBvals[$k]=$this->vals[$k]=null;
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

		static::prime_query();
	}
	
	/* if the subclass has not defined its query, build one from the field list
	 * but emit an error to the browser to ensure this is caught and corrected
	 */
	public static function prime_query(){
		//Set the query that would be used by load()
		if(''==static::$query){
			$keys=array_keys(static::$vars);
			static::$query='SELECT t.`'.join('`, t.`',$keys).'` FROM `'.static::$table.'` t';
			G::croak(static::$query);
		}
	}
	
	/* return the pkey, which is a protected static var
	 */
	public static function getPkey(){
		return static::$pkey;
	}
	
	/* return the table, which is a protected static var
	 */
	public static function getTable(){
		return static::$table;
	}

	/**
	 * Override this function to perform custom actions AFTER load
	 * $row will be an array with unregistered values selected in load()
	 */
	public function onload($row = array()) {}

	/* load object from database
	 *  if pkey is not set, assume fill(), else select()
	 */
	public function load(){
		if(null==$this->vals[static::$pkey]){
			return $this->fill();
		}
		return $this->select();
	}
		
	/* SELECT the record from the database using static::$query 
	 * use sprintf() to embed the registered pkey
	 * returns values selected that are not registered variables, typ. array()
	 */
	public function select(){
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

		//data from DB should be filtered with setall to ensure specific types
		$this->setAll($row);
		foreach(static::$vars as $k => $v){
			$this->DBvals[$k]=$this->vals[$k];
			unset($row[$k]);
		}
		$this->onload($row);
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
				if('b'==static::$vars[$k]['type']){
					$query.=" AND t.`$k`=".($this->vals[$k]?'1':'0');
				}else{
					$query.=" AND t.`$k`='".G::$m->escape_string($this->vals[$k])."'";
				}
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

		//data from DB should be filtered with setall to ensure specific types
		$this->setAll($row);
		foreach(static::$vars as $k => $v){
			$this->DBvals[$k]=$this->vals[$k];
			unset($row[$k]);
		}
		$this->onload($row);
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
				if('b'==static::$vars[$k]['type']){
					$query.=" AND t.`$k`=".($this->vals[$k]?'1':'0');
				}else{
					$query.=" AND t.`$k`='".G::$m->escape_string($this->vals[$k])."'";
				}
			}
		}
		
		//if no fields were set, return false
		if(''==$query && $count==null){
			return null;
		}

		$query=static::$query." WHERE 1 ".$query
			.' GROUP BY `'.static::$pkey.'`'
			.(null!=$order && array_key_exists($order,static::$vars) ? ' ORDER BY t.`'.$order.'` '.($desc?'desc':'asc'):'')
			.('rand()'==$order ? ' ORDER BY RAND() '.($desc?'desc':'asc'):'')
			.(is_numeric($count) && is_numeric($start) ? ' LIMIT '.$start.','.$count:'')
			;
		if(false===$result=G::$m->query($query)){
			return false;
		}
		$a=array();
		while($row=$result->fetch_assoc()){
			$a[$row[static::$pkey]]=new static($row);
		}
		$result->close();

		return $a;
	}
	
	/* SELECT all the records from the database using static::$query 
	 * add passed WHERE clause, returns collection
	 */
	protected static function search_where($where="WHERE 1",$count=null,$start=0,$order=null,$desc=false){
		static::prime_query();

		$query=static::$query.' '.$where
			.' GROUP BY `'.static::$pkey.'`'
			.(null!=$order && array_key_exists($order,static::$vars) ? ' ORDER BY t.`'.$order.'` '.($desc?'desc':'asc'):'')
			.('rand()'==$order ? ' ORDER BY RAND() '.($desc?'desc':'asc'):'')
			.(is_numeric($count) && is_numeric($start) ? ' LIMIT '.$start.','.$count:'')
			;
		if(false===$result=G::$m->query($query)){
			return false;
		}
		$a=array();
		while($row=$result->fetch_assoc()){
			$a[$row[static::$pkey]]=new static($row);
		}
		$result->close();

		return $a;
	}
	/* SELECT all the records from the database using static::$query 
	 * add passed list of ids, returns collection
	 */
	public static function search_ids($ids=array()){
		if(!is_array($ids)){
			return false;
		}
		$a=array();
		foreach($ids as $k => $v){
			if(is_numeric($v))$a[]=$v;
		}
		if(1 > count($a)){
			return array();
		}
		return static::search_where("WHERE t.`".static::$pkey."` IN (".implode(',',$a).")");
	}
	
	/* SELECT all the records from the database using static::$query 
	 * add passed list of ids, returns collection
	 */
	public static function byId($id){
		$R=new static($id);
		$R->load();
		return $R;
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
			if ($this->vals[$k]!=$this->DBvals[$k]
				|| (null===$this->vals[$k])!=(null===$this->DBvals[$k])
				|| (true===$this->vals[$k])!=(true===$this->DBvals[$k])
				|| (false===$this->vals[$k])!=(false===$this->DBvals[$k])
			){
				$save=true;
			}
		}
		//if save is still false, no fields were set, this is unexpected
		if(false===$save){
			return null;
		}
		$this->oninsert();
		foreach(static::$vars as $k => $v){
			if ($this->vals[$k]!=$this->DBvals[$k]
				|| (null===$this->vals[$k])!=(null===$this->DBvals[$k])
				|| (true===$this->vals[$k])!=(true===$this->DBvals[$k])
				|| (false===$this->vals[$k])!=(false===$this->DBvals[$k])
			){
				if('b'==static::$vars[$k]['type']){
					$query.=" `$k`=".($this->vals[$k]?'1':'0').',';
				}else{
					$query.=" `$k`='".G::$M->escape_string($this->vals[$k])."',";
				}
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
		$query='UPDATE `'.static::$table.'` SET ';
		$save=false;
		foreach(static::$vars as $k => $v){
			if ($this->vals[$k]!=$this->DBvals[$k]
				|| (null===$this->vals[$k])!=(null===$this->DBvals[$k])
				|| (true===$this->vals[$k])!=(true===$this->DBvals[$k])
				|| (false===$this->vals[$k])!=(false===$this->DBvals[$k])
			){
				$save=true;
			}
		}
		//if save is still false, no fields were set, this is unexpected
		if(false===$save){
			return null;
		}
		$this->onupdate();
		foreach(static::$vars as $k => $v){
			if ($this->vals[$k]!=$this->DBvals[$k]
				|| (null===$this->vals[$k])!=(null===$this->DBvals[$k])
				|| (true===$this->vals[$k])!=(true===$this->DBvals[$k])
				|| (false===$this->vals[$k])!=(false===$this->DBvals[$k])
			){
				if(null===$this->vals[$k]){
					$query.='`'.$k."`=NULL,";
				}elseif('b'==static::$vars[$k]['type']){
					$query.='`'.$k.'`='.($this->vals[$k]?'1':'0').',';
				}else{
					$query.='`'.$k."`='".G::$M->escape_string($this->vals[$k])."',";
				}
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
