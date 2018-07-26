<?php
/**
 * LoginLog - AR class for logging log-ins
 * File : /^/models/LoginLog.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core\data;

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
    /** @var string Table name, un-prefixed */
    protected static $table = G_DB_TABL.'LoginLog';
    /** @var string Primary Key */
    protected static $pkey  = 'pkey';
    /** @var string Select query, without WHERE clause */
    protected static $query = '';
    /** @var array Table definition as collection of fields */
    protected static $vars  = array(
        'pkey'     => array('type' => 'i', 'min' => 1, 'guard' => true),
        'login_id' => array('type' => 'i', 'min' => 0),
        'ip'       => array('type' => 'ip', 'def' => G_REMOTE_ADDR),
        'ua'       => array('type' => 's', 'max' => 255),
        'iDate'    => array('type' => 'ts', 'min' => 0, 'def' => NOW)
    );
}
