<?php
/**
 * Factory
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * Class Factory
 *
 * This instantiates Objects
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class Factory {

    /**
     * Instantiates a new object.
     * Takes a className followed by parameters needed for the constructor of
     * that object
     *
     * @return mixed
     */
    public function build(/* Classname, Arg1, Arg2.. */) {
        $args = func_get_args();
        $className = array_shift($args);

        if (is_string($className)) {
            // Use Reflection to pass dynamic constructor arguments
            $reflection = new ReflectionClass($className);
            return $reflection->newInstanceArgs($args);
        }
        return null;
    }
}
