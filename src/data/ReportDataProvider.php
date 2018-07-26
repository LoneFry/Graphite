<?php
/**
 * ReportDataProvider - Provide report data from MySQL
 * File : /^/lib/ReportDataProvider.php
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
 * ReportDataProvider class - Fetches reports for Report models
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 */
class ReportDataProvider extends DataProvider {
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
        /** @var Report $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'Report')) {
            trigger_error('Supplied class name does not extend Report', E_USER_ERROR);
        }

        $data = $Model->fetch($params, $orders, $count, $start);

        // If result is not an array, empty, or not an array of arrays, return it
        if (!is_array($data) || !count($data) || !is_array(reset($data))) {
            return $data;
        }

        // Else, convert the arrays to objects
        // $data = array_map(function($val) { return (object)$val; }, $data);

        return $data;
    }

    /**
     * Count records of type $class according to search params $params
     *
     * @param string $class  Name of Model to search for
     * @param array  $params Values to search against
     *
     * @return array Found records
     */
    public function count($class, array $params = array()) {
        /** @var Report $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'Report')) {
            trigger_error('Supplied class name does not extend Report', E_USER_ERROR);
        }

        $data = $Model->count($params);

        return $data;
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
}
