<?php
/**
 * MysqlDataProvider - Provide data from MySQL
 * File : /^/lib/MysqlDataProvider.php
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * MysqlDataProvider class - Runs CRUD to MySQL for PassiveRecord models
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 * @see      /^/lib/PassiveRecord.php
 */
class MysqlDataProvider extends DataProvider {
    /**
     * Search for records of type $class according to search params $params
     * Order results by $orders and limit results by $count, $start
     *
     * @param string $class  Name of Model to search for
     * @param array  $params Values to search against
     * @param array  $orders Order(s) of results
     * @param int    $count  Number of rows to fetch
     * @param int    $start  Number of rows to skip
     *
     * @return array Found records
     */
    public function fetch($class, array $params = array(), array $orders = array(), $count = null, $start = 0) {
        /** @var PassiveRecord $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'PassiveRecord')) {
            trigger_error('Supplied class "'.$class.'" name does not extend PassiveRecord', E_USER_ERROR);
        }

        $vars = $Model->getFieldList();
        $values = array();
        // Build search WHERE clause
        foreach ($params as $key => $val) {
            if (!isset($vars[$key])) {
                // Skip Invalid field
                continue;
            }
            // Support list of values for IN conditions
            if (is_array($val) && !in_array($vars[$key]['type'], array('a', 'j', 'o', 'b'))) {
                foreach ($val as $key2 => $val2) {
                    // Sanitize each value through the model
                    $Model->$key = $val2;
                    $val2 = $Model->$key;
                    $val[$key2] = G::$m->escape_string($val2);
                }
                $values[] = "t.`$key` IN ('".implode("', '", $val)."')";
            } else {
                $Model->$key = $val;
                $val = $Model->$key;
                if ('b' == $vars[$key]['type']) {
                    $values[] = "t.`$key` = ".($val ? "b'1'" : "b'0'");
                } else {
                    $values[] = "t.`$key` = '".G::$M->escape_string($val)."'";
                }
            }
        }

        $keys = array_keys($vars);
        $query = $Model->getQuery();
        if ('' == $query) {
            $query = 'SELECT t.`'.join('`, t.`', $keys).'`'
                .' FROM `'.$Model->getTable().'` t';
        }
        $query .= (count($values) ? ' WHERE '.join(' AND ', $values) : '')
            .' GROUP BY t.`'.$Model->getPkey().'`'
            .$this->_makeOrderBy($orders, array_keys($vars))
            .(is_numeric($count) && is_numeric($start)
                ? ' LIMIT '.((int)$start).','.((int)$count)
                : '')
        ;

        $source = $Model->getSource();
        $MySql = mysqli_::buildForSource($source);
        if (null != $MySql) {
            $result = $MySql->query($query);
        } else {
            $result = G::$m->query($query);
        }
        if (false === $result) {
            return false;
        }

        $Records = array();
        while ($row = $result->fetch_assoc()) {
            /** @var PassiveRecord $Records[$row[$Model->getPkey()]] */
            $Records[$row[$Model->getPkey()]] = new $class();
            $Records[$row[$Model->getPkey()]]->load_array($row);
        }
        $result->close();

