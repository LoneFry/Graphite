<?php
/**
 * Security - core Security/Session manager
 * File : /^/lib/Security.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once SITE.CORE.'/models/Login.php';

/**
 * Security class - for authenticating and managing current user.
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/models/Login.php
 */
class Security {
    protected $Login = false;
    protected $ip;
    protected $ua;
    protected $UA;

    /**
     * Security constructor
     */
    public function __construct() {
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ua = strtolower(''
            . (isset($_SERVER['HTTP_USER_AGENT']     )?$_SERVER['HTTP_USER_AGENT']     :'')
            . (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'')
            . (isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'')
            . (isset($_SERVER['HTTP_ACCEPT_CHARSET'] )?$_SERVER['HTTP_ACCEPT_CHARSET'] :'')
            );
        $this->UA = sha1($this->ua);

        ini_set('session.use_only_cookies', 1);
        session_start();
        if (!isset($_SESSION['ua'])) {
            $_SESSION['ua'] = '';
        }
        if (!isset($_SESSION['ip'])) {
            $_SESSION['ip'] = '';
        }

        if (isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id']) && 0 < $_SESSION['login_id']) {
            $Login = new Login(array('login_id' => $_SESSION['login_id']));
            if (false === $Login->load()) {
                G::msg('Failed to load login from session, please login again.', 'error');
                $Login = false;

            //if login disabled, fail
            } elseif ($Login->disabled == 1) {
                G::msg('Your account is currently disabled.', 'error');
                $Login = false;

            //if login configured so, test UA hash against last request
            } elseif ($Login->sessionStrength > 0 && $Login->UA!=$this->UA) {
                G::msg('Your account was authenticated in a different browser, '
                       .'and multiple logins are disabled for your account.', 'error');
                $Login = false;

            //if login configured so, test IP against last request
            } elseif ($Login->sessionStrength > 1 && $Login->lastIP!=$this->ip) {
                G::msg('Your account was authenticated from a different computer/IP-address, '
                       .'and multiple logins are disabled for your account.', 'error');
                $Login = false;

            //if we got here, we should have a valid login, update usage data
            } elseif (false !== $Login && 'Login' == get_class($Login)) {
                $Login->dateActive = NOW;
                $_SESSION['ua'] = $Login->UA = $this->UA;
                $_SESSION['ip'] = $Login->lastIP = $this->ip;
                //move to $this->close()//$Login->save();

                $this->Login = $Login;
            }
        }

        if (false === $this->Login) {
            $_SESSION['login_id'] = 0;
        }
    }

    /**
     * Test login credentials
     *
     * @param string $loginname loginname attempting to login
     * @param string $password  provided password
     *
     * @return bool true on success, false on failure
     */
    public function authenticate($loginname, $password) {
        $Login = new Login(array('loginname' => $loginname));
        if (false === $Login->fill()) {
            return false;
        }

        if ($Login->disabled) {
            G::msg('Your account is currently disabled.', 'error');
            return false;
        }

        if (!$Login->test_password($password)) {
            return false;
        }

        $Login->dateLogin = NOW;
        $Login->dateActive = NOW;
        $_SESSION['ua'] = $Login->UA = $this->UA;
        $_SESSION['ip'] = $Login->lastIP = $this->ip;
        //move to $this->close() $Login->save();

        $_SESSION['login_id'] = $Login->login_id;
        $_SESSION['loginname'] = $Login->loginname;

        $this->Login = $Login;

        session_regenerate_id();

        include_once SITE.CORE.'/models/LoginLog.php';
        $LL = new LoginLog(array('login_id' => $Login->login_id, 'ua' => $this->ua), true);
        $LL->save();

        return true;
    }

    /**
     * Log current user out
     *
     * @return void
     */
    public function deauthenticate() {
        if (false !== $this->Login && 'Login' == get_class($this->Login)) {
            $this->Login->dateLogout = NOW;
            $this->Login->save();
            $this->Login = false;
            $_SESSION = array();

            // Be thorough, also delete the session cookie
            if (ini_get("session.use_cookies") && !headers_sent()) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', NOW-86400, $params["path"],
                    $params["domain"], $params["secure"], $params["httponly"]);
            }
            session_destroy();
        }
    }

    /**
     * Close session for current request
     *
     * @return void
     */
    public function close() {
        session_write_close();
        if ($this->Login) {
            $this->Login->save();
        }
    }

    /**
     * Test if current logged in user has Role by passing test to Login
     *
     * @param string $s role name
     *
     * @return bool true if current Login has role, false otherwise
     */
    public function roleTest($s) {
        if (false !== $this->Login && 'Login' == get_class($this->Login)) {
            return $this->Login->roleTest($s);
        }
        return false;
    }

    /**
     * __get magic method
     *
     * @param string $k property to get
     *
     * @return mixed requested value if found, null on failure
     */
    public function __get($k) {
        switch ($k) {
            case 'Login':
                return $this->Login;
            case 'ip':
                return $this->ip;
            case 'ua':
                return $this->ua;
            case 'UA':
                return $this->UA;
            default:
                $trace = debug_backtrace();
                trigger_error('Undefined property via __get(): '.$k.' in '
                              .$trace[0]['file'].' on line '.$trace[0]['line'],
                              E_USER_NOTICE);
                return null;
        }
    }

    /**
     * ensure session is closed properly
     *
     * @return void
     */
    function __destruct() {
        $this->close();
    }

    /**
     * Test password against policies
     *
     * @param string $password password to test
     *
     * @return bool|string true if passed|error text if failed
     */
    public static function validate_password($password) {
        //if there are no policies, everything passes!
        if (!isset(G::$G['SEC']['passwords'])) {
            return true;
        }

        extract(G::$G['SEC']['passwords']);

        //test what a password must be
        if (isset($require) && is_array($require)) {
            foreach ($require as $v) {
                if (!preg_match($v[0], $password)) {
                    return $v[1];
                }
            }
        }

        //test what a password must not be
        if (isset($deny) && is_array($deny)) {
            foreach ($deny as $v) {
                $matches = array();
                if (preg_match($v[0], $password, $matches)) {
                    return vsprintf($v[1], $matches);
                }
            }
        }

        return true;
    }
}
