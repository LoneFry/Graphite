<?php
/**
 * G - static class for scoping core Graphite objects & functions
 * File : /^/lib/G.php
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
 * G class - static class for scoping core Graphite objects & functions
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */
final class G {
    /** @var mysqli_ mysqli_ object */
    public static $M;
    /** @var mysqli_ mysqli_ object  with read-only connection */
    public static $m;
    /** @var View View object */
    public static $V;
    /** @var Dispatcher (Controller) Dispatcher object */
    public static $C;
    /** @var Security Security / Session object */
    public static $S;
    /** @var array Graphite configuration array */
    public static $G = array();

    /** @var array Stores messages to be displayed to the user */
    private static $_msg = array();

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct() {

    }


    /**
     * Log messages for output later
     *
     * @param string $s the message
     *                  pass null to return the messages
     *                  pass true to return the messages and clear the log
     * @param string $c class, arbitrary, used at will by template on output
     *
     * @return mixed
     */
    public static function msg($s = null, $c = '') {
        if (null === $s) {
            return self::$_msg;
        }
        if (true === $s) {
            $msg = self::$_msg;
            self::$_msg = array();
            return $msg;
        }
        self::$_msg[] = array($s, $c);
    }

    /**
     * Replace special characters with their common counterparts
     *
     * @param string $s the string to alter
     *
     * @return string
     */
    public static function normalize_special_characters($s) {
        // ‘single’ and “double” quot’s yeah.
        $s = str_replace(array(
            '“',  // left side double smart quote
            '”',  // right side double smart quote
            '‘',  // left side single smart quote
            '’',  // right side single smart quote
            '…',  // ellipsis
            '—',  // em dash
            '–'), // en dash
            array('"', '"', "'", "'", "...", "-", "-"),
            $s);
        return $s;
    }

    /**
     * Emit invocation info, and passed value
     *
     * @param mixed $v   value to var_dump
     * @param bool  $die whether to exit when done
     *
     * @return void
     */
    public static function croak($v = null, $die = false) {
        $d = debug_backtrace();
        echo '<pre class="G__croak">'
            .'<div class="G__croak_info"><b>'.__METHOD__.'()</b> called'
            .(isset($d[1])
                ? ' in <b>'.(isset($d[1]['class'])
                    ? $d[1]['class'].$d[1]['type']
                    : ''
                    ).$d[1]['function'].'()</b>'
                : '')
            .' at <b>'.$d[0]['file'].':'.$d[0]['line'].'</b></div>'
            .'<hr><div class="G__croak_value">';
        // @codingStandardsIgnoreStart
        var_dump($v);
        // @codingStandardsIgnoreEnd
        echo '</div></pre>';
        if ($die) {
            exit;
        }
    }

    /**
     * Return a newline delimited call stack
     *
     * @return string call stack
     */
    public static function trace() {
        // get PHP's trace
        $d = debug_backtrace();
        // build printable trace
        $s = '';
        for ($i = 0; $i < count($d); $i++) {
            $s .= "\n";
            // If the called function is in a class, indicate it
            if (isset($d[$i]['class'])) {
                // If the called function is in a subclass, indicate it also
                if (isset($d[$i]['object']) && get_class($d[$i]['object']) != $d[$i]['class']) {
                    $s .= '['.get_class($d[$i]['object']).']';
                }
                $s .= $d[$i]['class'].$d[$i]['type'];
            }
            // Indicate the called function
            if (isset($d[$i]['function'])) {
                $s .= $d[$i]['function'].'() called at ';
            }
            // Indicate the file and line of the current call
            $s .= $d[$i]['file'].':'.$d[$i]['line'].";";
        }

        return $s;
    }

    /**
     * Close Security and mysqli objects in proper order
     * This should be called before PHP cleanup to close things in order
     * register_shutdown_function() is one way to do this.
     *
     * @return void
     */
    public static function close() {
        if (self::$S) {
            self::$S->close();
        }
        if (self::$M) {
            self::$M->close();
        }
        if (self::$m) {
            self::$m->close();
        }
    }

    /**
     * Provide short alias for Localizer::translate
     *
     * @return mixed Pass-through return value of Localizer::translate
     */
    public static function _() {
        return call_user_func_array(array('Localizer', 'translate'), func_get_args());
    }
}
// register G::close() to be called at shutdown
register_shutdown_function('G::close');
