<?php
/**
 * Record - core database active record class file
 * File : /^/lib/Record.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once LIB.'/DataModel.php';

/**
 * Record class - used as a base class for Active Record Model classes
 *  an example extension is at bottom of file
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 * @see      /^/lib/DataModel.php
 */
abstract class Record extends DataModel {
	protected $DBvals = array();//instance DB values of vars defined in $vars

	//Should be defined in subclasses
	//protected static $table;//name of table
	//protected static $pkey;//name of primary key column
	//protected static $vars=array();//record definition

	/**
	 * constructor accepts four prototypes:
	 * Record(true) will create an instance with default values
	 * Record(int) will create an instance with pkey set to int
	 * Record(array()) will create an instance with supplied values
	 * record(array(),true) will create a record with supplied values
	 *
	 * @param bool|int|array $a pkey value|set defaults|set values
	 * @param bool           $b set defaults
	 */
	public function __construct($a = null, $b = null) {
		// Ensure that a pkey is defined in subclasses
		if (!isset(static::$pkey) || !isset(static::$vars[static::$pkey])) {
			throw new Exception('Record class defined with no pkey, or pkey not registered');
		}
		if (!isset(static::$table)) {
			throw new Exception('Record class defined with no table');
		}

		//initialize the values arrays with null values as some tests depend
		foreach (static::$vars as $k => $v) {
			$this->DBvals[$k] = $this->vals[$k] = null;
		}

		// This fakes constructor overriding
		if (true === $a) {
			$this->defaults();
		} elseif (is_numeric($a)) {
			$this->setAll(array(static::$pkey => $a));
		} else {
			if (true === $b) {
				$this->defaults();
			}
			if (is_array($a)) {
				$this->setAll($a);
			}
		}
	}

	/**
	 * A suitable default static prime() function to prime the $table & $query
	 * if the subclass has not defined its query, build one from the field list
	 * ::prime() should be called immediately after extending class definition
	 *
	 * @return void
	 */
	public static function prime() {
		//Set the class table name by prepending the configured prefix
		static::$table = G::$M->tabl.static::$table;

		//Set the query that would be used by load()
		if ('' == static::$query) {
			$keys = array_keys(static::$vars);
			static::$query = 'SELECT t.`'.join('`, t.`', $keys).'` FROM `'.static::$table.'` t';
		}
	}

	/**
	 * return the pkey, which is a protected static var
	 *
	 * @return string Model's primary key
	 */
	public static function getPkey() {
		return static::$pkey;
	}

	/**
	 * return the table, which is a protected static var
	 *
	 * @return string Model's table name
	 */
	public static function getTable() {
		return static::$table;
	}

	/**
	 * return array of values changed since last DB load/save
	 *
	 * @return array Changed values
	 */
	public function getDiff() {
		$diff = array();
		foreach (static::$vars as $k => $v) {
			if ($this->vals[$k] != $this->DBvals[$k]
				|| (null  === $this->vals[$k]) != (null  === $this->DBvals[$k])
				|| (true  === $this->vals[$k]) != (true  === $this->DBvals[$k])
				|| (false === $this->vals[$k]) != (false === $this->DBvals[$k])
			) {
				$diff[$k] = $this->vals[$k];
			}
		}
		return $diff;
	}

	/**
	 * Override this function to perform custom actions AFTER load
	 *
	 * @param array $row unregistered values selected in load()
	 *
	 * @return void
	 */
	public function onload($row = array()) {
	}

	/**
	 * "load" object from array, sets DBvals as if loaded from database
	 *  if pkey is not passed, fail
	 *
	 * @param array $row values
	 *
	 * @return mixed array of unregistered values on success, false on failure
	 */
	public function load_array($row) {
		if (!isset($row[static::$pkey]) || null === $row[static::$pkey]) {
			return false;
		}
		$row = $this->setAll($row, false);
		foreach (static::$vars as $k => $v) {
			$this->DBvals[$k] = $this->vals[$k];
		}
		$this->onload($row);
		return $row;
	}

