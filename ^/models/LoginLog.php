<?php
/**
 * LoginLog - AR class for logging log-ins
 * File : /^/models/LoginLog.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * LoginLog class - AR class for logging log-ins
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Record.php
 */
class LoginLog extends Record {
    protected static $table = 'LoginLog';
    protected static $pkey  = 'pkey';
    protected static $query = '';
    protected static $vars  = array(
        'pkey'     => array('type' => 'i', 'min' => 1, 'guard' => true),
        'login_id' => array('type' => 'i', 'min' => 0),
        'ip'       => array('type' => 'ip'),
        'ua'       => array('type' => 's', 'max' => 255),
        'iDate'    => array('type' => 'ts', 'min' => 0)
    );

    /**
     * prime() initialized static values, call below class definition
     *
     * @return void
     */
    public static function prime() {
        parent::prime();
        self::$vars['ip']['def'] = $_SERVER['REMOTE_ADDR'];
        self::$vars['iDate']['def'] = NOW;
    }
}
LoginLog::prime();
