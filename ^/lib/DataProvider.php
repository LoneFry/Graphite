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
abstract class DataProvider implements IDataProvider {
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

        $results = $this->search(get_class($Model), array($Model->getPkey() => $Model->{$Model->getPkey()}));
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

        $results = $this->search(get_class($Model), $params, array(), 1, 0);
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
