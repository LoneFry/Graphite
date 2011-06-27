<?php
/*****************************************************************************
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

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}


final class G{
	static 
		$M,          // mysqli object
		$m,          // mysqli object with read-only connection
		$V,          // View object
		$C,          // Controller object
		$S,          // Security / Session object
		$G=array();  // Gonfiguration array

	private static $msg=array();
	
	/*
	 * private constructor to prevent instanciation
	 */
	private function __construct(){}
	
	
	/*
	 * log messages for output later
	 * $s = the message
	 * $c = class, arbitrary, used at will by template on output
	 */
	public static function msg($s=null,$c=''){
		if(null==$s){
			return self::$msg;
		}
		self::$msg[]=array($s,$c);
	}

	/*
	 * replace special characters with their common counterparts
	 * $s = the string to alter
	 */
	public static function normalize_special_characters($s){
		//‘single’ and “double” quot’s yeah.
		$s=str_replace(array(
			'â€œ',  // left side double smart quote
			'â€',  // right side double smart quote
			'â€˜',  // left side single smart quote
			'â€™',  // right side single smart quote
			'â€¦',  // elipsis
			'â€”',  // em dash
			'â€“')  // en dash
			,array('"','"',"'","'","...","-","-")
			,$s)
		;
		return $s;
	}
	
	/*
	 * emit invokation info, and passed value
	 * $v = value to var_dump
	 * $die = whether to exit when done
	 */
	public static function croak($v=null,$die=true){
		$d=debug_backtrace();
		echo '<pre class="G__croak">'
			.'<div class="G__croak_info"><b>'.__METHOD__.'()</b> called'
			.(isset($d[1])?' in <b>'.(isset($d[1]['class'])?$d[1]['class'].$d[1]['type']:'').$d[1]['function'].'()</b>':'')
			.' at <b>'.$d[0]['file'].':'.$d[0]['line'].'</b></div>'
			.'<hr><div class="G__croak_value">';
		var_dump($v);
		echo '</div></pre>';
		if($die)exit;
	}
}
