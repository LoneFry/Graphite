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

define('SITE', dirname(dirname(dirname(__FILE__))));
// the ABSOLUTE path of the lib includes
define('LIB', SITE.'/^/lib');

// Path of testing includes
define('TEST_ROOT', SITE.'/tests');

// Graphite Version indicator, for scripts interacting herewith
define('GVER', 5);

// to save from having to work around magic quotes, just refuse to work with it
if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
    die('disable magic quotes');
}

require_once TEST_ROOT . '/mocks/lib/G.php';
require_once TEST_ROOT . '/^/config.php';

// Force some configs for testing
G::$G['db']['tabl'] = 'test_';
G::$G['db']['log']  = 1;

require_once SITE . '/^/lib/AutoLoader.php';
AutoLoader::registerDirectory();

spl_autoload_register(array('AutoLoader', 'loadClass'));

define('MODE', G::$G['MODE']);      // controls a few things that assist dev

if (isset(G::$G['timezone'])) {
    date_default_timezone_set(G::$G['timezone']);
}

$_SERVER['REMOTE_ADDR'] = 'testing.lonefry.com';


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
    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        AutoLoader::addFile(TEST_ROOT.'/mocks/lib/mysqli_.php', true);

        // setup DB connection or fail.
        G::$G['db']['tabl'] = 'test_';
        G::$G['db']['log']  = 1;
        G::$m               = G::$M = new mysqli_(
            G::$G['db']['host'],
            G::$G['db']['user'],
            G::$G['db']['pass'],
            G::$G['db']['name'],
            null,
            null,
            G::$G['db']['tabl'],
            G::$G['db']['log']
        );
    }

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
            ->setMethods(array('roleTest'))
            ->getMock();
        // Mock out view object
        G::$V = $this->getMockBuilder('View')
            ->setConstructorArgs(array(array()))
            ->setMethods(array('render'))
            ->getMock();

        // $this->getMock('View', array('render'));
    }

    /**
     * Teardown
     *
     * @return mixed
     */
    public function tearDown() {
        // Put stuff in here that you want to do after each test.
    }
};
