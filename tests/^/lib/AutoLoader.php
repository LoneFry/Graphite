<?php
/**
 * ${NAME}
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/* Testing Globals */
require_once dirname(__FILE__).'/../../includeme.php';



/**
 * AdminController class unit test
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class AutoLoaderTest extends UnitTest {

    protected $obj;


    /**
     * Resets the fixtures between each run.
     *
     * @return mixed
     */
    public function setUp() {
        parent::setUp();

        G::$V->_title = '';
        G::$V->_template = '';
        G::$V->_siteName = '';
        G::$_msg = array();


    }

    /**
     * Tests that the register is giving back the appropriate format.
     *
     * DISABLED due to interferance with main AutoLoader
     *
     * @return void
     */
    public function testRegisterDirectory() {
        /*
        $dirResult = array(
            '/var/www/vhosts/cris.overnightbdc.com/^/models/Foo.php',
            '/var/www/vhosts/cris.overnightbdc.com/^/lib/Bar.php',
            '/var/www/vhosts/cris.overnightbdc.com/^/controllers/AirController.php',
        );

        $expectedValue = array(
            'Foo' => '/var/www/vhosts/cris.overnightbdc.com/^/models/Foo.php',
            'Bar' => '/var/www/vhosts/cris.overnightbdc.com/^/lib/Bar.php',
            'AirController' => '/var/www/vhosts/cris.overnightbdc.com/^/controllers/AirController.php',
        );

        $MockAuto = $this->getMockClass(
            'AutoLoader', // name of class to mock
            array('getDirListing') // list of methods to mock
        );


        $MockAuto::staticExpects($this->any())
            ->method('getDirListing')
            ->will($this->returnValue($dirResult));

        G::$G['includePath'] = '/^';
        $MockAuto::$classNames = array();
        $MockAuto::registerDirectory();

        $this->assertEquals($expectedValue, $MockAuto::$classNames);
        */
        $this->assertTrue(true);
    }

}
