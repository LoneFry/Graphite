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
 * File        : /^/lib/Security.php
 *                core Security/Session manager
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

require_once SITE.CORE.'/models/Login.php';

/* 
 * Login Security - for authenticating and managing current user.
 * see Record.php for details.
 */
class Security{
	protected $Login=false;
	protected $ip;
	protected $ua;
	protected $UA;
	
	public function __construct(){
		$this->ip=$_SERVER['REMOTE_ADDR'];
		$this->ua=
			(isset($_SERVER['HTTP_USER_AGENT']     )?$_SERVER['HTTP_USER_AGENT']     :'').
			(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'').
			(isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'').
			(isset($_SERVER['HTTP_ACCEPT_CHARSET'] )?$_SERVER['HTTP_ACCEPT_CHARSET'] :'');
		$this->UA=sha1($this->ua);
		
		ini_set('session.use_only_cookies',1);
		session_start();
		if(!isset($_SESSION['ua'])){
			$_SESSION['ua']='';
		}
		if(!isset($_SESSION['ip'])){
			$_SESSION['ip']='';
		}
	
		if(isset($_SESSION['login_id']) && is_numeric($_SESSION['login_id'])){
			$Login=new Login(array('login_id'=>$_SESSION['login_id']));
			$Login->load();
			
			//if login disabled, fail
			if($Login->disabled == 1){
				$Login=false;
			}
			
			//if login configured so, test UA hash against last request
			elseif($Login->sessionStrength > 0 && $_SESSION['ua']!=$this->UA){
				$Login=false;
			}
			
			//if login configured so, test IP against last request
			elseif($Login->sessionStrength > 1 && $_SESSION['ip']!=$this->ip){
				$Login=false;
			}
			
			//if we got here, we should have a valid login, update usage data
			elseif(false!==$Login && 'Login'==get_class($Login)){
				$Login->dateActive="now";
				$_SESSION['ua']=$Login->UA=$this->UA;
				$_SESSION['ip']=$Login->lastIP=$this->ip;
				//move to $this->close() $Login->save();
				
				$this->Login=$Login;
			}
		}
	}
	
	public function authenticate($loginname,$password,$hashword=''){
		//Ensure at least one password form was submitted
		if(null==$password && ''==$hashword){
			return false;
		}
		
		//Search criteria starts with loginname, add password if passed
		$Login=array('loginname'=>$loginname);
		if(null!=$password){
			$Login['password']=sha1(substr($password,0,255));
		}
		$Login=new Login($Login);
		$Login->fill();
		
		//Check hashword if passed, compared to DB password
		if(''!=$hashword){
			if($hashword!=sha1(session_id().$Login->password)){
				return false;
			}
		}

		$Login->dateLogin="now";
		$Login->dateActive="now";
		$_SESSION['ua']=$Login->UA=$this->UA;
		$_SESSION['ip']=$Login->lastIP=$this->ip;
		//move to $this->close() $Login->save();
		
		$_SESSION['login_id']=$Login->login_id;

		$this->Login=$Login;

		session_regenerate_id();
		
		return true;
	}
	public function deauthenticate(){
		if(false!==$this->Login && 'Login'==get_class($this->Login)){
			$this->Login->dateLogout="now";
			$this->Login->save();
			$this->Login=false;
			session_destroy();
		}
	}
	
	public function close(){
		session_write_close();
		if($this->Login)$this->Login->save();
	}
	
	public function roleTest($s){
		if(false!==$this->Login && 'Login'==get_class($this->Login)){
			return $this->Login->roleTest($s);
		}
		return false;
	}
	
	public function __get($k){
		switch($k){
			case 'Login': return $this->Login;
			case 'ip': return $this->ip;
			case 'ua': return $this->ua;
			case 'UA': return $this->UA;
			default:
				$trace = debug_backtrace();
				trigger_error('Undefined property via __get(): '.$k.' in '.$trace[0]['file'].' on line '.$trace[0]['line'],E_USER_NOTICE);
				return null;
		}
	}	
	
	function __destruct(){
		$this->close();
	}
}
	