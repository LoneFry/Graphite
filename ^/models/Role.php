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
 * File        : /^/models/Role.php
 *                Role AR class
 ****************************************************************************/

require_once LIB.'/Record.php';

/*
 * Role class - for managing site roles/responsiblities
 * see Record.php for details.
 */
class Role extends Record {
	protected static $table='Roles';
	protected static $pkey='role_id';
	protected static $query='SELECT t.`role_id`, t.`label`, t.`description`, t.`creator_id`, t.`disabled`, t.`dateModified`, t.`dateCreated` FROM `Roles` t';

	public static function prime(){
		self::$table=G::$G['db']['tabl'].'Roles';
		self::$query='SELECT t.`role_id`, t.`label`, t.`description`, t.`creator_id`, t.`disabled`, t.`dateModified`, t.`dateCreated` FROM `'.static::$table.'` t';
	}
	protected static $vars=array(
		'role_id'=>        array('type'=>'i' ,'strict'=>false,'def'=>null,'min'=>1),
		'label'=>          array('type'=>'s' ,'strict'=>true ,'def'=>null,'min'=>3,'max'=>255),
		'description'=>    array('type'=>'s' ,'strict'=>true ,'def'=>null,'min'=>3,'max'=>255),
		'creator_id'=>     array('type'=>'i' ,'strict'=>true ,'def'=>0   ,'min'=>1),
		'disabled'=>       array('type'=>'b' ,'strict'=>false,'def'=>0   ),
		'dateModified'=>   array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0),
		'dateCreated'=>    array('type'=>'ts','strict'=>false,'def'=>null,'min'=>0)
	);

	public function oninsert(){
		$this->__set('dateCreated',NOW);
		if($this->__get('creator_id')<1)$this->__set('creator_id',G::$S->Login->login_id);
	}

	public function onupdate(){
		$this->__set('dateModified',NOW);
	}
	public function getCreator(){
		if($this->__get('creator_id') > 0){
			$creator=new Login($this->__get('creator_id'));
			$creator->load();
			return $creator->loginname;
		}
		return '';
	}
	public function getMembers($detail='grantor_id'){
		if($detail=='loginname'){
			$query="SELECT l.`login_id`, l.`loginname` "
				."FROM `".G::$G['db']['tabl']."Logins` l, `".G::$G['db']['tabl']."Roles_Logins` rl "
				."WHERE l.`login_id`=rl.`login_id` AND rl.`role_id`=".$this->__get('role_id')
				." ORDER BY l.`loginname`"
			;
		}else{
			$query="SELECT rl.`login_id`, rl.`grantor_id` "
				."FROM `".G::$G['db']['tabl']."Roles_Logins` rl "
				."WHERE rl.`role_id`=".$this->__get('role_id')
			;
		}
		if(false===$result=G::$m->query($query)){
			return false;
		}
		if(0==$result->num_rows){
			$result->close();
			return array();
		}
		$a=array();
		while($row=$result->fetch_array()){
			$a[$row[0]]=$row[1];
		}
		$result->close();
		return $a;
	}
	public function grant($login_id){
		if(!is_numeric($login_id))return false;
		$grantor=G::$S->Login?G::$S->Login->login_id:0;
		$query="INSERT INTO `".G::$G['db']['tabl']."Roles_Logins` (`role_id`,`login_id`,`grantor_id`,`dateCreated`) "
			."VALUES (".$this->__get('role_id').",".$login_id.",".$grantor.",".NOW.")";
		if(G::$M->query($query)){
			return true;
		}
		return false;
	}
	public function revoke($login_id){
		if(!is_numeric($login_id))return false;
		$query="DELETE FROM `".G::$G['db']['tabl']."Roles_Logins` "
			."WHERE `role_id`=".$this->__get('role_id')." AND `login_id`=".$login_id;
		if(G::$M->query($query)){
			return true;
		}
		return false;
	}
}
Role::prime();
