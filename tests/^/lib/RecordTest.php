<?php
/**
 * RecordTest
 *
 * PHP version 5
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once dirname(__FILE__) . '/../includeme.php';


/**
 * Record class unit test
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class RecordTest extends UnitTest {
    protected $obj;

    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        AutoLoader::addFile(TEST_ROOT.'/mocks/models/TestRecord.php', true);
    }

    /**
     * Resets the fixtures between each run.
     *
     * @return mixed
     */
    public function setUp() {
        parent::setUp();
        // Create a new TestRecord with defaults set
        $this->obj = new TestRecord(true);
    }

    /**
     * Destroys the session and other clean ups.
     *
     * @return mixed|void
     */
    public function tearDown() {
        $this->obj = null;
    }

    /**
     * Data Provider for test_i()
     *
     * @return array
     */
    public function dataTestGetTable() {
        $data = array();

        // Data assume TestRecord specifies table `Test`
        $data[] = array(null, 'Test');
        $data[] = array('Login', 'Test_Login');
        $data[] = array('Role', 'Test_Role');

        return $data;
    }

    /**
     * Tests integer setter/getter
     *
     * @param string $request Value to request
     * @param mixed  $expect  Expected result
     *
     * @return void
     *
     * @dataProvider dataTestGetTable
     */
    public function testGetTable($request, $expect) {
        $result = TestRecord::getTable($request);
        $this->assertEquals(G::$m->tabl.$expect, $result);
    }

    /**
     * Data Provider for test_i()
     *
     * @return array
     */
    public function dataTestGetTableError() {
        $data = array();

        // Data assume TestRecord specifies table `Test`
        $data[] = array('bad`table', null);

        return $data;
    }

    /**
     * Tests integer setter/getter
     *
     * @param string $request Value to request
     * @param mixed  $expect  Expected result
     *
     * @return void
     *
     * @dataProvider dataTestGetTableError
     */
    public function testGetTableError($request, $expect) {
        $this->errors = array();
        set_error_handler(array($this, 'errorHandler'));

        $result = TestRecord::getTable($request);
        $this->assertEquals($expect, $result);
        $this->assertError('Requested invalid joiner table', E_USER_NOTICE);

        restore_error_handler();
    }

    private $errors;

    public function errorHandler($errno, $errstr) {
        $this->errors[] = compact('errno', 'errstr');
    }

    public function assertError($errstr, $errno) {
        foreach ($this->errors as $error) {
            if ($error['errstr'] === $errstr && $error['errno'] === $errno) {
                return;
            }
        }
        $this->fail('Error with level '.$errno.' and message \''.$errstr.'\' not found in ',
            var_export($this->errors, true));
    }
}
