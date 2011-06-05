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
 * File        : /^/lib/Controller.php
 *                core Controller
 *                dispatches Actors to perform requested Actions
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

class Controller {
	protected $actor='404';
	protected $actorPath='';
	protected $actor404='404';
	protected $actor404Path='';
	protected $action='';
	protected $includePath=array();
	protected $params=array();

	function __construct($cfg){
		//Check for and validate location of Actors
		if(isset(G::$G['includePath'])){
			foreach(explode(';',G::$G['includePath']) as $v){
				$s=realpath(SITE.$v.'/actors');
				if(file_exists($s)){
					$this->includePath[]=$s.'/';
				}
			}
		}
		if(0==count($this->includePath)){
			$this->includePath[]=SITE.CORE.'/actors/';
		}
		
		//set config default first, incase passed path is not found
		if(isset($cfg['actor404'])){$this->actor404($cfg['actor404']);}
		//Path based requests take priority, check for path and parse
		if(isset($cfg['path'])){
			$a=explode('/',trim($cfg['path'],'/'));
			if(count($a) > 0){$this->actor(urldecode(array_shift($a)));}
			if(count($a) > 0){$this->action=urldecode(array_shift($a));}
			$this->params=$a;//what's left of the request path
		}else{
			//If Path was not passed, check for individual configs
			if(isset($cfg['actor'])){$this->actor($cfg['actor']);}
			if(isset($cfg['action'])){$this->action=$cfg['action'];}
			if(isset($cfg['params'])){$this->params=explode('/',trim($cfg['params'],'/'));}
		}
		
		//If we have other params, pair them up and add them to the _GET array
		//Yes, this will result in redundancy: paired and unpaired; intentional
		//I wonder if this belongs elsewhere
		if(0<count($this->params)){
			$a=$this->params;
			while(count($a) > 0){$this->params[urldecode(array_shift($a))]=urldecode(array_shift($a));}
			$_GET=$_GET+$this->params;//add params to _GET array without overriding
		}
	}
	
	//Set and return actor name
	//Verifies Actor file exists in configured location
	public function actor404(){
		if(0<count($a=func_get_args())){
			foreach($this->includePath as $v){
				$s=realpath($v.$a[0].'.php');
				if(false!==strpos($s,$v) && file_exists($s)){
					$this->actor404=$a[0];
					$this->actor404Path=$v;
					break;
				}
			}
		}
		return $this->actor404;
	}
	
	//Set and return actor name
	//Verifies Actor file exists in configured location
	public function actor(){
		if(0<count($a=func_get_args())){
			foreach($this->includePath as $v){
				$s=realpath($v.$a[0].'.php');
				if(false!==strpos($s,$v) && file_exists($s)){
					$this->actor=$a[0];
					$this->actorPath=$v;
					break;
				}else{
					$this->actor=$this->actor404;
					$this->actorPath=$this->actor404Path;
				}
			}
		}
		return $this->actor;
	}
	
	//Perform specified action in specified Actor
	public function Act(){
		require_once LIB.'/Actor.php';
		require_once $this->actorPath.$this->actor.'.php';
		$Actor='A'.$this->actor;
		$Actor=new $Actor($this->params);
		if(''!=$this->action)$Actor->action=$this->action;
		$Actor->act($this->params);
	}
}
