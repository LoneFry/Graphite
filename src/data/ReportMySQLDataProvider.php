<?php
/**
 * ReportMySQLDataProvider - Provide report data from MySQL
 * File : /^/lib/ReportMySQLDataProvider.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core\data;

/**
 * ReportMySQLDataProvider class - Fetches reports for PassiveReport models
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 */
abstract class ReportMySQLDataProvider extends MySQLDataProvider {
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
        /** @var PassiveReport $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'PassiveReport')) {
            trigger_error('Supplied class name does not extend PassiveReport', E_USER_ERROR);
        }

        // Sanitize $params through Model
        $Model->setAll($params);

        $vars   = $Model->getParamList();
        $params = $Model->getAll();
        $params = array_filter($params, function($val) {
            return !is_null($val);
        });

        $query = array();

        foreach ($params as $key => $val) {
            if ('a' === $vars[$key]['type']) {
                $arr = unserialize($Model->$key);

                foreach ($arr as $kk => $vv) {
                    $arr[$kk] = G::$m->escape_string($vv);
                }
                $query[] = sprintf($vars[$key]['sql'], "'".implode("', '", $arr)."'");
            } else {
                $query[] = sprintf($vars[$key]['sql'], G::$m->escape_string($val));
            }
        }

        if (count($query) == 0) {
            $query = sprintf($this->getQueryForReport($class), '1');
        } else {
            $query = sprintf($this->getQueryForReport($class), implode(' AND ', $query));
        }

        $query .= $this->_makeOrderBy($Model->getOrders($orders));

        if (null == $count) {
            $count = $Model->getCount;
            $start = $Model->getStart;
        }
        if (is_numeric($count) && is_numeric($start)) {
            // add limits also
            $query .= ' LIMIT '.$start.', '.$count;
        }

        $result = G::$m->query($query);

        if (false === $result) {
            return false;
        }
        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $result->close();
        $Model->setData($data);
        $Model->onload();

        return $Model->toArray();
    }

    /**
     * Search for records of type $class according to provided primary key(s)
     *
     * @param string $class Name of Model to search for
     * @param mixed  $pkey  Value(s) of primary key to fetch
     *
     * @return array Found records
     */
    public function byPK($class, $pkey) {
        return false;
    }

    /**
     * Save data does not apply to reports
     *
     * @param PassiveRecord $Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function insert(PassiveRecord &$Model) {
        return false;
    }

    /**
     * Save data does not apply to reports
     *
     * @param PassiveRecord $Model Model to save, passed by reference
     *
     * @return bool false
     */
    public function update(PassiveRecord &$Model) {
        return false;
    }

    /**
     * Gets the Query for the report
     *
     * @param string $class Name of Report
     *
     * @return mixed
     */
    abstract public function getQueryForReport($class);
}
