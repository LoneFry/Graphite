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
 * File        : /^/lib/View.php
 *                core View processor
 *                manages which templates will be used
 *                manages which variables will be in scope
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

class View {
	protected $templates=array(
		'header'=>'header.php',
		'footer'=>'footer.php',
		'template'=>'404.php'
		);
	protected $includePath=null;

	public $vals=array('_meta'=>array(),'_script'=>array(),'_link'=>array());

	function __construct($cfg){
		//Check for and validate location of Actors
		if(isset(G::$G['includePath'])){
			foreach(explode(';',G::$G['includePath']) as $v){
				$s=realpath(SITE.$v.'/templates');
				if(file_exists($s)){
					$this->includePath[]=$s.'/';
				}
			}
		}
		if(0==count($this->includePath)){
			$this->includePath[]=SITE.CORE.'/templates/';
		}
		
		if(isset($cfg['_header'])){
			$this->setTemplate('header',$cfg['header']);
			unset($cfg['_header']);
		}
		if(isset($cfg['_footer'])){
			$this->setTemplate('footer',$cfg['footer']);
			unset($cfg['_footer']);
		}
		if(isset($cfg['_template'])){
			$this->setTemplate('template',$cfg['template']);
			unset($cfg['_template']);
		}
		if(isset($cfg['_meta']) && is_array($cfg['_meta']) && 0 < count($cfg['_meta'])){
			foreach($cfg['_meta'] as $name => $content){
				$this->_meta($name,$content);
			}
			unset($cfg['_meta']);
		}
		if(isset($cfg['_script']) && is_array($cfg['_script']) && 0 < count($cfg['_script'])){
			foreach($cfg['_script'] as $src){
				$this->_script($src);
			}
			unset($cfg['_script']);
		}
		if(isset($cfg['_link']) && is_array($cfg['_link']) && 0 < count($cfg['_link'])){
			foreach($cfg['_link'] as $a){
				$this->_link(
					isset($a['rel']  )?$a['rel']  :'',
					isset($a['type'] )?$a['type'] :'',
					isset($a['href'] )?$a['href'] :'',
					isset($a['title'])?$a['title']:''
				);
			}
			unset($cfg['_link']);
		}
		$this->vals=$this->vals+$cfg;
	}

	public function _meta($name=null,$content=null){
		if(null==$name){
			return $this->vals['_meta'];
		}
		$this->vals['_meta'][$name]=$content;
	}

	public function _script($src=null){
		if(null==$src){
			return $this->vals['_script'];
		}
		$this->vals['_script'][]=$src;
	}

	public function _link($rel=null,$type='',$href='',$title=''){
		if(null==$rel){
			return $this->vals['_link'];
		}
		$this->vals['_link'][]=array('rel'=>$rel,'type'=>$type,'href'=>$href,'title'=>$title);
	}
	
	public function in_realpath($path,$file){
		if(''==$file)return '';
		//Get the realpath of the file, then verify it exists in passed path
		$s=realpath($path.'/'.$file);
		if(false!==strpos($s,$path) && file_exists($s)){
			return substr($s,strlen($path));
		}
		return false;
	}

	public function setTemplate($template,$file){
		foreach($this->includePath as $dir){
			if(false!==$s=$this->in_realpath($dir,$file)){
				$this->templates[$template]=$s;
				break;
			}
		}
		return $this->templates[$template];
	}
	public function getTemplate($template){
		return $this->templates[$template];
	}

	function __set($name,$value){
		switch($name){
			case '_header': return $this->setTemplate('header',$value);
			case '_footer': return $this->setTemplate('footer',$value);
			case '_template': return $this->setTemplate('template',$value);
			default:
				$this->vals[$name]=$value;
		}
	}
	function __get($name){
		switch($name){
			case '_header': return $this->getTemplate('header');
			case '_footer': return $this->getTemplate('footer');
			case '_template': return $this->getTemplate('template');
			default:
				if(isset($this->vals[$name]))return $this->vals[$name];
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$name.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
		}
	}
	/* __isset magic method restores the normal operation of isset()
	 */
	public function __isset($k){
		return array_key_exists($k,$this->vals);
	}
	
	/* __unset magic method restores the normal operation of unset()
	 */
	public function __unset($k){
		unset($this->vals[$k]);
	}
	
	public function render($_template='template'){
		extract($this->vals);
		foreach($this->includePath as $v){
			if(file_exists($v.$this->templates[$_template])){
				include_once $v.$this->templates[$_template];
				return true;
			}
		}
		return false;
	}
}
function html($s){echo htmlspecialchars($s);}
function get_header(){G::$V->render('header');}
function get_footer(){G::$V->render('footer');}
function get_template(){G::$V->render();}
