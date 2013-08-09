<?php
/**
 * ModelsTest - Test all include models
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once dirname(__FILE__).'/../../includeme.php';


/**
 * Model class unit tests
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class ModelsTest extends UnitTest {
    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Tests ContactLog
     */
    public function testContactLog() {
        $this->assertSame('id', ContactLog::getPkey());
        $this->assertSame('test_ContactLog', ContactLog::getTable());

        mysqli_::$_aQueries = array(array(0));
        ContactLog::all();
        $expect = "SELECT t.`id`, t.`from`, t.`date`, t.`subject`, t.`to`, t.`body`, t.`IP`, t.`login_id`, t.`flagDismiss` FROM `test_ContactLog` t GROUP BY `id`";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);
    }

    /**
     * Tests LoginLog
     */
    public function testLoginLog() {
        $this->assertSame('pkey', LoginLog::getPkey());
        $this->assertSame('test_LoginLog', LoginLog::getTable());

        mysqli_::$_aQueries = array(array(0));
        LoginLog::all();
        $expect = "SELECT t.`pkey`, t.`login_id`, t.`ip`, t.`ua`, t.`iDate` FROM `test_LoginLog` t GROUP BY `pkey`";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);
    }

    /**
     * Tests Login
     */
    public function testLogin() {
        $this->assertSame('login_id', Login::getPkey());
        $this->assertSame('test_Logins', Login::getTable());
        $this->assertSame('test_Logins', Login::getTable());

        mysqli_::$_aQueries = array(array(0));
        Login::all();
        $expect = "SELECT t.`login_id`, t.`loginname`, t.`password`, t.`realname`, t.`email`, t.`comment`, t.`sessionStrength`, t.`UA`, t.`lastIP`, t.`dateActive`, t.`dateLogin`, t.`dateLogout`, t.`dateModified`, t.`dateCreated`, t.`referrer_id`, t.`disabled`, t.`flagChangePass`, GROUP_CONCAT(r.label) as roles FROM `test_Logins` t LEFT JOIN `test_Roles_Logins` rl ON t.login_id = rl.login_id LEFT JOIN `test_Roles` r ON r.role_id = rl.role_id GROUP BY `login_id`";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);

        $Login = new Login(1);

        mysqli_::$_aQueries = array(array(0));
        $Login->initials();
        $expect = "SELECT UPPER(LEFT(loginname, 1)), count(loginname) FROM `test_Logins` GROUP BY UPPER(LEFT(loginname, 1))";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);

        mysqli_::$_aQueries = array(array(0));
        $Login->forInitial('C');
        $expect = "SELECT t.`login_id`, t.`loginname` FROM `test_Logins` t WHERE `loginname` LIKE 'C%' ORDER BY `loginname`";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);
    }

    /**
     * Tests Role
     */
    public function testRole() {
        $this->assertSame('role_id', Role::getPkey());
        $this->assertSame('test_Roles', Role::getTable());

        mysqli_::$_aQueries = array(array(0));
        Role::all();
        $expect = "SELECT t.`role_id`, t.`label`, t.`description`, t.`creator_id`, t.`disabled`, t.`dateModified`, t.`dateCreated` FROM `test_Roles` t GROUP BY `role_id`";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);

        $Role = new Role(1);

        mysqli_::$_aQueries = array(array(0));
        $Role->getMembers('grantor_id');
        $expect = "SELECT rl.`login_id`, rl.`grantor_id` FROM `test_Roles_Logins` rl WHERE rl.`role_id` = 1";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);

        mysqli_::$_aQueries = array(array(0));
        $Role->getMembers('loginname');
        $expect = "SELECT l.`login_id`, l.`loginname` FROM `test_Logins` l, `test_Roles_Logins` rl WHERE l.`login_id` = rl.`login_id` AND rl.`role_id` = 1 ORDER BY l.`loginname`";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);

        mysqli_::$_aQueries = array(array(0));
        $Role->grant(2);
        $expect = "INSERT INTO `test_Roles_Logins` (`role_id`,`login_id`,`grantor_id`,`dateCreated`) VALUES (1,2,0,".NOW.")";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);

        mysqli_::$_aQueries = array(array(0));
        $Role->revoke(2);
        $expect = "DELETE FROM `test_Roles_Logins` WHERE `role_id` = 1 AND `login_id` = 2";
        $this->assertSame($expect, mysqli_::$_aQueries[1][1]);
    }
}
