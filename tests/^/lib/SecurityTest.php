<?php
/**
 * SecurityTest
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <apt142@gmail.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once dirname(__FILE__).'/../../includeme.php';


/**
 * AdminController class unit test
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
class SecurityTest extends UnitTest {

    protected $obj;


    /**
     * Resets the fixtures between each run.
     *
     * @return mixed
     */
    public function setUp() {
        $this->obj = $this->getMockBuilder('Security')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    /**
     * Destroys the session and other clean ups.
     *
     * @return mixed|void
     */
    public function tearDown() {

    }

    /**
     * Tests authentication method on security controller
     *
     * @return void
     */
    public function testAuthenticate() {
        /* This method touches session.  As a result it requires special
           mocking. Marking the test as skipped for now.
        */
        $this->markTestSkipped();
        // $this->obj->authenticate('username', 'password');
    }




}
