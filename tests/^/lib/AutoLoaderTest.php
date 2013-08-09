<?php
/**
 * AutoLoaderTest
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once dirname(__FILE__).'/../../includeme.php';


/**
 * AutoLoader class unit test
 *
 * @category PHPUNIT
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 *
 */
class AutoLoaderTest extends UnitTest {

    protected $obj;


    /**
     * Resets the fixtures between each run.
     *
     * @return mixed
     */
    public function setUp() {

        G::$G = array();
        G::$G['includePath'] = null;

    }

    /**
     * Tests that the register is giving back the appropriate format.
     *
     * DISABLED due to interference with main AutoLoader
     *
     * @return void
     */

    public function testRegisterDirectory() {
        $dirResult = array(
            '/path/to/awesome/Foo.php',
            '/Bar.php',
            '/path/to/awesome/AirController.php',
        );

        $expectedValue = array(
            'Foo' => '/path/to/awesome/Foo.php',
            'Bar' => '/Bar.php',
            'AirController' => '/path/to/awesome/AirController.php',
        );

        $MockAuto = $this->getMockBuilder('AutoLoader')
            ->disableOriginalConstructor()
            ->setMethods(array('getDirListing'))
            ->getMock();

        $MockAuto::staticExpects($this->any())
            ->method('getDirListing')
            ->will($this->returnValue($dirResult));

        G::$G['includePath'] = '/^';
        $MockAuto::registerDirectory();

        foreach ($expectedValue as $class => $path) {
            $this->assertTrue($MockAuto::getClass($class) !== null);
        }
    }

    /**
     * Tests Add File Function
     *
     * @return void
     */
    public function testAddFile() {
        $MockAuto = $this->getMockBuilder('AutoLoader')
            ->disableOriginalConstructor()
            ->setMethods(array('getDirListing'))
            ->getMock();

        $class = 'Foo';
        $path  = '/path/to/awesome/Foo.php';
        $path2 = '/path/to/nowhere/Foo.php';

        $MockAuto::removeClass($class);

        $this->assertTrue($MockAuto::getClass($class) === null);

        // Did add?
        $MockAuto::addFile($path);
        $this->assertTrue($MockAuto::getClass($class) == $path);

        // Test Default Non-overwrite
        $MockAuto::addFile($path2);
        $this->assertTrue($MockAuto::getClass($class) == $path);

        // Test Overwrite
        $MockAuto::addFile($path2, true);
        $this->assertTrue($MockAuto::getClass($class) == $path2);
    }
}
