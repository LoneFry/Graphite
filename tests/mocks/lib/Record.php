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

// require_once SITE.'/^/lib/DataModel.php';

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
    protected $DBvals = array();// instance DB values of vars defined in $vars

    /**
     * constructor accepts four prototypes:
     * Record(true) will create an instance with default values
     * Record(int) will create an instance with pkey set to int
     * Record(array()) will create an instance with supplied values
     * record(array(),true) will create a record with supplied values
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b set defaults
     *
     * @throws Exception
     */
    public function __construct($a = null, $b = null) {
        // initialize the values arrays with null values as some tests depend
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
        // Set the class table name by prepending the configured prefix
        static::$table = G::$M->tabl.static::$table;

        // Set the query that would be used by load()
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
        return array();
    }

    /**
     * SELECT the record from the database using static::$query
     * add all set values to the WHERE clause, otherwise like load()
     *
     * @return mixed array of unregistered values on success, false on failure
     */
    public function fill() {
        return array();
    }

    /**
     * SELECT all the records from the database using static::$query
     * add all set values to the WHERE clause, returns collection
     *
     * @param int      $count LIMIT - number of rows to SELECT
     * @param int      $start OFFSET - number of rows to skip
     * @param int      $order ORDER BY - column to sort query by
     * @param bool|int $desc  DESC/ASC - true for DESC ordering
     *
     * @return array collection of objects found in search
     */
    public function search($count = null, $start = 0, $order = null, $desc = false) {
        return array();
    }

    /**
     * SELECT all the records from the database using static::$query
     * add passed WHERE clause, returns collection
     *
     * @param string   $where Custom WHERE clause
     * @param int      $count LIMIT - number of rows to SELECT
     * @param int      $start OFFSET - number of rows to skip
     * @param int      $order ORDER BY - column to sort query by
     * @param bool|int $desc  DESC/ASC - true for DESC ordering
     *
     * @return array collection of objects found in search
     */
    protected static function search_where($where = "WHERE 1", $count = null,
        $start = 0, $order = null, $desc = false) {
        return $a;
    }

    /**
     * SELECT $count of the records from the database using static::$query
     *
     * @param int      $count LIMIT - number of rows to SELECT
     * @param int      $start OFFSET - number of rows to skip
     * @param int      $order ORDER BY - column to sort query by
     * @param bool|int $desc  DESC/ASC - true for DESC ordering
     *
     * @return array collection of objects found in search
     */
    public static function some($count = null, $start = 0, $order = null, $desc = false) {
        return array();
    }

    /**
     * SELECT all the records from the database using static::$query
     *
     * @param int      $order ORDER BY - column to sort query by
     * @param bool|int $desc  DESC/ASC - true for DESC ordering
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
     * @param array    $ids   array of numeric ids to SELECT records for
     * @param int      $count LIMIT - number of rows to SELECT
     * @param int      $start OFFSET - number of rows to skip
     * @param int      $order ORDER BY - column to sort query by
     * @param bool|int $desc  DESC/ASC - true for DESC ordering
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
        $where = "WHERE t.`".static::$pkey."` IN (".implode(',', $a).")";

        return static::search_where($where, $count, $start, $order, $desc);
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
        return true;
    }

    /**
     * Drop table from database
     *
     * @return bool
     */
    public static function drop() {
        $query = "DROP TABLE IF EXISTS `".static::$table."`";
        return G::$M->query($query);
    }

    /**
     * Create table in database
     *
     * @param bool $returnQuery If true, return query instead of running it
     *
     * @return mixed
     */
    public static function create($returnQuery = false) {
        return G::$M->query($query);
    }
}