	/**
	 * load object from database
	 *  if pkey is not set, assume fill(), else select()
	 *
	 * @return mixed array of unregistered values on success, false on failure
	 */
	public function load() {
		if (null === $this->vals[static::$pkey]) {
			return $this->fill();
		}
		return $this->select();
	}

	/**
	 * SELECT the record from the database using static::$query
	 * use sprintf() to embed the registered pkey
	 * returns values selected that are not registered variables, typ. array()
	 *
	 * @return mixed array of unregistered values on success, false on failure
	 */
	public function select() {
		// Fail if pkey has no value
		if (null === $this->vals[static::$pkey]) {
			return false;
		}

		// embed pkey value into instance SELECT query, then run
		$query=static::$query." WHERE t.`".static::$pkey."`='%d'";
		$query=sprintf($query, $this->vals[static::$pkey]);
		if (false === $result=G::$m->query($query)) {
			return false;
		}
		if (0 == $result->num_rows) {
			$result->close();
			return false;
		}
		$row = $result->fetch_assoc();
		$result->close();

		//data from DB should be filtered with setall to ensure specific types
		$this->setAll($row);
		foreach (static::$vars as $k => $v) {
			$this->DBvals[$k] = $this->vals[$k];
			unset($row[$k]);
		}
		$this->onload($row);
		return $row;
	}

	/**
	 * SELECT the record from the database using static::$query
	 * add all set values to the WHERE clause, otherwise like load()
	 *
	 * @return mixed array of unregistered values on success, false on failure
	 */
	public function fill() {
		// embed pkey value into instance SELECT query, then run
		$query = '';
		foreach (static::$vars as $k => $v) {
			if (null !== $this->vals[$k]) {
				if ('b' == static::$vars[$k]['type']) {
					$query .= " AND t.`$k`=".($this->vals[$k]?'1':'0');
				} else {
					$query .= " AND t.`$k`='".G::$m->escape_string($this->vals[$k])."'";
				}
			}
		}

		//if no fields were set, return false
		if ('' == $query) {
			return null;
		}

		$query = static::$query." WHERE ".substr($query, 4)
			.' GROUP BY `'.static::$pkey.'`'
			.' LIMIT 1';
		if (false === $result = G::$m->query($query)) {
			return false;
		}
		if (0 == $result->num_rows) {
			$result->close();
			return false;
		}
		$row = $result->fetch_assoc();
		$result->close();

		//data from DB should be filtered with setall to ensure specific types
		$this->setAll($row);
		foreach (static::$vars as $k => $v) {
			$this->DBvals[$k] = $this->vals[$k];
			unset($row[$k]);
		}
		$this->onload($row);
		return $row;
	}

	/**
	 * SELECT all the records from the database using static::$query
	 * add all set values to the WHERE clause, returns collection
	 *
	 * @param int $count LIMIT - number of rows to SELECT
	 * @param int $start OFFSET - number of rows to skip
	 * @param int $order ORDER BY - column to sort query by
	 * @param int $desc  DESC/ASC - true for DESC ordering
	 *
	 * @return array collection of objects found in search
	 */
	public function search($count = null, $start = 0, $order = null, $desc = false) {
		// embed pkey value into instance SELECT query, then run
		$query = '';
		foreach (static::$vars as $k => $v) {
			if (null !== $this->vals[$k]) {
				if ('b' == static::$vars[$k]['type']) {
					$query .= " AND t.`$k`=".($this->vals[$k]?'1':'0');
				} else {
					$query .= " AND t.`$k`='".G::$m->escape_string($this->vals[$k])."'";
				}
			}
		}

		//if no fields were set, return false
		if ('' == $query && $count == null) {
			return null;
		}

		$query = static::$query." WHERE 1 ".$query
			.' GROUP BY `'.static::$pkey.'`'
			.(null !== $order && array_key_exists($order, static::$vars) ? ' ORDER BY t.`'.$order.'` '.($desc?'desc':'asc'):'')
			.('rand()'==$order ? ' ORDER BY RAND() '.($desc?'desc':'asc'):'')
			.(is_numeric($count) && is_numeric($start) ? ' LIMIT '.((int)$start).','.((int)$count):'')
			;
		if (false === $result = G::$m->query($query)) {
			return false;
		}
		$a = array();
		while ($row = $result->fetch_assoc()) {
			$a[$row[static::$pkey]] = new static();
			$a[$row[static::$pkey]]->load_array($row);
		}
		$result->close();

		return $a;
	}

