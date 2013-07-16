<?php
/**
 * Includeme file that includes/configures for unit tests
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http:// creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http:// g.lonefry.com
 * @see      /^/lib/Controller.php
 */

define('NOW', microtime(true));
// the root of this website
define('SITE', dirname(dirname(__FILE__)));
// the RELATIVE path of the core files
define('CORE', '/^');
// the ABSOLUTE path of the lib includes
define('LIB', SITE.CORE.'/lib');

// Path of testing includes
define('TEST_ROOT', SITE.'/tests');

// Graphite Version indicator, for scripts interacting herewith
define('GVER', 5);


// to save from having to work around magic quotes, just refuse to work with it
if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
    die('disable magic quotes');
}

require_once TEST_ROOT.'/mocks/G.php';
require_once TEST_ROOT.'/config.php';


define('MODE', G::$G['MODE']);      // controls a few things that assist dev
define('CONT', G::$G['CON']['URL']);// for use in URLs

if (isset(G::$G['timezone'])) {
    date_default_timezone_set(G::$G['timezone']);
}

require_once LIB.'/View.php';
require_once LIB.'/mysqli_.php';
require_once LIB.'/Security.php';

$_SERVER['REMOTE_ADDR'] = 'testing.overnightbdc.com';


/**
 * Unit Test wrapper class for initializing mock G components.
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http:// creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http:// g.lonefry.com
 */
class UnitTest extends PHPUnit_Framework_TestCase {

    /**
     * Setup
     *
     * @return mixed
     */
    public function setUp() {
        // Setup Stuff

        // Mock out security object
        G::$S = $this->getMockBuilder('Security')
            ->disableOriginalConstructor()
            ->getMock();
        // Mock out view object
        G::$V = $this->getMockBuilder('View')
            ->setConstructorArgs(array(array()))
            ->setMethods(array('render'))
            ->getMock();

        // $this->getMock('View', array('render'));

        // Mock out read sql object
        G::$m = $this->getMockBuilder('mysqli_')
            ->disableOriginalConstructor()
            ->getMock();

        // Mock out write sql object
        G::$M = $this->getMockBuilder('mysqli_')
            ->disableOriginalConstructor()
            ->getMock();

    }

    /**
     * Teardown
     *
     * @return mixed
     */
    public function tearDown() {

    }


};