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
    public function search($class, array $params, array $orders = array(), $count = null, $start = 0) {
        /** @var PassiveRecord $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'PassiveRecord')) {
            trigger_error('Supplied class name does not extend PassiveRecord', E_USER_ERROR);
        }

        // Sanitize $params through Model
        $Model->setAll($params);
        $params = $Model->getDiff();

        // If no fields were set, this is unexpected
        if (0 == count($params)) {
            return null;
        }

        $vars = $Model->getFieldList();
        $values = array();
        // Build search WHERE clause
        foreach ($params as $key => $val) {
            if ('b' == $vars[$key]['type']) {
                $values[] = "`$key` = ".($this->vals[$key] ? "b'1'" : "b'0'");
            } else {
                $values[] = "`$key` = '".G::$M->escape_string($this->vals[$key])."'";
            }
        }

        $keys = array_keys($vars);
        $query = 'SELECT t.`'.join('`, t.`', $keys).'`'
            .' FROM `'.G::$m->tabl.$Model->getTable().'` t'
            .' WHERE '.join(' AND ', $values)
            .' GROUP BY `'.$Model->getPkey().'`'
            .$this->_makeOrderBy($orders)
            .(is_numeric($count) && is_numeric($start)
                ? ' LIMIT '.((int)$start).','.((int)$count)
                : '')
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
                $values[] = $this->vals[$key] ? "b'1'" : "b'0'";
            } else {
                $values[] = G::$M->escape_string($this->vals[$key]);
            }
        }

        $query = 'INSERT INTO `'.$Model->getTable().'` '
            . '(`' . implode('`, `', $fields) . '`)'
            . "VALUES ('" . implode("','", $values) . "')";

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
                $values[] = "`$key` = ".($this->vals[$key] ? "b'1'" : "b'0'");
            } else {
                $values[] = "`$key` = '".G::$M->escape_string($this->vals[$key])."'";
            }
        }

        $query = 'UPDATE `'.$Model->getTable().'` SET '
            .implode(',', $values)
            ." WHERE `".$Model->getPkey()."` = '".G::$M->escape_string($Model->{$Model->getPkey()})."'";

        if (false === G::$M->query($query)) {
            return false;
        }
        if (0 != G::$M->insert_id) {
            $Model->{$Model->getPkey()} = G::$M->insert_id;
        }

        $Model->unDiff();
    }

    /**
     * Take an array that has fieldnames for keys
     *  and bool indicating asc/desc order for values
     *
     * @param array $orders Array of field => (= 'asc' ?)
     *
     * @return string ORDER BY clause
     */
    protected function _makeOrderBy(array $orders = array()) {
        if (0 == count($orders)) {
            return '';
        }

        foreach ($orders as $field => $asc) {
            if ('rand()' == $field) {
                $orders[$field] = "RAND() ".($asc ? 'ASC' : 'DESC');
            } else {
                $orders[$field] = "`$field` ".($asc ? 'ASC' : 'DESC');
            }
        }

        return 'ORDER BY '.join(',', $orders);
    }

    /**
     * Derive DDL for a field as configured
     *
     * @param string $field  Name of field to derive DDL for
     * @param array  $config Definition of field from Model
     *
     * @return bool|string
     */
    protected function _deriveDDL($field, array $config) {
        switch ($config['type']) {
            case 'f': // float
                $config['ddl'] = '`'.$field.'` FLOAT NOT NULL';
                if (isset($config['def']) && is_numeric($config['def'])) {
                    $config['ddl'] .= ' DEFAULT '.$config['def'];
                }
                break;
            case 'b': // boolean stored as bit
                $config['ddl'] = '`'.$field.'` BIT(1) NOT NULL';
                if (isset($config['def'])) {
                    $config['ddl'] .= ' DEFAULT '.($config['def'] ? "b'1'" : "b'0'");
                } else {
                    $config['ddl'] .= " DEFAULT b'0'";
                }
                break;
            case 'ip': // IP address stored as int
                $config['ddl'] = '`'.$field.'` INT(10) UNSIGNED NOT NULL';
                if (isset($config['def'])) {
                    if (!is_numeric($config['def'])) {
                        $config['ddl'] .= ' DEFAULT '.ip2long($config['def']);
                    } else {
                        $config['ddl'] .= ' DEFAULT '.$config['def'];
                    }
                } else {
                    $config['ddl'] .= ' DEFAULT 0';
                }
                break;
            case 'em': // email address
            case 'o': // serialize()'d variables
            case 'j': // json_encoded()'d variables
            case 'a': // serialized arrays
            case 's': // string
                if (!isset($config['max']) || !is_numeric($config['max']) || 16777215 < $config['max']) {
                    $config['ddl'] = '`'.$field.'` LONGTEXT NOT NULL';
                } elseif (65535 < $config['max']) {
                    $config['ddl'] = '`'.$field.'` MEDIUMTEXT NOT NULL';
                } elseif (255 < $config['max']) {
                    $config['ddl'] = '`'.$field.'` TEXT NOT NULL';
                } else {
                    $config['ddl'] = '`'.$field.'` VARCHAR('.((int)$config['max']).') NOT NULL';
                }
                if (isset($config['def'])) {
                    $config['ddl'] .= " DEFAULT '".G::$M->escape_string($config['def'])."'";
                }
                break;
            case 'ts': // int based timestamps
                // convert date min/max values to ints and fall through
                if (isset($config['min']) && !is_numeric($config['min'])) {
                    $config['min'] = strtotime($config['min']);
                }
                if (isset($config['max']) && !is_numeric($config['max'])) {
                    $config['max'] = strtotime($config['max']);
                }
                if (isset($config['def']) && !is_numeric($config['def'])) {
                    $config['def'] = strtotime($config['def']);
                }
            // fall through
            case 'i': // integers
                if (isset($config['min']) && is_numeric($config['min']) && 0 <= $config['min']) {
                    if (!isset($config['max']) || !is_numeric($config['max'])) {
                        $config['ddl'] = '`'.$field.'` INT(10) UNSIGNED NOT NULL';
                    } elseif (4294967295 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` BIGINT(20) UNSIGNED NOT NULL';
                    } elseif (16777215 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` INT(10) UNSIGNED NOT NULL';
                    } elseif (65535 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` MEDIUMINT(7) UNSIGNED NOT NULL';
                    } elseif (255 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` SMALLINT(5) UNSIGNED NOT NULL';
                    } elseif (0 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` TINYINT(3) UNSIGNED NOT NULL';
                    }
                } else {
                    if (!isset($config['max']) || !is_numeric($config['max'])) {
                        $config['ddl'] = '`'.$field.'` INT(11) NOT NULL';
                    } elseif (2147483647 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` BIGINT(20) NOT NULL';
                    } elseif (8388607 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` INT(11) NOT NULL';
                    } elseif (32767 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` MEDIUMINT(8) NOT NULL';
                    } elseif (127 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` SMALLINT(6) NOT NULL';
                    } elseif (0 < $config['max']) {
                        $config['ddl'] = '`'.$field.'` TINYINT(4) NOT NULL';
                    }
                }
                if (isset($config['def']) && is_numeric($config['def'])) {
                    $config['ddl'] .= ' DEFAULT '.$config['def'];
                } elseif ($field != static::$pkey) {
                    $config['ddl'] .= ' DEFAULT 0';
                }

                // If the PRIMARY KEY is an INT type, assume AUTO_INCREMENT
                // This can be overridden with an explicit DDL
                if ($field == static::$pkey) {
                    $config['ddl'] .= ' AUTO_INCREMENT';
                }
                break;
            case 'e': // enums
                $config['ddl'] = '`'.$field.'` ENUM(';
                foreach ($config['values'] as $v) {
                    $config['ddl'] .= "'".G::$M->escape_string($v)."',";
                }
                $config['ddl'] = substr($config['ddl'], 0, -1).') NOT NULL';
                if (isset($config['def'])) {
                    $config['ddl'] .= " DEFAULT '".G::$M->escape_string($config['def'])."'";
                }
                break;
            case 'dt': // datetimes and mysql timestamps
                // A column called 'recordChanged' is assumed to be a MySQL timestamp
                if ('recordChanged' == $field) {
                    $config['ddl'] = '`'.$field.'` TIMESTAMP NOT NULL'
                        .' DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';
                    break;
                }

                $config['ddl'] = '`'.$field.'` DATETIME NOT NULL';
                if (isset($config['def'])) {
                    // This supports more flexible defaults, like '5 days ago'
                    if (!is_numeric($config['def'])) {
                        $config['def'] = strtotime($config['def']);
                    }
                    $config['ddl'] .= " DEFAULT '".date('Y-m-d H:i:s', $config['def'])."'";
                }
                break;
            default:
                trigger_error('Unknown field type "'.$config['type'].'"');

                return false;
        }

        return $config['ddl'];
    }
}
