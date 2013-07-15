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

require_once SITE.'/^/lib/Record.php';
require_once SITE.'/^/lib/PasswordHasher.php';
require_once SITE.'/^/lib/SHA1PasswordHasher.php';
require_once SITE.'/^/lib/PBKDF2PasswordHasher.php';

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
        self::$table = G::$G['db']['tabl'].'Logins';
        self::$query = 'SELECT t.`login_id`, t.`loginname`, t.`password`, '
            .'t.`realname`, t.`referrer_id`, t.`comment`, t.`email`, t.`UA`, '
            .'t.`sessionStrength`, t.`lastIP`, t.`disabled`, t.`dateActive`, '
            .'t.`dateLogin`, t.`dateLogout`, t.`dateModified`, '
            .'t.`dateCreated`, t.`flagChangePass`, '
            .'GROUP_CONCAT(r.label) as roles '
            .'FROM `'.G::$G['db']['tabl'].'Logins` t '
            .'LEFT JOIN `'.G::$G['db']['tabl'].'Roles_Logins` rl '
                .'ON t.login_id = rl.login_id '
            .'LEFT JOIN `'.G::$G['db']['tabl'].'Roles` r '
                .'ON r.role_id = rl.role_id';
    }

    /**
     * wrap the parent constructor and set roles if passed
     *
     * @param bool|int|array $a pkey value|set defaults|set values
     * @param bool           $b set defaults
     */
    public function __construct($a = null, $b = null) {
        parent::__construct($a, $b);
        if (is_array($a) && isset($a['roles'])) {
            $this->roles = explode(',', $a['roles']);
        }
    }

    /**
     * process extra values returned by load()'s SELECT query
     *
     * @param array $row extra values for post processing
     *
     * @return array remaining unprocesed values
     */
    public function onload($row = array()) {
        if (isset($row['roles'])) {
            $this->roles = explode(',', $row['roles']);
            unset($row['roles']);
        }
        return $row;
    }

    /**
     * Pre-process Login for INSERT
     * Assure dateCreated and referrer_id have appropriate values
     *
     * @return void
     */
    public function oninsert() {
        $this->__set('dateCreated', NOW);
        if ($this->__get('referrer_id') < 1) {
            $this->__set('referrer_id', G::$S->Login->login_id);
        }
    }

    /**
     * Pre-process Login for UPDATE
     * Ensure dateModified is set only for non-meta fields
     *
     * @return void
     */
    public function onupdate() {
        $a = array('dateLogIn', 'dateLogout', 'dateActive', 'dateModified', 'dateCreated', 'lastIP');
        foreach (static::$vars as $k => $v) {
            if ($this->vals[$k] != $this->DBvals[$k] && !in_array($k, $a)) {
                $this->__set('dateModified', NOW);
                break;
            }
        }
    }

    /**
     * Indicate whether login has specified role
     *
     * @param string $role Role to test
     *
     * @return bool true if Login has Role, false otherwise
     */
    public function roleTest($role) {
        return is_array($this->roles) && in_array($role, $this->roles);
    }

    /**
     * Getter/Setter for loginname value
     *
     * @return string $this->loginname
     */
    public function loginname() {
        if (0 < count($a = func_get_args())) {
            if (strlen($a[0]) >= static::$vars['loginname']['min']
                && preg_match('/'.self::$labelRE.'/', $a[0])
            ) {
                $this->vals['loginname'] = substr($a[0], 0, static::$vars['loginname']['max']);
            }
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
            if (PasswordHasher::is_hash($a[0])) {
                $this->vals['password'] = $a[0];
            } elseif (strlen($a[0]) >= static::$vars['password']['min']) {
                $this->vals['password'] = PasswordHasher::hash_password($a[0]);
            }
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
        return PasswordHasher::test_password($password, $this->password);
    }

    /**
     * Get referring loginname
     *
     * @return string loginname of the referring Login
     */
    public function getReferrer() {
        if ($this->__get('referrer_id') > 0) {
            $referrer = new Login($this->__get('referrer_id'));
            $referrer->load();
            return $referrer->loginname;
        }
        return '';
    }

    /**
     * return number of logins per initial letter
     *
     * @return array Array indexed by letter containing counts per letter
     */
    public static function initials() {
        // get login counts per letter
        $letters = array('A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0,
                         'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0,
                         'K' => 0, 'L' => 0, 'M' => 0, 'N' => 0, 'O' => 0,
                         'P' => 0, 'Q' => 0, 'R' => 0, 'S' => 0, 'T' => 0,
                         'U' => 0, 'V' => 0, 'W' => 0, 'X' => 0, 'Y' => 0,
                         'Z' => 0);
        $query = "SELECT UPPER(LEFT(loginname,1)), count(loginname)"
            ." FROM `".self::$table."` GROUP BY UPPER(LEFT(loginname,1))";
        if (false !== $result = G::$m->query($query)) {
            while ($row = $result->fetch_array()) {
                $letters[$row[0]] = $row[1];
            }
        }
        return $letters;
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
        if (strlen($c) < 1) {
            return array();
        }
        $query = "SELECT t.`login_id`, t.`loginname`"
            ." FROM `".static::$table."` t"
            ." WHERE `loginname` LIKE '".G::$m->escape_string($c)."%'"
            ." ORDER BY `loginname`";
        if (false === $result = G::$m->query($query)) {
            return false;
        }
        if (0 == $result->num_rows) {
            $result->close();
            return array();
        }
        $a = array();
        while ($row = $result->fetch_assoc()) {
            $a[$row[static::$pkey]] = new static($row);
        }
        $result->close();

        return $a;
    }
}
Login::prime();
