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
abstract class ActiveRecord extends PassiveRecord {
    /** @var array Instance DB values of vars defined in $vars */
    protected $DBvals = array();

    // Should be defined in subclasses
    // protected static $table;// name of table
    // protected static $pkey;// name of primary key column
    // protected static $vars = array();// record definition
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
            $keys          = array_keys(static::$vars);
            static::$query = 'SELECT t.`'.join('`, t.`', $keys).'` FROM `'.static::$table.'` t';
        }
    }

    /**
     * Override this function to perform custom actions AFTER load
     *
     * @param array $row Unregistered values selected in load()
     *
     * @return void
     */
    public function onload(array $row = array()) {
    }

    /**
     * "Load" object from array, sets DBvals as if loaded from database
     *  if pkey is not passed, fail
     *
     * @param array $row values
     *
     * @return mixed Array of unregistered values on success, false on failure
     */
    public function load_array(array $row) {
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
     * Load object from database
     *  if pkey is not set, assume fill(), else select()
     *
     * @return mixed Array of unregistered values on success, false on failure
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
     * @return mixed Array of unregistered values on success, false on failure
     */
    public function select() {
        // Fail if pkey has no value
        if (null === $this->vals[static::$pkey]) {
            return false;
        }

        // embed pkey value into instance SELECT query, then run
        $query = static::$query." WHERE t.`".static::$pkey."` = '%d'";
        $query = sprintf($query, $this->vals[static::$pkey]);
        if (false === $result = G::$m->query($query)) {
            return false;
        }
        if (0 == $result->num_rows) {
            $result->close();

            return false;
        }
        $row = $result->fetch_assoc();
        $result->close();

        // data from DB should be filtered with setall to ensure specific types
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
     * @return mixed Array of unregistered values on success, false on failure
     */
    public function fill() {
        // embed pkey value into instance SELECT query, then run
        $query = '';
        foreach (static::$vars as $k => $v) {
            if (null !== $this->vals[$k]) {
                if ('b' == static::$vars[$k]['type']) {
                    $query .= " AND t.`$k` = ".($this->vals[$k] ? '1' : '0');
                } else {
                    $query .= " AND t.`$k` = '".G::$m->escape_string($this->vals[$k])."'";
                }
            }
        }

        // if no fields were set, return false
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

        // data from DB should be filtered with setall to ensure specific types
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
     * @param int    $count LIMIT - number of rows to SELECT
     * @param int    $start OFFSET - number of rows to skip
     * @param string $order ORDER BY - column to sort query by
     * @param bool   $desc  DESC/ASC - true for DESC ordering
     *
     * @return array Collection of objects found in search
     */
    public function search($count = null, $start = 0, $order = null, $desc = false) {
        // embed pkey value into instance SELECT query, then run
        $query = '';
        foreach (static::$vars as $k => $v) {
            if (null !== $this->vals[$k]) {
                if ('b' == static::$vars[$k]['type']) {
                    $query .= " AND t.`$k` = ".($this->vals[$k] ? '1' : '0');
                } else {
                    $query .= " AND t.`$k` = '".G::$m->escape_string($this->vals[$k])."'";
                }
            }
        }

        // if no fields were set, return false
        if ('' == $query && $count == null) {
            return null;
        }

        $query = static::$query." WHERE 1 ".$query
            .' GROUP BY `'.static::$pkey.'`'
            .(null !== $order && array_key_exists($order, static::$vars)
                ? ' ORDER BY t.`'.$order.'` '.($desc ? 'desc' : 'asc')
                : '')
            .('rand()' == $order ? ' ORDER BY RAND() '.($desc ? 'desc' : 'asc') : '')
            .(is_numeric($count) && is_numeric($start)
                ? ' LIMIT '.((int)$start).','.((int)$count)
                : '');
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
     * @param string $order ORDER BY - column to sort query by
     * @param bool   $desc  DESC/ASC - true for DESC ordering
     *
     * @return array Collection of objects found in search
     */
    protected static function search_where($where = "WHERE 1", $count = null, $start = 0, $order = null, $desc = false
    ) {
        $query = static::$query.' '.$where
            .' GROUP BY `'.static::$pkey.'`'
            .(null !== $order && array_key_exists($order, static::$vars)
                ? ' ORDER BY t.`'.$order.'` '.($desc ? 'desc' : 'asc')
                : '')
            .('rand()' == $order ? ' ORDER BY RAND() '.($desc ? 'desc' : 'asc') : '')
            .(is_numeric($count) && is_numeric($start)
                ? ' LIMIT '.((int)$start).','.((int)$count)
                : '');
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
     * @param int    $count LIMIT - number of rows to SELECT
     * @param int    $start OFFSET - number of rows to skip
     * @param string $order ORDER BY - column to sort query by
     * @param bool   $desc  DESC/ASC - true for DESC ordering
     *
     * @return array Collection of objects found in search
     */
    public static function some($count = null, $start = 0, $order = null, $desc = false) {
        $query = static::$query
            .' GROUP BY `'.static::$pkey.'`'
            .(null !== $order && array_key_exists($order, static::$vars)
                ? ' ORDER BY t.`'.$order.'` '.($desc ? 'desc' : 'asc')
                : '')
            .('rand()' == $order ? ' ORDER BY RAND() '.($desc ? 'desc' : 'asc') : '')
            .(is_numeric($count) && is_numeric($start)
                ? ' LIMIT '.((int)$start).','.((int)$count)
                : '');
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
     * @param string $order ORDER BY - column to sort query by
     * @param bool   $desc  DESC/ASC - true for DESC ordering
     *
     * @return array Collection of objects found in search
     */
    public static function all($order = null, $desc = false) {
        return static::some(null, 0, $order, $desc);
    }

    /**
     * SELECT all the records from the database using static::$query
     * add passed list of ids, returns collection
     *
     * @param array  $ids   Array of numeric ids to SELECT records for
     * @param int    $count LIMIT - number of rows to SELECT
     * @param int    $start OFFSET - number of rows to skip
     * @param string $order ORDER BY - column to sort query by
     * @param bool   $desc  DESC/ASC - true for DESC ordering
     *
     * @return array Collection of objects found in search
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
     * @param int $id Numeric id to SELECT record for
     *
     * @deprecated
     * @see Record::byPK
     *
     * @return object Object for specified ID
     */
    public static function byId($id) {
        $R = new static($id);
        $R->load();

        return $R;
    }

    /**
     * SELECT the record from the database with the specified pkey value
     *
     * @param int $val Numeric id to SELECT record for
     *
     * @return bool|Record False on failure or Record object for specified PKey
     */
    public static function byPK($val) {
        $R        = new static();
        $pkey     = static::$pkey;
        $R->$pkey = $val;
        if (false === $R->select()) {
            return false;
        }

        return $R;
    }

    /**
     * Commit object to database
     *  if pkey is not set, assume INSERT query, else UPDATE
     *
     * @return mixed Value returned by delegated method
     */
    public function save() {
        if (null === $this->vals[static::$pkey]) {
            return $this->insert();
        }

        return $this->update();
    }

    /**
     * Build INSERT query for set values, run and store insert_id
     * set value detection based on DBval, null for new (unloaded) records
     * $save flag set if any field changed, typically pkey set for insert()
     *
     * returns new pkey value (insert_id)
     * (uses MySQL specific INSERT ... SET ... syntax)
     *
     * @return mixed New primary key value of inserted row, or false on failure
     */
    public function insert() {
        $query = 'INSERT INTO `'.static::$table.'` SET ';
        $save  = false;
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k]
                || (null === $this->vals[$k]) != (null === $this->DBvals[$k])
                || (true === $this->vals[$k]) != (true === $this->DBvals[$k])
                || (false === $this->vals[$k]) != (false === $this->DBvals[$k])
            ) {
                $save = true;
            }
        }
        // if save is still false, no fields were set, this is unexpected
        if (false === $save) {
            return null;
        }
        $this->oninsert();
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k]
                || (null === $this->vals[$k]) != (null === $this->DBvals[$k])
                || (true === $this->vals[$k]) != (true === $this->DBvals[$k])
                || (false === $this->vals[$k]) != (false === $this->DBvals[$k])
            ) {
                if ('b' == static::$vars[$k]['type']) {
                    $query .= " `$k` = ".($this->vals[$k] ? '1' : '0').',';
                } else {
                    $query .= " `$k` = '".G::$M->escape_string($this->vals[$k])."',";
                }
            }
        }

        $query = substr($query, 0, -1);
        if (false === G::$M->query($query)) {
            return false;
        }
        if (0 != G::$M->insert_id) {
            $this->vals[static::$pkey] = G::$M->insert_id;
        }

        // Subsequent to successful DB commit, update DBvals
        foreach (static::$vars as $k => $v) {
            $this->DBvals[$k] = $this->vals[$k];
        }

        return $this->vals[static::$pkey];
    }

    /**
     * Build UPDATE query for changed values, run
     * set value detection based on DBval, set in load()
     * $save flag set if any field changed
     *
     * @return bool True on success, false on failure, null on abort
     */
    public function update() {
        $query = 'UPDATE `'.static::$table.'` SET ';
        $save  = false;
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k]
                || (null === $this->vals[$k]) != (null === $this->DBvals[$k])
                || (true === $this->vals[$k]) != (true === $this->DBvals[$k])
                || (false === $this->vals[$k]) != (false === $this->DBvals[$k])
            ) {
                $save = true;
            }
        }
        // if save is still false, no fields were set, this is unexpected
        if (false === $save) {
            return null;
        }
        $this->onupdate();
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k]
                || (null === $this->vals[$k]) != (null === $this->DBvals[$k])
                || (true === $this->vals[$k]) != (true === $this->DBvals[$k])
                || (false === $this->vals[$k]) != (false === $this->DBvals[$k])
            ) {
                if (null === $this->vals[$k]) {
                    $query .= '`'.$k."` = NULL,";
                } elseif ('b' == static::$vars[$k]['type']) {
                    $query .= '`'.$k.'` = '.($this->vals[$k] ? '1' : '0').',';
                } else {
                    $query .= '`'.$k."` = '".G::$M->escape_string($this->vals[$k])."',";
                }
            }
        }

        $query = substr($query, 0, -1)
            ." WHERE `".static::$pkey."` = '".G::$M->escape_string($this->vals[static::$pkey])."' LIMIT 1";
        if (false === G::$M->query($query)) {
            return false;
        }

        // Subsequent to successful DB commit, update DBvals
        foreach (static::$vars as $k => $v) {
            $this->DBvals[$k] = $this->vals[$k];
        }

        return true;
    }

    /**
     * Delete a record
     *
     * @return bool True on success, false on failure
     */
    public function delete() {
        // Fail if pkey has no value
        if (null === $this->vals[static::$pkey]) {
            return false;
        }
        $this->ondelete();
        $query = 'DELETE FROM `'.static::$table.'` '
            ." WHERE `".static::$pkey."` = '".G::$M->escape_string($this->vals[static::$pkey])."' LIMIT 1";
        if (false === G::$M->query($query)) {
            return false;
        }

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
        $query = "CREATE TABLE IF NOT EXISTS `".static::$table."` (";
        foreach (static::$vars as $field => $config) {
            if (!isset($config['ddl'])) {
                $config['ddl'] = static::deriveDDL($field);
            }
            $query .= $config['ddl'].', ';
        }
        $query .= 'PRIMARY KEY(`'.static::$pkey.'`))';

        if ($returnQuery) {
            return $query;
        }

        return G::$M->query($query);
    }

    /**
     * Get DESCRIBE data from mysql server
     *
     * @return array|bool
     */
    public static function describe() {
        $query = "DESCRIBE `".static::$table."`";

        return G::$m->queryToArray($query);
    }
}