	/**
	 * SELECT all the records from the database using static::$query
	 * add passed WHERE clause, returns collection
	 *
	 * @param string $where Custom WHERE clause
	 * @param int    $count LIMIT - number of rows to SELECT
	 * @param int    $start OFFSET - number of rows to skip
	 * @param int    $order ORDER BY - column to sort query by
	 * @param int    $desc  DESC/ASC - true for DESC ordering
	 *
	 * @return array collection of objects found in search
	 */
	protected static function search_where($where = "WHERE 1", $count = null, $start = 0, $order = null, $desc = false) {
		$query = static::$query.' '.$where
			.' GROUP BY `'.static::$pkey.'`'
			.(null !== $order && array_key_exists($order, static::$vars) ? ' ORDER BY t.`'.$order.'` '.($desc?'desc':'asc'):'')
			.('rand()'==$order ? ' ORDER BY RAND() '.($desc?'desc':'asc'):'')
			.(is_numeric($count) && is_numeric($start) ? ' LIMIT '.((int)$start).','.((int)$count):'')
			;
		if (false === $result = G::$m->query($query)) {
			return false;
		}
		$a = array();
		while ($row = $result->fetch_assoc()) {
			$a[$row[static::$pkey]] = new static();
			$a[$row[static::$pkey]]->load_array($row);
		}
		$result->close();

		return $a;
	}

	/**
	 * SELECT $count of the records from the database using static::$query
	 *
	 * @param int $count LIMIT - number of rows to SELECT
	 * @param int $start OFFSET - number of rows to skip
	 * @param int $order ORDER BY - column to sort query by
	 * @param int $desc  DESC/ASC - true for DESC ordering
	 *
	 * @return array collection of objects found in search
	 */
	public static function some($count = null, $start = 0, $order = null, $desc = false) {
		$query = static::$query
			.' GROUP BY `'.static::$pkey.'`'
			.(null !== $order && array_key_exists($order, static::$vars) ? ' ORDER BY t.`'.$order.'` '.($desc?'desc':'asc'):'')
			.('rand()'==$order ? ' ORDER BY RAND() '.($desc?'desc':'asc'):'')
			.(is_numeric($count) && is_numeric($start) ? ' LIMIT '.((int)$start).','.((int)$count):'')
			;
		if (false === $result = G::$m->query($query)) {
			return false;
		}
		$a = array();
		while ($row = $result->fetch_assoc()) {
			$a[$row[static::$pkey]] = new static();
			$a[$row[static::$pkey]]->load_array($row);
		}
		$result->close();

		return $a;
	}

	/**
	 * SELECT all the records from the database using static::$query
	 *
	 * @param int $order ORDER BY - column to sort query by
	 * @param int $desc  DESC/ASC - true for DESC ordering
	 *
	 * @return array collection of objects found in search
	 */
	public static function all($order = null, $desc = false) {
		return static::some(null, 0, $order, $desc);
	}

	/**
	 * SELECT all the records from the database using static::$query
	 * add passed list of ids, returns collection
	 *
	 * @param array $ids   array of numeric ids to SELECT records for
	 * @param int   $count LIMIT - number of rows to SELECT
	 * @param int   $start OFFSET - number of rows to skip
	 * @param int   $order ORDER BY - column to sort query by
	 * @param int   $desc  DESC/ASC - true for DESC ordering
	 *
	 * @return array collection of objects found in search
	 */
	public static function search_ids($ids = array(), $count = null, $start = 0, $order = null, $desc = false) {
		if (!is_array($ids)) {
			return false;
		}
		$a = array();
		foreach ($ids as $k => $v) {
			if (is_numeric($v)) {
				$a[] = $v;
			}
		}
		if (1 > count($a)) {
			return array();
		}
		return static::search_where("WHERE t.`".static::$pkey."` IN (".implode(',', $a).")", $count, $start, $order, $desc);
	}

