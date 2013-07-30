<?php
/**
 * Login - Login (user) AR class
 * File : /^/models/Login.php
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
 * Login class - for managing site users, including current user.
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Record.php
 */
class Login extends Record {
    protected static $table = 'Logins';
    protected static $pkey  = 'login_id';
    protected static $query = '';

    protected static $vars = array(
        'login_id'        => array('type' => 'i' , 'min' => 1),
        'loginname'       => array('type' => 's' , 'strict' => true, 'min' => 3, 'max' => 255),
        'password'        => array('type' => 's' , 'strict' => true, 'min' => 3, 'max' => 255),
        'realname'        => array('type' => 's' , 'max' => 255),
        'email'           => array('type' => 'em', 'max' => 255),
        'comment'         => array('type' => 's' , 'max' => 255),
        'sessionStrength' => array('type' => 'e' , 'def' => 2, 'values' => array(0,1,2)),
        'UA'              => array('type' => 's' , 'min' => 40, 'max' => 40),
        'lastIP'          => array('type' => 'ip'),
        'dateActive'      => array('type' => 'ts', 'min' => 0),
        'dateLogin'       => array('type' => 'ts', 'min' => 0),
        'dateLogout'      => array('type' => 'ts', 'min' => 0),
        'dateModified'    => array('type' => 'ts', 'min' => 0),
        'dateCreated'     => array('type' => 'ts', 'min' => 0),
        'referrer_id'     => array('type' => 'i' , 'strict' => true, 'def' => 0, 'min' => 1),
        'disabled'        => array('type' => 'b' , 'def' => 0),
        'flagChangePass'  => array('type' => 'b' , 'def' => 1),
    );

    // a regex for determining valid loginnames
    protected static $labelRE = '^\w[\w\_\-\@\.\d]+$';
    // cache the Roles this Login has
    protected $roles = array();

    /**
     * prime() initialized static values, call below class definition
     *
     * @return void
     */
    public static function prime() {
    }

    /**
     * wrap the parent constructor and set roles if passed
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b set defaults
     */
    public function __construct($a = null, $b = null) {
        parent::__construct($a, $b);
    }

    /**
     * process extra values returned by load()'s SELECT query
     *
     * @param array $row extra values for post processing
     *
     * @return array remaining unprocesed values
     */
    public function onload($row = array()) {
        return $row;
    }

    /**
     * Pre-process Login for INSERT
     * Assure dateCreated and referrer_id have appropriate values
     *
     * @return void
     */
    public function oninsert() {
    }

    /**
     * Pre-process Login for UPDATE
     * Ensure dateModified is set only for non-meta fields
     *
     * @return void
     */
    public function onupdate() {
    }

    /**
     * Indicate whether login has specified role
     *
     * @param string $role Role to test
     *
     * @return bool true if Login has Role, false otherwise
     */
    public function roleTest($role) {
        return true;
    }

    /**
     * Getter/Setter for loginname value
     *
     * @return string $this->loginname
     */
    public function loginname() {
        $a = func_get_args();
        if (!empty($a)) {
            $this->vals['loginname'] = $a[0];
        }
        return $this->vals['loginname'];
    }

    /**
     * Getter/Setter for password value
     *
     * @return string $this->password
     */
    public function password() {
        if (0 < count($a = func_get_args())) {
            $this->vals['password'] = PasswordHasher::hash_password($a[0]);
        }
        return $this->vals['password'];
    }

    /**
     * Verify supplied password using configured PasswordHasher
     *
     * @param string $password the password to verify
     *
     * @return bool true if password verified, false if not
     */
    public function test_password($password) {
        return true;
    }

    /**
     * Get referring loginname
     *
     * @return string loginname of the referring Login
     */
    public function getReferrer() {
        return 'referrername';
    }

    /**
     * return number of logins per initial letter
     *
     * @return array Array indexed by letter containing counts per letter
     */
    public static function initials() {
        return array('A', 'B', 'C');
    }

    /**
     * SELECT all the records from the database using static::$query
     * add all set values to the WHERE clause, returns collection
     *
     * @param string $c Search for logins with loginnames starting with this
     *
     * @return array collection of Login objects starting wiht passed string
     */
    public static function forInitial($c = null) {
        $list = array();
        switch ($c) {
            // Note intended fall through.
            case 'D':
                $list[] = new static(
                    array('login_id' => 4, 'loginname' => 'Dave')
                );
            case 'C':
                $list[] = new static(
                    array('login_id' => 3, 'loginname' => 'Carol')
                );
            case 'B':
                $list[] = new static(
                    array('login_id' => 2, 'loginname' => 'Bob')
                );
            case 'A':
                $list[] = new static(
                    array('login_id' => 1, 'loginname' => 'Alice')
                );
                break;
            default:
                break;
        }
        return $list;
    }
}
Login::prime();
