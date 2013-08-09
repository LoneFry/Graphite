<?php
class TestRecord extends Record {
    protected static $table = 'Test';
    protected static $pkey = 'testInt';
    protected static $query = '';
    protected static $joiners = array('Login' => 'Test_Login');

    // vars array - all the information required to work with each record field
    //  val     the current value in this object instance
    //  type    the type, which defines which functions operate on it
    //  strict  declare whether or reject or adjust violating values
    //  def     default value, used by defaults() to set sane default values
    //  min     lowest number, earliest date, shortest string length
    //  max     highest number, latest date, longest string length
    //  values  valid choices for an enumeration (e) type variable
    //  format  string used by PHP's date() to format DateTime (dt) values
    //  ddl     Data Definition Language used by Record::create()
    public static $vars = array(
        'testInt'      => array('type' => 'i', 'def' => 4, 'min' => 3, 'max' => 42),
        'testFloat'    => array('type' => 'f', 'def' => 2.4, 'min' => 1.618, 'max' => 3.14159),
        'testEnum'     => array('type' => 'e', 'def' => 'two', 'values' => array('one', 'two', 'three')),
        'testDate'     => array('type' => 'dt', 'def' => '2013-08-07 13:58:00', 'min' => 1, 'max' => '2030-1-1', 'format' => 'Y-m-d H:i:s'),
        'testUnixTime' => array('type' => 'ts', 'def' => 1375898280, 'min' => 1, 'max' => '2030-1-1'),
        'testString'   => array('type' => 's', 'def' => 'Default String', 'min' => 3, 'max' => 42),
        'testEmail'    => array('type' => 'em', 'def' => 'user@domain.com', 'min' => 8, 'max' => 42),
        'testIP'       => array('type' => 'ip', 'def' => '127.0.0.1'),
        'testBool'     => array('type' => 'b', 'def' => false),
        'testObject'   => array('type' => 'o', 'def' => array('one', 'three'), 'min' => 3, 'max' => 72),
        'testJSON'     => array('type' => 'j', 'def' => array('one', 'three'), 'min' => 3, 'max' => 72),
        'testArray'    => array('type' => 'a', 'def' => array('one', 'three'), 'values' => array('one', 'two', 'three')),

        'strictInt'      => array('strict' => true, 'type' => 'i', 'def' => 4, 'min' => 3, 'max' => 42),
        'strictFloat'    => array('strict' => true, 'type' => 'f', 'def' => 2.4, 'min' => 1.618, 'max' => 3.14159),
        'strictEnum'     => array('strict' => true, 'type' => 'e', 'def' => 'two', 'values' => array('one', 'two', 'three')),
        'strictDate'     => array('strict' => true, 'type' => 'dt', 'def' => '2013-08-07 13:58:00', 'min' => 1,
                                  'max' => '2030-1-1', 'format' => 'Y-m-d H:i:s'),
        'strictUnixTime' => array('strict' => true, 'type' => 'ts', 'def' => 1375898280, 'min' => 1,
                                  'max' => '2030-1-1'),
        'strictString'   => array('strict' => true, 'type' => 's', 'def' => 'Default String', 'min' => 3, 'max' => 42),
        'strictEmail'    => array('strict' => true, 'type' => 'em', 'def' => 'user@domain.com', 'min' => 8, 'max' => 42),
        'strictIP'       => array('strict' => true, 'type' => 'ip', 'def' => '127.0.0.1'),
        'strictBool'     => array('strict' => true, 'type' => 'b', 'def' => false),
        'strictObject'   => array('strict' => true, 'type' => 'o', 'def' => array('one', 'three'), 'min' => 3, 'max' => 72),
        'strictJSON'     => array('strict' => true, 'type' => 'j', 'def' => array('one', 'three'), 'min' => 3, 'max' => 72),
        'strictArray'    => array('strict' => true, 'type' => 'a', 'def' => array('one', 'three'), 'values' => array('one', 'two', 'three')),
    );
}
TestRecord::prime();