	/**
	 * SELECT the record from the database with the specified pkey value
	 *
	 * @param int $id numeric id to SELECT record for
	 *
	 * @deprecated
	 * @see Record::byPK
	 *
	 * @return object object for specified ID
	 */
	public static function byId($id) {
		$R = new static($id);
		$R->load();
		return $R;
	}

	/**
	 * SELECT the record from the database with the specified pkey value
	 *
	 * @param int $val numeric id to SELECT record for
	 *
	 * @return bool|Record false on failure or Record object for specified PKey
	 */
	public static function byPK($val) {
		$R = new static();
		$pkey = static::$pkey;
		$R->$pkey = $val;
		if (false === $R->select()) {
			return false;
		}
		return $R;
	}

	/**
	 * commit object to database
	 *  if pkey is not set, assume INSERT query, else UPDATE
	 *
	 * @return mixed value returned by delegated method
	 */
	public function save() {
		if (null === $this->vals[static::$pkey]) {
			return $this->insert();
		}
		return $this->update();
	}

	/**
	 * Override this function to perform custom actions BEFORE insert
	 * This will not run if insert() does not commit to DB
	 *
	 * @return void
	 */
	public function oninsert() {
	}

	/**
	 * build INSERT query for set values, run and store insert_id
	 * set value detection based on DBval, null for new (unloaded) records
	 * $save flag set if any field changed, typically pkey set for insert()
	 *
	 * returns new pkey value (insert_id)
	 * (uses MySQL specific INSERT ... SET ... syntax)
	 *
	 * @return mixed new primary key value of inserted row, or false on failure
	 */
	public function insert() {
		$query = 'INSERT INTO `'.static::$table.'` SET ';
		$save = false;
		foreach (static::$vars as $k => $v) {
			if ($this->vals[$k] != $this->DBvals[$k]
				|| (null  === $this->vals[$k]) != (null  === $this->DBvals[$k])
				|| (true  === $this->vals[$k]) != (true  === $this->DBvals[$k])
				|| (false === $this->vals[$k]) != (false === $this->DBvals[$k])
			) {
				$save = true;
			}
		}
		//if save is still false, no fields were set, this is unexpected
		if (false === $save) {
			return null;
		}
		$this->oninsert();
		foreach (static::$vars as $k => $v) {
			if ($this->vals[$k] != $this->DBvals[$k]
				|| (null  === $this->vals[$k]) != (null  === $this->DBvals[$k])
				|| (true  === $this->vals[$k]) != (true  === $this->DBvals[$k])
				|| (false === $this->vals[$k]) != (false === $this->DBvals[$k])
			) {
				if ('b' == static::$vars[$k]['type']) {
					$query .= " `$k`=".($this->vals[$k]?'1':'0').',';
				} else {
					$query .= " `$k`='".G::$M->escape_string($this->vals[$k])."',";
				}
			}
		}

		$query = substr($query, 0, -1);
		if (false === G::$M->query($query)) {
			return false;
		}
		$this->vals[static::$pkey] = G::$M->insert_id;

		//Subsequent to successful DB commit, update DBvals
		foreach (static::$vars as $k => $v) {
			$this->DBvals[$k] = $this->vals[$k];
		}

		return $this->vals[static::$pkey];
	}

	/**
	 * Override this function to perform custom actions BEFORE update
	 * This will not be called if update() does not commit to DB
	 *
	 * @return void
	 */
	public function onupdate() {
	}

