<?php
/**
 * Factory
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core;

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
     * @param string $className Name of Class to include
     * @param array  $args      Constructor arguments
     *
     * @return mixed
     */
    public function build($className, ...$args) {
        if (is_string($className)) {
            // Use Reflection to pass dynamic constructor arguments
            foreach (G::$G['namespaces'] as $namespace) {
                if (class_exists($namespace.$className)) {
                    $reflection = new \ReflectionClass($namespace.$className);

                    return $reflection->newInstanceArgs($args);
                }
            }
        }

        return null;
    }
}
