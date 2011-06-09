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
 * File        : /^/actors/Actor.php
 *                Actor base class
 *                
 * Actors are dispatched by the Controller
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

/* 
 * Actor class - used as a base class for MVC Actor classes
 * a trivial example extension is in 404.php
 */
abstract class Actor { //perform action requested of controller
	protected $action='404';
	
	public function do_404($params){
		header("HTTP/1.0 404 File Not Found");
		G::$V->_template='404.php';
		G::$V->_title='Requested Page Not Found';
	}
	public function do_403($params){
		header("HTTP/1.0 403 Forbidden");
		G::$V->_template='403.php';
		G::$V->_title='Permission Denied';
	}
	
	public function action(){
		if(0<count($a=func_get_args())){
			if(method_exists($this,'do_'.$a[0])){
				$this->action=$a[0];
			}else{
				$this->action='404';
			}
		}
		return $this->action;
	}
	
	public function act($a){
		$func='do_'.$this->action;
		$this->$func($a);
	}
	
	function __set($name,$value){
		switch($name){
			case 'action': return $this->action($value);
			default:
				$trace = debug_backtrace();
				trigger_error('Undefined property via __set(): '.$name.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
		}
	}
	function __get($name){
		switch($name){
			case 'action': return $this->action;
			default:
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$name.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
		}
	}

}