	/**
	 * build UPDATE query for changed values, run
	 * set value detection based on DBval, set in load()
	 * $save flag set if any field changed
	 *
	 * @return bool true on success, false on failure, null on abort
	 */
	public function update() {
		$query = 'UPDATE `'.static::$table.'` SET ';
		$save = false;
		foreach (static::$vars as $k => $v) {
			if ($this->vals[$k] != $this->DBvals[$k]
				|| (null  === $this->vals[$k]) != (null  === $this->DBvals[$k])
				|| (true  === $this->vals[$k]) != (true  === $this->DBvals[$k])
				|| (false === $this->vals[$k]) != (false === $this->DBvals[$k])
			) {
				$save = true;
			}
		}
		//if save is still false, no fields were set, this is unexpected
		if (false === $save) {
			return null;
		}
		$this->onupdate();
		foreach (static::$vars as $k => $v) {
			if ($this->vals[$k] != $this->DBvals[$k]
				|| (null  === $this->vals[$k]) != (null  === $this->DBvals[$k])
				|| (true  === $this->vals[$k]) != (true  === $this->DBvals[$k])
				|| (false === $this->vals[$k]) != (false === $this->DBvals[$k])
			) {
				if (null === $this->vals[$k]) {
					$query .= '`'.$k."`=NULL,";
				} elseif ('b' == static::$vars[$k]['type']) {
					$query .= '`'.$k.'`='.($this->vals[$k]?'1':'0').',';
				} else {
					$query .= '`'.$k."`='".G::$M->escape_string($this->vals[$k])."',";
				}
			}
		}

		$query = substr($query, 0, -1)
			." WHERE `".static::$pkey."`='".G::$M->escape_string($this->vals[static::$pkey])."' LIMIT 1";
		if (false === G::$M->query($query)) {
			return false;
		}

		//Subsequent to successful DB commit, update DBvals
		foreach (static::$vars as $k => $v) {
			$this->DBvals[$k] = $this->vals[$k];
		}

		return true;
	}

	/**
	 * Override this function to perform custom actions BEFORE delete
	 * This will not be called if update() does not commit to DB
	 *
	 * @return void
	 */
	public function ondelete() {
	}

	/**
	 * delete a record
	 *
	 * @return bool true on success, false on failure
	 */
	public function delete() {
		// Fail if pkey has no value
		if (null === $this->vals[static::$pkey]) {
			return false;
		}
		$this->ondelete();
		$query = 'DELETE FROM `'.static::$table.'` '
			." WHERE `".static::$pkey."`='".G::$M->escape_string($this->vals[static::$pkey])."' LIMIT 1";
		if (false === G::$M->query($query)) {
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
	public function __construct($a=null,$b=null) {
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
		'test_id' => 		array('type' => 'i' ,'min' => 1),
		'testCustom' =>   array('type' => 's' ,'strict' => true ,'min' => 3,'max' => 255),
		'testName' => 	array('type' => 's' ,'def' => '[Default Name]','min' => 3,'max' => 255),
		'testEnum' => 	array('type' => 'e' ,'def' => 0,'values' => array(0,1,2)),
		'testIP' => 		array('type' => 'ip'),
		'testBool' => 	array('type' => 'b'  ,'def' => false),
		'testDate' => 	array('type' => 'dt','min' => 1,'format' => 'Y-m-d H:i:s'),
		'testInt' => 		array('type' => 'i' ,'min' => 1),
		'testFloat' => 	array('type' => 'f' ,'min' => 1),
		'testBit' => 		array('type' => 'b'  ,'def' => false),
		'testEmail' => 	array('type' => 'em','def' => '')
	);

	//example custom getter/setter
	// it should be named the same as the registered variable it affects
	// it should be sure to manipulate only $this->vals[$key]
	public function testCustom() {
		if (0<count($a=func_get_args()))
		if (strlen($a[0])>=3 && preg_match('/'.self::$labelRE.'/', $a[0])) {
			$this->vals['testCustom']=substr(trim(strip_tags($a[0])),0,255);
		}
		return $this->vals['testCustom'];
	}
}
*/
