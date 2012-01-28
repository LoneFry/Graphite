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
 * File        : /^/models/Login.php
 *                Login file AR class
 ****************************************************************************/

require_once LIB.'/Record.php';

/*
 * Login class - for managing site users, including current user.
 * see Record.php for details.
 */
class Login extends Record {
	protected static $table='Logins';
	protected static $pkey='login_id';
	protected static $query='SELECT t.`login_id`, t.`loginname`, t.`password`, t.`realname`, t.`referrer_id`, t.`comment`, t.`email`, t.`UA`, t.`sessionStrength`, t.`lastIP`, t.`disabled`, t.`dateActive`, t.`dateLogin`, t.`dateLogout`, t.`dateModified`, t.`dateCreated`, t.`flagChangePass`, GROUP_CONCAT(r.label) as roles FROM `Logins` t LEFT JOIN `Roles_Logins` rl ON t.login_id=rl.login_id LEFT JOIN `Roles` r ON r.role_id=rl.role_id';

	public static function prime(){
		self::$table=G::$G['db']['tabl'].'Logins';
		self::$query='SELECT t.`login_id`, t.`loginname`, t.`password`, t.`realname`, t.`referrer_id`, t.`comment`, t.`email`, t.`UA`, t.`sessionStrength`, t.`lastIP`, t.`disabled`, t.`dateActive`, t.`dateLogin`, t.`dateLogout`, t.`dateModified`, t.`dateCreated`, t.`flagChangePass`, GROUP_CONCAT(r.label) as roles FROM `'.G::$G['db']['tabl'].'Logins` t LEFT JOIN `'.G::$G['db']['tabl'].'Roles_Logins` rl ON t.login_id=rl.login_id LEFT JOIN `'.G::$G['db']['tabl'].'Roles` r ON r.role_id=rl.role_id';
	}
	protected static $vars=array(
		'login_id'=>        array('type'=>'i' ,'strict'=>false,'def'=>null,'min'=>1),
		'loginname'=>       array('type'=>'s' ,'strict'=>true ,'def'=>null,'min'=>3,'max'=>255),
		'password'=>        array('type'=>'s' ,'strict'=>true ,'def'=>null,'min'=>3,'max'=>255),
		'realname'=>        array('type'=>'s' ,'strict'=>false,'def'=>null,'max'=>255),
		'email'=>           array('type'=>'em','strict'=>false,'def'=>null,'max'=>255),
		'comment'=>         array('type'=>'s' ,'strict'=>false,'def'=>null,'max'=>255),

		'sessionStrength'=> array('type'=>'e' ,'strict'=>false,'def'=>2   ,'values'=>array(0,1,2)),
		'UA'=>              array('type'=>'s' ,'strict'=>false,'def'=>null,'min'=>40,'max'=>40),
		'lastIP'=>          array('type'=>'ip','strict'=>false,'def'=>null),

		'dateActive'=>      array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),
		'dateLogin'=>       array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),
		'dateLogout'=>      array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),
		'dateModified'=>    array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),
		'dateCreated'=>     array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),

		'referrer_id'=>     array('type'=>'i' ,'strict'=>true ,'def'=>0   ,'min'=>1),
		'disabled'=>        array('type'=>'b' ,'strict'=>false,'def'=>0   ),
		'flagChangePass'=>  array('type'=>'b' ,'strict'=>false,'def'=>1   )
	);

	// a regex for determining valid loginnames
	protected static $labelRE='^\w[\w\_\-\@\.\d]+$';
	// cache the Roles this Login has
	protected $roles=array();

	public function __construct($a=null,$b=null){
		parent::__construct($a,$b);
		if(is_array($a) && isset($a['roles'])){
			$this->roles=explode(',',$a['roles']);
		}
	}

	public function load(){
		$row=parent::load();
		if(isset($row['roles'])){
			$this->roles=explode(',',$row['roles']);
			unset($row['roles']);
		}
		return $row;
	}

	public function fill(){
		$row=parent::fill();
		if(isset($row['roles'])){
			$this->roles=explode(',',$row['roles']);
			unset($row['roles']);
		}
		return $row;
	}

	public function oninsert(){
		$this->__set('dateCreated',NOW);
		if($this->__get('referrer_id')<1)$this->__set('referrer_id',G::$S->Login->login_id);
	}

	public function onupdate(){
		$a=array('dateLogIn','dateLogout','dateActive','dateModified','dateCreated','lastIP');
		foreach(static::$vars as $k => $v){
			if($this->vals[$k]!=$this->DBvals[$k] && !in_array($k,$a)){
				$this->__set('dateModified',NOW);
				break;
			}
		}
	}

	public function roleTest($role){
		return is_array($this->roles) && in_array($role,$this->roles);
	}

	public function loginname(){
		if(0<count($a=func_get_args()))
		if(strlen($a[0])>=static::$vars['loginname']['min'] && preg_match('/'.self::$labelRE.'/', $a[0]))$this->vals['loginname']=substr($a[0],0,static::$vars['loginname']['max']);
		return $this->vals['loginname'];
	}
	public function password(){
		if(0<count($a=func_get_args())){
			if(preg_match('/[0-9a-f]{40}/i',$a[0]) && sha1('')!=$a[0])$this->vals['password']=$a[0];
			elseif(strlen($a[0])>=static::$vars['password']['min'])$this->vals['password']=sha1(substr($a[0],0,static::$vars['password']['max']));
		}
		return $this->vals['password'];
	}
	
	public function getReferrer(){
		if($this->__get('referrer_id') > 0){
			$referrer=new Login($this->__get('referrer_id'));
			$referrer->load();
			return $referrer->loginname;
		}
		return '';
	}
	
	public static function initials(){
		//get login counts per letter
		$letters=array('A'=>0,'B'=>0,'C'=>0,'D'=>0,'E'=>0,'F'=>0,'G'=>0,'H'=>0,'I'=>0,'J'=>0,'K'=>0,'L'=>0,'M'=>0,'N'=>0,'O'=>0,'P'=>0,'Q'=>0,'R'=>0,'S'=>0,'T'=>0,'U'=>0,'V'=>0,'W'=>0,'X'=>0,'Y'=>0,'Z'=>0);
		$query="SELECT UPPER(LEFT(loginname,1)), count(loginname) FROM `".self::$table."` group by UPPER(LEFT(loginname,1))";
		if(false===$result=G::$m->query($query)){
			//quietly fail//fErr($DB->error);
		}else{
			while($row=$result->fetch_array()){
				$letters[$row[0]]=$row[1];
			}
		}
		return $letters;
	}
	
	/* SELECT all the records from the database using static::$query 
	 * add all set values to the WHERE clause, returns collection
	 */
	public static function forInitial($c=null){
		if(strlen($c) < 1)return array();
		$query="SELECT t.`login_id`, t.`loginname` FROM `".static::$table."` t WHERE `loginname` LIKE '".G::$m->escape_string($c)."%' ORDER BY `loginname`";
		if(false===$result=G::$m->query($query)){
			return false;
		}
		if(0==$result->num_rows){
			$result->close();
			return array();
		}
		$a=array();
		while($row=$result->fetch_assoc()){
			$a[$row[static::$pkey]]=new static($row);
		}
		$result->close();

		return $a;
	}
}
Login::prime();
