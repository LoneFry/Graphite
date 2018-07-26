<?php
/**
 * Session - core Session Data Wrapper
 * File : /^/lib/Session.php
 *
 * PHP version 7.0
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

namespace Graphite\core;

/**
 * Session class - accessing persistent session data
 *
 * @category Graphite
 * @package  Core
 * @author   Tyler Uebele
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
class Session {
    /** @var Session $instance */
    private static $instance = null;

    /** @var bool $hash Hash of last $_SESSION state */
    private $hash = false;

    /** @var bool $open Indicates whether session is open */
    private $open = false;

    /** @var string $session_id PHP's Session ID for re-opening */
    private $session_id = null;

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct() {
    }

    /**
     * Create and return singleton instance
     *
     * @return Session
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Store a value to the Session
     *
     * @param string $key Session key to set
     * @param mixed  $val Session value to set
     *
     * @return mixed Requested value
     */
    public function set($key, $val) {
        $_SESSION[$key] = $val;

        return $val;
    }

    /**
     * Retrieve a value from the Session
     *
     * @param string $key Session key to get
     *
     * @return mixed Value in session for requested key
     */
    public function get($key) {
        if (!array_key_exists($key, $_SESSION)) {
            $trace = debug_backtrace();
            trigger_error('Undefined property via '.__METHOD__.': '
                .$key.' in '.$trace[1]['file'].' on line '.$trace[1]['line'],
                E_USER_NOTICE);

            return null;
        }

        return $_SESSION[$key];
    }

    /**
     * Verify a value in the Session
     *
     * @param string $key value to verify exists
     *
     * @return bool
     */
    public function exists($key) {
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Remove a value from the Session
     *
     * @param string $key Value to destroy
     *
     * @return void
     */
    public function drop($key) {
        unset($_SESSION[$key]);
    }

    /**
     * Set dirty bit false and call session_start
     *
     * @return bool pass through value from session_start()
     */
    public function start() {
        if (null != $this->session_id) {
            session_id($this->session_id);
        }
        if (true !== $this->open) {
            // If we already have a session, preserve its data for the re-open
            if (isset($_SESSION)) {
                $temp       = $_SESSION;
                $this->open = session_start();
                // Assign $_SESSION to the merged difference between itself and newer versions
                $_SESSION   = array_patch($this->initialSessionArray, $_SESSION, $temp);
            } else {
                $this->open = session_start();
                ksort($_SESSION);
                $this->hash = md5(json_encode($_SESSION));
                // Save the initial values of the newly created $_SESSION
                $this->initialSessionArray = $_SESSION;
            }
            $this->session_id = session_id();
        }

        return $this->open;
    }

    /**
     * If @_SESSION changed, and we're not open, open to save changed values first
     *
     * @return void
     */
    public function write_close() {
        // Sort and compare current session state to last known state
        ksort($_SESSION);
        $state = md5(json_encode($_SESSION));
        if ($state != $this->hash && true !== $this->open) {
            // Make sure we have an open session
            $this->start();
        }
        $this->open = false;
        // Store current session state
        $this->hash = $state;
        session_write_close();
    }

    /**
     * call session_regenerate_id() and store new ID
     *
     * @return bool
     */
    public function regenerate_id() {
        // Make sure we have an open session
        $this->start();
        $return = session_regenerate_id();
        $this->session_id = session_id();

        return $return;
    }

    /**
     * Call a PHP session_* function
     *
     * @param string $func Partial name of PHP session_* function to call
     * @param array  $argv Arguments to pass PHP session_* function
     *
     * @return mixed Return value of PHP session_* function
     */
    public function __call($func, $argv) {
        if ('cli' == php_sapi_name()) {
            return false;
        }
        if (function_exists('session_'.$func)) {
            // Make sure we have an open session
            $this->start();
            return call_user_func_array('session_'.$func, $argv);
        }
        $trace = debug_backtrace();
        trigger_error('Undefined property via '.__METHOD__.': '
            .$func.' in '.$trace[1]['file'].' on line '.$trace[1]['line'],
            E_USER_NOTICE);
    }
}
