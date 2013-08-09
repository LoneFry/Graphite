<?php
/**
 * DataModelTest
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
 * DataModel class unit test
 *
 * PHP version 5
 *
 * @category PHPUNIT
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class DataModelTest extends UnitTest {
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
    public function dataTestSettersSame() {
        $data = array();

        $key    = 'testInt';
        $def    = TestRecord::$vars[$key]['def'];
        $min    = TestRecord::$vars[$key]['min'];
        $max    = TestRecord::$vars[$key]['max'];
        $data[] = array($key, -10, $min);
        $data[] = array($key, 3, 3);
        $data[] = array($key, 3.14159, 3);
        $data[] = array($key, 45, $max);
        $data[] = array($key, '', $min);
        $data[] = array($key, 0, $min);
        $data[] = array($key, true, $min);
        $data[] = array($key, false, $min);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictInt';
        $def = TestRecord::$vars[$key]['def'];
        $data[] = array($key, -10, $def);
        $data[] = array($key, 1.618, $def);
        $data[] = array($key, 3, 3);
        $data[] = array($key, 6.28318, $def);
        $data[] = array($key, 45, $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        $key    = 'testFloat';
        $def = TestRecord::$vars[$key]['def'];
        $min    = TestRecord::$vars[$key]['min'];
        $max    = TestRecord::$vars[$key]['max'];
        $data[] = array($key, -10, $min);
        $data[] = array($key, 1.618, 1.618);
        $data[] = array($key, 2, 2.0);
        $data[] = array($key, 3, 3.0);
        $data[] = array($key, 3.14159, 3.14159);
        $data[] = array($key, 4, $max);
        $data[] = array($key, '', $min);
        $data[] = array($key, 0, $min);
        $data[] = array($key, true, $min);
        $data[] = array($key, false, $min);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictFloat';
        $def = TestRecord::$vars[$key]['def'];
        $data[] = array($key, -1, $def);
        $data[] = array($key, 1, $def);
        $data[] = array($key, 1.618, 1.618);
        $data[] = array($key, 2, 2.0);
        $data[] = array($key, 3, 3.0);
        $data[] = array($key, 3.14159, 3.14159);
        $data[] = array($key, 4, $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        $key    = 'testEnum';
        $def = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'zero', 'one');
        $data[] = array($key, 'one', 'one');
        $data[] = array($key, 'two', 'two');
        $data[] = array($key, 'three', 'three');
        $data[] = array($key, 'four', 'one');
        $data[] = array($key, 0, 'one');
        $data[] = array($key, true, 'one');
        $data[] = array($key, false, 'one');
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictEnum';
        $def = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'zero', $def);
        $data[] = array($key, 'one', 'one');
        $data[] = array($key, 'two', 'two');
        $data[] = array($key, 'three', 'three');
        $data[] = array($key, 'four', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'testDate';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'tomorrow', date(TestRecord::$vars[$key]['format'], strtotime('tomorrow')));
        $data[] = array($key, '2011-09-28 12:13:14', '2011-09-28 12:13:14');
        $data[] = array($key, '1900-01-01', date(TestRecord::$vars[$key]['format'], 1));
        $data[] = array($key, 1234567890, '2009-02-13 18:31:30');
        $data[] = array($key, 1357924680, '2013-01-11 12:18:00');
        $data[] = array($key, 1111111111, '2005-03-17 20:58:31');
        $data[] = array($key, '', date(TestRecord::$vars[$key]['format'], 1));
        $data[] = array($key, 0, date(TestRecord::$vars[$key]['format'], 1));
        $data[] = array($key, true, date(TestRecord::$vars[$key]['format'], 1));
        $data[] = array($key, false, date(TestRecord::$vars[$key]['format'], 1));
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictDate';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'tomorrow', date(TestRecord::$vars[$key]['format'], strtotime('tomorrow')));
        $data[] = array($key, '2011-09-28 12:13:14', '2011-09-28 12:13:14');
        $data[] = array($key, '1900-01-01', $def);
        $data[] = array($key, 1234567890, '2009-02-13 18:31:30');
        $data[] = array($key, 1357924680, '2013-01-11 12:18:00');
        $data[] = array($key, 1111111111, '2005-03-17 20:58:31');
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'testUnixTime';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'tomorrow', strtotime('tomorrow'));
        $data[] = array($key, '2011-09-28 12:13:14', strtotime('2011-09-28 12:13:14'));
        $data[] = array($key, '1900-01-01', 1);
        $data[] = array($key, 1234567890, strtotime('2009-02-13 18:31:30'));
        $data[] = array($key, 1357924680, strtotime('2013-01-11 12:18:00'));
        $data[] = array($key, 1111111111, strtotime('2005-03-17 20:58:31'));
        $data[] = array($key, '', 1);
        $data[] = array($key, 0, 1);
        $data[] = array($key, true, 1);
        $data[] = array($key, false, 1);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictUnixTime';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'tomorrow', strtotime('tomorrow'));
        $data[] = array($key, '2011-09-28 12:13:14', strtotime('2011-09-28 12:13:14'));
        $data[] = array($key, '1900-01-01', $def);
        $data[] = array($key, 1234567890, strtotime('2009-02-13 18:31:30'));
        $data[] = array($key, 1357924680, strtotime('2013-01-11 12:18:00'));
        $data[] = array($key, 1111111111, strtotime('2005-03-17 20:58:31'));
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'testString';
        $def    = TestRecord::$vars[$key]['def'];
        $max    = TestRecord::$vars[$key]['max'];
        $data[] = array($key, 'jo', $def);
        $data[] = array($key, 'Joe', 'Joe');
        $data[] = array(
            $key, 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz',
            substr('abcdefghijklmnopqrstuvwxyzabcdefghijklmnop', 0, $max)
        );
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictString';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'jo', $def);
        $data[] = array($key, 'Joe', 'Joe');
        $data[] = array($key, 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz', $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        $key    = 'testEmail';
        $def    = TestRecord::$vars[$key]['def'];
        $max    = TestRecord::$vars[$key]['max'];
        $data[] = array($key, 'u@h.cc', $def);
        $data[] = array($key, 'user@host', $def);
        $data[] = array($key, 'user@host.com', 'user@host.com');
        $data[] = array($key, 'reallylonguser@reallylonghostreallylonghost.com', $def);
        $data[] = array($key, 'invalide@host@host.com', $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictEmail';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'u@h.cc', $def);
        $data[] = array($key, 'user@host', $def);
        $data[] = array($key, 'user@host.com', 'user@host.com');
        $data[] = array($key, 'reallylonguser@reallylonghostreallylonghost.com', $def);
        $data[] = array($key, 'invalide@host@host.com', $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, $def);
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        $key    = 'testIP';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, '0.0.0.0', '0.0.0.0');
        $data[] = array($key, '127.0.0.1', '127.0.0.1');
        $data[] = array($key, '255.255.255.255', '255.255.255.255');
        $data[] = array($key, '192.168.1.2', '192.168.1.2');
        $data[] = array($key, '292.168.1.2', $def);
        $data[] = array($key, '168.1.2', $def);
        $data[] = array($key, 'asdf', $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, '0.0.0.0');
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictIP';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, '0.0.0.0', '0.0.0.0');
        $data[] = array($key, '127.0.0.1', '127.0.0.1');
        $data[] = array($key, '255.255.255.255', '255.255.255.255');
        $data[] = array($key, '192.168.1.2', '192.168.1.2');
        $data[] = array($key, '292.168.1.2', $def);
        $data[] = array($key, '168.1.2', $def);
        $data[] = array($key, 'asdf', $def);
        $data[] = array($key, '', $def);
        $data[] = array($key, 0, '0.0.0.0');
        $data[] = array($key, true, $def);
        $data[] = array($key, false, $def);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        $key    = 'testBool';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'yes', true);
        $data[] = array($key, 'no', false);
        $data[] = array($key, 'true', true);
        $data[] = array($key, 'false', false);
        $data[] = array($key, 'ham', false);
        $data[] = array($key, 'eggs', false);
        $data[] = array($key, '', false);
        $data[] = array($key, 2, false);
        $data[] = array($key, 1, true);
        $data[] = array($key, 0, false);
        $data[] = array($key, true, true);
        $data[] = array($key, false, false);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictBool';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, 'yes', true);
        $data[] = array($key, 'no', false);
        $data[] = array($key, 'true', true);
        $data[] = array($key, 'false', false);
        $data[] = array($key, 'ham', false);
        $data[] = array($key, 'eggs', false);
        $data[] = array($key, '', false);
        $data[] = array($key, 2, false);
        $data[] = array($key, 1, true);
        $data[] = array($key, 0, false);
        $data[] = array($key, true, true);
        $data[] = array($key, false, false);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        $key    = 'testObject';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, serialize(array('one', 'two', 'three', 'four')), array('one', 'two', 'three', 'four'));
        $data[] = array(
            $key, serialize(array('one' => 'two', 'three' => 'four')),
            array('one' => 'two', 'three' => 'four')
        );
        $data[] = array($key, serialize(''), '');
        $data[] = array($key, serialize(0), 0);
        $data[] = array($key, serialize(true), true);
        $data[] = array($key, serialize(false), false);
        $data[] = array($key, serialize(null), null);
        $data[] = array($key, array('one', 'two', 'three', 'four'), array('one', 'two', 'three', 'four'));
        $data[] = array($key, array('one' => 'two', 'three' => 'four'), array('one' => 'two', 'three' => 'four'));
        $data[] = array($key, '', '');
        $data[] = array($key, 0, 0);
        $data[] = array($key, true, true);
        $data[] = array($key, false, false);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictObject';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, serialize(array('one', 'two', 'three', 'four')), array('one', 'two', 'three', 'four'));
        $data[] = array(
            $key, serialize(array('one' => 'two', 'three' => 'four')),
            array('one' => 'two', 'three' => 'four')
        );
        $data[] = array($key, serialize(''), '');
        $data[] = array($key, serialize(0), 0);
        $data[] = array($key, serialize(true), true);
        $data[] = array($key, serialize(false), false);
        $data[] = array($key, serialize(null), null);
        $data[] = array($key, array('one', 'two', 'three', 'four'), array('one', 'two', 'three', 'four'));
        $data[] = array($key, array('one' => 'two', 'three' => 'four'), array('one' => 'two', 'three' => 'four'));
        $data[] = array($key, '', '');
        $data[] = array($key, 0, 0);
        $data[] = array($key, true, true);
        $data[] = array($key, false, false);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'testArray';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, serialize(array('one', 'two', 'three', 'four')), serialize(array('one', 'two', 'three')));
        $data[] = array($key, serialize(array('one' => 'two', 'three' => 'four')), serialize(array('one' => 'two')));
        $data[] = array($key, serialize(''), serialize(array()));
        $data[] = array($key, serialize(0), serialize(array(0)));
        $data[] = array($key, serialize(true), serialize(array(true)));
        $data[] = array($key, serialize(false), serialize(array()));
        $data[] = array($key, serialize(null), serialize(array()));
        $data[] = array($key, array('one', 'two', 'three', 'four'), serialize(array('one', 'two', 'three')));
        $data[] = array($key, array('one' => 'two', 'three' => 'four'), serialize(array('one' => 'two')));
        $data[] = array($key, '', serialize(array()));
        $data[] = array($key, 0, serialize(array(0)));
        $data[] = array($key, true, serialize(array(true)));
        $data[] = array($key, false, serialize(array()));
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictArray';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, serialize(array('one', 'two')), serialize(array('one', 'two')));
        $data[] = array($key, serialize(array('one', 'two', 'three', 'four')), serialize($def));
        $data[] = array($key, serialize(array('one' => 'two', 'three' => 'four')), serialize($def));
        $data[] = array($key, serialize(''), serialize($def));
        $data[] = array($key, serialize(0), serialize($def));
        $data[] = array($key, serialize(true), serialize($def));
        $data[] = array($key, serialize(false), serialize($def));
        $data[] = array($key, serialize(null), serialize($def));
        $data[] = array($key, array('one', 'two'), serialize(array('one', 'two')));
        $data[] = array($key, array('one', 'two', 'three', 'four'), serialize($def));
        $data[] = array($key, array('one' => 'two', 'three' => 'four'), serialize($def));
        $data[] = array($key, '', serialize($def));
        $data[] = array($key, 0, serialize($def));
        $data[] = array($key, true, serialize($def));
        $data[] = array($key, false, serialize($def));
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);


        return $data;
    }

    /**
     * Tests integer setter/getter
     *
     * @param string $key    Field to set
     * @param mixed  $val    Value to set
     * @param mixed  $expect Expected result
     *
     * @return void
     *
     * @dataProvider dataTestSettersSame
     */
    public function testSettersSame($key, $val, $expect) {
        $this->obj->$key = TestRecord::$vars[$key]['def'];
        $this->obj->$key = $val;
        $result          = $this->obj->$key;
        $this->assertSame($expect, $result);
    }

    /**
     * Data Provider for test_i()
     *
     * @return array
     */
    public function dataTestSettersEquals() {
        $data = array();

        $key    = 'testJSON';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, json_encode(array('one', 'two', 'three', 'four')), array('one', 'two', 'three', 'four'));
        $data[] = array(
            $key, json_encode(array('one' => 'two', 'three' => 'four')),
            (object)array('one' => 'two', 'three' => 'four')
        );
        $data[] = array($key, json_encode(''), '');
        $data[] = array($key, json_encode(0), 0);
        $data[] = array($key, json_encode(true), true);
        $data[] = array($key, json_encode(false), false);
        $data[] = array($key, json_encode(null), null);
        $data[] = array($key, array('one', 'two', 'three', 'four'), array('one', 'two', 'three', 'four'));
        $data[] = array(
            $key, array('one' => 'two', 'three' => 'four'), (object)array('one' => 'two', 'three' => 'four')
        );
        $data[] = array($key, '', '');
        $data[] = array($key, 0, 0);
        $data[] = array($key, true, true);
        $data[] = array($key, false, false);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        $key    = 'strictJSON';
        $def    = TestRecord::$vars[$key]['def'];
        $data[] = array($key, json_encode(array('one', 'two', 'three', 'four')), array('one', 'two', 'three', 'four'));
        $data[] = array(
            $key, json_encode(array('one' => 'two', 'three' => 'four')),
            (object)array('one' => 'two', 'three' => 'four')
        );
        $data[] = array($key, json_encode(''), '');
        $data[] = array($key, json_encode(0), 0);
        $data[] = array($key, json_encode(true), true);
        $data[] = array($key, json_encode(false), false);
        $data[] = array($key, json_encode(null), null);
        $data[] = array($key, array('one', 'two', 'three', 'four'), array('one', 'two', 'three', 'four'));
        $data[] = array(
            $key, array('one' => 'two', 'three' => 'four'), (object)array('one' => 'two', 'three' => 'four')
        );
        $data[] = array($key, '', '');
        $data[] = array($key, 0, 0);
        $data[] = array($key, true, true);
        $data[] = array($key, false, false);
        $data[] = array($key, null, null);
        unset($def);
        unset($min);
        unset($max);

        return $data;
    }
        /**
     * Tests integer setter/getter
     *
     * @param string $key    Field to set
     * @param mixed  $val    Value to set
     * @param mixed  $expect Expected result
     *
     * @return void
     *
     * @dataProvider dataTestSettersEquals
     */
    public function testSettersEquals($key, $val, $expect) {
        $this->obj->$key = TestRecord::$vars[$key]['def'];
        $this->obj->$key = $val;
        $result          = $this->obj->$key;
        $this->assertEquals($expect, $result);
    }
}
