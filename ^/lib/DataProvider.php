<?php
/**
 * DataProvider - Base class for DataProviders
 * File : /^/lib/DataProvider.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * DataProvider class - provides partial functionality of DataProviders
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/mysqli_.php
 * @see      /^/lib/PassiveRecord.php
 */
abstract class DataProvider implements IDataProvider {
    /**
     * Search for records of type $class according to provided primary key(s)
     *
     * @param string $class Name of Model to search for
     * @param mixed  $pkey  Value(s) of primary key to fetch
     *
     * @return array Found records
     */
    public function byPK($class, $pkey) {
        /** @var PassiveRecord $Model */
        $Model = G::build($class);
        if (!is_a($Model, 'PassiveRecord')) {
            trigger_error('Supplied class name does not extend PassiveRecord', E_USER_ERROR);
        }

        $result = $this->fetch($class, array($class::getPkey() => $pkey));

        if (!is_array($pkey)) {
            if (!isset($result[$pkey])) {
                $result = false;
            } else {
                $result = $result[$pkey];
            }
        }

        return $result;
    }

    /**
     * Load data for passed model by its set values
     *
     * @param PassiveRecord $Model Model to load, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function load(PassiveRecord &$Model) {
        if (null === $Model->{$Model->getPkey()}) {
            return $this->fill($Model);
        }

        return $this->select($Model);
    }

    /**
     * Load data for passed model by its set values
     *
     * @param PassiveRecord $Model Model to load, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function select(PassiveRecord &$Model) {
        if (null === $Model->{$Model->getPkey()}) {
            return null;
        }

        $results = $this->fetch(get_class($Model), array($Model->getPkey() => $Model->{$Model->getPkey()}));
        if (count($results)) {
            $Model = array_shift($results);
            return true;
        }

        return false;
    }

    /**
     * Load data for passed model by its set values
     *
     * @param PassiveRecord $Model Model to load, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function fill(PassiveRecord &$Model) {
        $params = array_filter($Model->toArray(), function ($val) {
            return !is_null($val);
        });
        if (0 == count($params)) {
            return null;
        }

        $results = $this->fetch(get_class($Model), $params, array(), 1, 0);
        if (count($results)) {
            $Model = array_shift($results);

            return true;
        }

        return false;
    }

    /**
     * Save data for passed model
     *
     * @param PassiveRecord $Model Model to save, passed by reference
     *
     * @return bool|null True on success, False on failure, Null on invalid attempt
     */
    public function save(PassiveRecord &$Model) {
        if (null !== $Model->{$Model->getPkey()}) {
            return $this->update($Model);
        }

        return $this->insert($Model);
    }
}
