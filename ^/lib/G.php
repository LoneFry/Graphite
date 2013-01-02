<?php
/** **************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /^/lib/G.php
 *                core object
 *                static class for scoping core Graphite objects & functions
 ****************************************************************************/

final class G {
	static
		$M,            // mysqli object
		$m,            // mysqli object with read-only connection
		$V,            // View object
		$C,            // Dispatcher object
		$S,            // Security / Session object
		$G = array();  // Gonfiguration array

	private static $_msg = array();

	/**
	 * private constructor to prevent instanciation
	 */
	private function __construct() {

	}


	/**
	 * log messages for output later
	 *
	 * @param string $s the message
	 *                  pass null to return the messages
	 *                  pass true to return the messages and clear the log
	 * @param string $c class, arbitrary, used at will by template on output
	 *
	 * @return void
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
	 * replace special characters with their common counterparts
	 *
	 * @param string $s the string to alter
	 *
	 * @return void
	 */
	public static function normalize_special_characters($s) {
		//‘single’ and “double” quot’s yeah.
		$s = str_replace(array(
			'â€œ',  // left side double smart quote
			'â€',  // right side double smart quote
			'â€˜',  // left side single smart quote
			'â€™',  // right side single smart quote
			'â€¦',  // elipsis
			'â€”',  // em dash
			'â€“'), // en dash
			array('"', '"', "'", "'", "...", "-", "-"),
			$s);
		return $s;
	}

	/**
	 * emit invokation info, and passed value
	 *
	 * @param string $v   value to var_dump
	 * @param bool   $die whether to exit when done
	 *
	 * @return void
	 */
	public static function croak($v = null, $die = true) {
		$d = debug_backtrace();
		echo '<div class="G__croak">'
			.'<div class="G__croak_info"><b>'.__METHOD__.'()</b> called'
			.(isset($d[1])?' in <b>'.(isset($d[1]['class'])?$d[1]['class'].$d[1]['type']:'').$d[1]['function'].'()</b>':'')
			.' at <b>'.$d[0]['file'].':'.$d[0]['line'].'</b></div>'
			.'<hr><div class="G__croak_value">';
		var_dump($v);
		echo '</div></div>';
		if ($die) {
			exit;
		}
	}

	/**
	 * close Security and mysqli objects in proper order
	 * This should be called before PHP cleanup to close things in order
	 * register_shutdown_function() is one way to do this.
	 *
	 * @return void
	 */
	public static function close() {
		if (G::$S) {
			G::$S->close();
		}
		if (G::$M) {
			G::$M->close();
		}
		if (G::$m) {
			G::$m->close();
		}
	}
}
//register G::close() to be called at shutdown
register_shutdown_function('G::close');
