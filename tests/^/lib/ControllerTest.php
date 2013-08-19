<?php
/**
 * ControllerTest
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <apt142@gmail.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once dirname(__FILE__) . '/../includeme.php';


/**
 * Controller class unit test
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   Cris Bettis <apt142@gmail.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 *
 */
class ControllerTest extends UnitTest {

    protected $obj;


    /**
     * Resets the fixtures between each run.
     *
     * @return mixed
     */
    public function setUp() {
        $this->obj = $this->getMockBuilder('Controller')
            ->disableOriginalConstructor()
            ->setMethods(array('do_Foo'))
            ->getMock();
        $_POST = array();
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    /**
     * Clean up some global vars.
     *
     * @return mixed
     */
    public function tearDown() {
        $_POST = array();
        $_GET = array();
        $_SERVER['REQUEST_METHOD'] = null;
    }

    /**
     * Data Provider for testAction()
     *
     * @return array
     */
    public function dataTestAction() {
        return array(
            array('Foo', 'Foo'),
            array('404', ''),
            array('Bar', ''),
        );
    }

    /**
     * Test that the action is set.
     *
     * @param string $action   Action requested
     * @param string $expected Expected action implemented.
     *
     * @return void
     *
     * @dataProvider dataTestAction
     */
    public function testAction($action, $expected) {
        $result = $this->obj->action($action);
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the act method.
     *
     * @return void
     */
    public function testAct() {
        $this->obj->action('Foo');

        $this->obj->expects($this->any())
            ->method('do_Foo')
            ->will($this->returnCallback('makeArgArray'));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = array('a' => 1, 'b' => 2, 'c' => 3);
        $_POST = array('d' => 4, 'e' => 5, 'f' => 6);

        $expected = array(array_merge($_GET, array('Foo')), $_POST);
        $result = $this->obj->act(array('Foo'));

        $this->assertEquals($expected, $result);

        $expected = array(array_merge($_GET, array('Foo')), $_GET);
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $result = $this->obj->act(array('Foo'));
        $this->assertEquals($expected, $result);

        $expected = array(array_merge($_GET, array('Foo')), array());
        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $result = $this->obj->act(array('Foo'));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the constructor works.
     *
     * @return void
     */
    public function testConstruct() {
        $this->obj->__construct(array('Foo'));
        $this->assertEquals('Foo', $this->obj->action);
    }

    /**
     * Test that the action is set.
     *
     * @return void
     */
    public function testGet() {
        $this->obj->action = 'Foo';
        $this->assertEquals($this->obj->action, $this->obj->__get('action'));
    }

    /**
     * Test that the set action tosses cookies.
     *
     * @return void
     *
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testGetException() {
        $this->obj->action = 'Foo';
        $this->assertEquals($this->obj->action, $this->obj->__get('bar'));
    }

    /**
     * Test that the action is get.
     *
     * @return void
     */
    public function testSet() {
        $this->obj->__set('action', 'Foo');
        $this->assertEquals('Foo', $this->obj->action);
    }

    /**
     * Test that the get action tosses cookies.
     *
     * @return void
     *
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testSetException() {
        $this->obj->__set('bar', 'Foo');
    }

}

/**
 * Used to pass through extra arguments in mock functions.
 *
 * @return array
 */
function makeArgArray() {
    return func_get_args();
}
