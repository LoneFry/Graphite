<?php
/**
 * AdminController class unit test
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 *
 * @group controllers
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
 * @author   Cris Bettis
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class AdminControllerTest extends UnitTest {

    protected $controller;


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

        // Duplicate the controller but mock Mailer.
        $this->controller = $this->getMockBuilder('AdminController')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        G::$S->expects($this->any())
            ->method('roleTest')
            ->will($this->returnValue(true));
    }

    /**
     * Mocks the LoginEdit function on controller.
     *
     * @return void
     */
    public function fakeLoginEdit() {
        $this->controller = $this->getMockBuilder('AdminController')
            ->disableOriginalConstructor()
            ->setMethods(array('do_LoginEdit'))
            ->getMock();
    }

    /**
     * Tests the do_list action
     *
     * @return mixed
     */
    public function testDo_list() {
        $this->controller->do_list(array());

        $this->assertEquals('Admin.list.php', G::$V->_template);
        $this->assertEquals('Administrative Options', G::$V->_title);
    }

    /**
     * Tests the do_list action
     *
     * @return mixed
     */
    public function testDo_loginNoLetter() {
        G::$V->list = null;

        $this->controller->do_login(array('login'));

        $this->assertEquals('Admin.Login.php', G::$V->_template);
        $this->assertEquals('Select Login', G::$V->_title);

        $this->assertTrue(is_array(G::$V->list));

        // Verify it hits the letter command and lets appropriately.
        $this->assertEquals(array('A', 'B', 'C'), G::$V->letters);
    }

    /**
     * Tests the do_list action
     *
     * @return mixed
     *
     */
    public function testDo_loginList() {
        G::$V->list = null;

        $this->controller->do_login(array('login', 'D'));
        $this->assertEquals(4, count(G::$V->list));

        // Verify it hits the letter command and lets appropriately.
        $this->assertEquals(array('A', 'B', 'C'), G::$V->letters);
    }

    /**
     * Tests the do_list action
     *
     * @return mixed
     */
    public function testDo_loginGetEdit() {
        G::$V->list = null;

        // Verify that do_LoginEdit get called without actually calling it.
        $this->fakeLoginEdit();
        $this->controller->expects($this->once())
            ->method('do_LoginEdit')
            ->will($this->returnValue(false));

        $this->controller->do_login(array('login', 'A'), null);
    }

    /**
     * Test the do_LoginAdd
     *
     * @return void
     */
    public function testDo_LoginAdd() {
        $this->controller->do_LoginAdd(array(), null);

        $this->assertEquals('Admin.LoginAdd.php', G::$V->_template);
        $this->assertEquals('Add Login', G::$V->_title);

        $this->assertTrue(is_a(G::$V->L, 'Login'));
        $this->assertEquals(array('A', 'B', 'C'), G::$V->letters);
    }

    /**
     * Dataprovider for testDo_LoginAddPost
     *
     * @return array
     */
    public function dataLoginAddPost() {

        $post = array(
            'loginname'       => 'loginname',
            'realname'        => 'reallname',
            'pass1'           => '',
            'pass2'           => 'pass2',
            'email1'          => 'dev@urldoesnotexist.com',
            'email2'          => 'email2',
            'sessionStrength' => 'sessionStrength',
            'flagChangePass'  => 'flagChangePass',
            'disabled'        => 'false',
        );

        // Array of argument arrays.
        $returnSets = array();

        // Emails mismatched
        $msg = 'admin.loginadd.msg.emailmismatch';
        $returnSets[] = array($post, $msg);
        // Make emails match
        $post['email2'] = $post['email1'];

        // Password Empty Test
        $msg = 'admin.loginadd.msg.passwordempty';
        $returnSets[] = array($post, $msg);
        // Fill in valid password
        $post['pass1'] = '!WordOfPassing1';

        // Test Mismatched passwords
        $msg = 'admin.loginadd.msg.passwordmismatch';
        $returnSets[] = array($post, $msg);
        // Make passwords match
        $post['pass2'] = $post['pass1'];

        /*
        // Test Invalid Login name
        $post['loginname'] = 7;
        $msg = 'admin.loginadd.msg.loginnameinvalid';
        $returnSets[] = array($post, $msg);
        // Make username correct
        $post['loginname'] = 'loginname';
        */

        // @TODO: Make a dataset for loginname collision

        // Test All Valid
        // Mocks return null from db so nochange is what you'll get.
        $msg = 'admin.loginadd.msg.nochange';
        $returnSets[] = array($post, $msg);

        return $returnSets;
    }

    /**
     * Test the do_LoginAdd Post
     *
     * @param array  $post Post object
     * @param string $msg  Returned Msg
     *
     * @return void
     *
     * @dataProvider dataLoginAddPost
     */
    public function testDo_LoginAddPost($post, $msg) {
        G::$M->errno = 1062;
        $this->controller->do_LoginAdd(array(), $post);

        $found = false;
        foreach (G::$_msg as $entry) {
            if (in_array($msg, $entry)) {
                $found = true;
            }
        }

        $this->assertTrue($found);
    }

    /**
     * Test the do_LoginEdit
     *
     * @return void
     */
    public function testdo_LoginEditGet() {
        $this->controller->do_LoginEdit(array('foo', 1), null);

        $this->assertEquals('Admin.LoginEdit.php', G::$V->_template);
        $this->assertEquals('Edit Login', G::$V->_title);

        $this->assertTrue(is_a(G::$V->L, 'Login'));
        $this->assertEquals(array('A', 'B', 'C'), G::$V->letters);
    }

    /**
     * Dataprovider for testDo_LoginEditPost
     *
     * @return array
     */
    public function dataLoginEditPost() {

        $post = array(
            'login_id'        => 1,
            'loginname'       => 'loginname',
            'realname'        => 'reallname',
            'pass1'           => '',
            'pass2'           => 'pass2',
            'email1'          => 'dev@urldoesnotexist.com',
            'email2'          => 'email2',
            'sessionStrength' => 'sessionStrength',
            'flagChangePass'  => 'flagChangePass',
            'disabled'        => 'false',
        );

        // Array of argument arrays.
        $returnSets = array();

        // Emails mismatched
        $msg = 'admin.loginedit.msg.emailmismatch';
        $returnSets[] = array($post, $msg);
        // Make emails match
        $post['email2'] = $post['email1'];

        // Test Mismatched passwords
        $msg = 'admin.loginedit.msg.passwordmismatch';
        $returnSets[] = array($post, $msg);
        // Make passwords match
        $post['pass2'] = $post['pass1'];

        // Test All Valid
        // Mocks return null from db so nochange is what you'll get.
        $msg = 'admin.loginedit.msg.success';
        $returnSets[] = array($post, $msg);

        return $returnSets;
    }

    /**
     * Test the do_LoginEdit Post
     *
     * @param array  $post Post object
     * @param string $msg  Returned Msg
     *
     * @return void
     *
     * @dataProvider dataLoginEditPost
     */
    public function testDo_LoginEditPost($post, $msg) {
        G::$M->errno = 1062;
        $this->controller->do_LoginEdit(array('foo', $post['login_id']), $post);

        $found = false;
        foreach (G::$_msg as $entry) {
            if (in_array($msg, $entry)) {
                $found = true;
            }
        }
        $this->assertTrue($found);
    }
}
