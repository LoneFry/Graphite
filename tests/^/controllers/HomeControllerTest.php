<?php
/**
 * HomeController class unit test
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 *
 * @group controllers
 */

/* Testing Globals */
require_once dirname(__FILE__).'/../../includeme.php';



/**
 * HomeController class unit test
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   Cris Bettis <cris.bettis@bettercarpeople.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class HomeControllerTest extends UnitTest {

    protected $controller;

    /**
     * Resets the fixtures between each run.
     *
     * @return mixed
     */
    public function setUp() {
        parent::setUp();

        // Duplicate the controller but mock Mailer.
        $this->controller = $this->getMockBuilder('HomeController')
            ->disableOriginalConstructor()
            ->setMethods(array('mailer'))
            ->getMock();
        // new HomeController();

        G::$V->_siteName = '';
        G::$_msg = array();
    }


    /**
     * Tests the do_home action
     *
     * @return mixed
     */
    public function testDo_home() {
        $this->controller->do_home(array());

        $this->assertEquals(G::$V->_template, 'Home.php');
        $this->assertEquals(G::$V->_title, G::$V->_siteName);
    }

    /**
     * Test the GET request on the do_contact action
     *
     * @return mixed
     */
    public function testDo_contactGet() {
        $this->controller->do_contact(array(), array());

        $viewVars = array(
            '_title',
            'seed',
            'from',
            'subject',
            'message',
            'honey',
            'honey2',
            '_head'
        );

        foreach ($viewVars as $name) {
            $tmp = G::$V->$name;
            $this->assertTrue(!empty($tmp));
        }
    }

    /**
     * Tests the POST request on the do_contact action
     *
     * @return mixed
     * @disabled disabled
     */
    public function testDo_contactPost() {
        $viewVars = array();

        $viewVars['seed'] = 1;
        $viewVars['from'] = substr(md5(1), -6);
        $viewVars['subject'] = md5($viewVars['from']);
        $viewVars['message'] = md5($viewVars['subject']);
        $viewVars['honey'] = md5($viewVars['message']);
        $viewVars['honey2'] = md5($viewVars['honey']);


        // Possible responses
        $notBlankMsg = 'home.contact.msg.honeynotempty';

        $newLineMsg = 'home.contact.msg.fromnewline';

        $newLineMsgSubject = 'home.contact.msg.subjectnewline';

        $okMsg = 'home.contact.msg.sent';

        // Fake a post object
        $post = array(
            'apple' => 1,
            $viewVars['from']    => 'from',
            $viewVars['subject'] => 'subject',
            $viewVars['message'] => 'message',
            $viewVars['honey']   => 'honey',
            $viewVars['honey2']  => 'honey2'
        );


        // Tests honeys with data.
        $this->controller->do_contact(array(), $post);
        $this->assertEquals(G::$_msg[0][0], $notBlankMsg);


        $post[$viewVars['honey']]  = '';
        $post[$viewVars['honey2']] = '';

        // Tests from with carriage return.
        G::$_msg = array();
        $this->controller->do_contact(
            array(),
            array_merge($post, array($viewVars['from'] =>"from\n"))
        );
        $this->assertEquals($newLineMsg, G::$_msg[0][0]);

        // Tests subject with carriage return.
        G::$_msg = array();
        $this->controller->do_contact(
            array(),
            array_merge($post, array($viewVars['subject'] =>"subject\n"))
        );
        $this->assertEquals($newLineMsgSubject, G::$_msg[0][0]);

        // Test for everything is on hold because of program structure.

        G::$_msg = array();


        $this->controller->do_contact(
            array(),
            $post
        );
        $this->assertEquals($okMsg, G::$_msg[0][0]);

    }

}