        return $Records;
    }

    /**
     * Save data for passed model
     *
     * @param PassiveRecord $Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function insert(PassiveRecord &$Model) {
        $diff = $Model->getDiff();

        // If no fields were set, this is unexpected
        if (0 == count($diff)) {
            return null;
        }

        $vars = $Model->getFieldList();
        $fields = array();
        $values = array();

        $Model->oninsert();
        foreach ($diff as $key => $val) {
            $fields[] = $key;
            if ('b' == $vars[$key]['type']) {
                $values[] = $diff[$key] ? "b'1'" : "b'0'";
            } else {
                $values[] = "'".G::$M->escape_string($diff[$key])."'";
            }
        }

        $query = 'INSERT INTO `'.$Model->getTable().'`'
            . ' (`' . implode('`, `', $fields) . '`)'
            . " VALUES (" . implode(", ", $values) . ")";

        if (false === G::$M->query($query)) {
            return false;
        }
        if (0 != G::$M->insert_id) {
            $Model->{$Model->getPkey()} = G::$M->insert_id;
        }

        $Model->unDiff();

        return $Model->{$Model->getPkey()};
    }

     /**
     * Save data for passed model
     *
     * @param PassiveRecord $Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function insert_update(PassiveRecord &$Model) {
        $diff = $Model->getDiff();

        // If no fields were set, this is unexpected
        if (0 == count($diff)) {
            return null;
        }

        // Iff the pkey has a value, add it to the diff to ensure the UPDATE works
        if (null !== $Model->{$Model->getPkey()}) {
            $diff[$Model->getPkey()] = $Model->{$Model->getPkey()};
        }
        $vars = $Model->getFieldList();
        $fields = array();
        $values = array();
        $updates = array();

        $Model->oninsert();
        foreach ($diff as $key => $val) {
            $fields[] = $key;
            if ('b' == $vars[$key]['type']) {
                $values[] = $diff[$key] ? "b'1'" : "b'0'";
                $updates[] = "`$key` = ".($diff[$key] ? "b'1'" : "b'0'");
            } else {
                $values[] = "'".G::$M->escape_string($diff[$key])."'";
                $updates[] = "`$key` = '".G::$M->escape_string($diff[$key])."'";
            }
        }

        $query = 'INSERT INTO `'.$Model->getTable().'`'
            . ' (`' . implode('`, `', $fields) . '`)'
            . " VALUES (" . implode(", ", $values) . ")"
            . " ON DUPLICATE KEY UPDATE "
            .implode(', ', $updates);

        if (false === G::$M->query($query)) {
            return false;
        }
        if (0 != G::$M->insert_id) {
            $Model->{$Model->getPkey()} = G::$M->insert_id;
        }

        $Model->unDiff();

        return $Model->{$Model->getPkey()};
    }

    /**
     * Save data for passed model
     *
     * @param PassiveRecord $Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function update(PassiveRecord &$Model) {
        // If the PKey is not set, what would we update?
        if (null === $Model->{$Model->getPkey()}) {
            return null;
        }
        $diff = $Model->getDiff();

        // If no fields were set, this is unexpected
        if (0 == count($diff)) {
            return null;
        }
        $vars = $Model->getFieldList();
        $values = array();

        $Model->onupdate();
        foreach ($diff as $key => $val) {
            if (null === $Model->{$Model->getPkey()}) {
                $values[] = "`$key` = NULL";
            } elseif ('b' == $vars[$key]['type']) {
                $values[] = "`$key` = ".($diff[$key] ? "b'1'" : "b'0'");
            } else {
                $values[] = "`$key` = '".G::$M->escape_string($diff[$key])."'";
            }
        }

        $query = 'UPDATE `'.$Model->getTable().'` SET '
            .implode(', ', $values)
            ." WHERE `".$Model->getPkey()."` = '".G::$M->escape_string($Model->{$Model->getPkey()})."'";

        if (false === G::$M->query($query)) {
            return false;
        }
        $Model->unDiff();
        return true;
    }

    /**
     * Delete data for passed Model
     *
     * @param PassiveRecord $Model Model to be passed
     *
     * @return mixed|null
     */
    public function delete(PassiveRecord &$Model) {
        // If the PKey is not set, what would we update?
        if (null === $Model->{$Model->getPkey()}) {
            return null;
        }

        $Model->ondelete();
        $query  = 'DELETE FROM `'.$Model->getTable().'` '
            ." WHERE `".$Model->getPkey()."` = '".G::$M->escape_string($Model->{$Model->getPkey()})."'";

        return G::$M->query($query);
    }

    /**
     * Take an array that has fieldnames for keys
     *  and bool indicating asc/desc order for values
     *
     * @param array $orders Array of field => (= 'asc' ?)
     * @param array $valids list of valid order by values
     *
     * @return string ORDER BY clause
     */
    protected function _makeOrderBy(array $orders = array(), array $valids = array()) {
        if (0 == count($orders) || 0 == count($valids)) {
            return '';
        }

        foreach ($orders as $field => $asc) {
            if (false === $asc || 'desc' == strtolower($asc)) {
                $asc = 'DESC';
            } elseif (true === $asc || 'asc' == strtolower($asc)) {
                $asc = 'ASC';
            } else {
                $asc = '';
            }
            if ('rand()' == $field) {
                $orders[$field] = "RAND() ".$asc;
            } elseif (in_array($field, $valids)) {
                $orders[$field] = "`$field` ".$asc;
            }
        }

        return 'ORDER BY '.join(',', $orders);
    }
}
