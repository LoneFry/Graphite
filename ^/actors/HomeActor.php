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
 * File        : /^/actors/HomeActor.php
 *                A default Actor class
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

class HomeActor extends Actor{
	protected $action='home';
	
	public function do_home($params){
		G::$V->_template='Home.php';
		G::$V->_title=G::$V->_siteName;
	}

	public function do_contact($params){
		G::$V->_template='Contact.php';
		G::$V->_title=G::$V->_siteName.': Contact';
		G::$V->_script(CORE.'/js/ajas.Email.js');
		G::$V->seed   =$seed=(int)(isset($_POST['apple'])?$_POST['apple']:microtime(true));
		G::$V->from   =$from   =substr(md5($seed),-6);
		G::$V->subject=$subject=md5($from);
		G::$V->message=$message=md5($subject);
		G::$V->honey  =$honey  =md5($message);
		G::$V->honey2 =$honey2 =md5($honey);

		if (isset($_POST[$from]) && 
			isset($_POST[$subject]) && 
			isset($_POST[$message]) && 
			isset($_POST[$honey]) && 
			isset($_POST[$honey2])) 
		{
			$loginname=G::$S->Login?G::$S->Login->loginname:'[not logged in]';
			$login_id=G::$S->Login?G::$S->Login->login_id:0;
			if (''!=$_POST[$honey] || ''!=$_POST[$honey2]) {
				G::msg('The field labeled "Leave Blank" was not left blank.  Your message has not been sent.  We check this to prevent automated mailers.');
			} elseif (false!==strpos($_POST[$from],"\n") || false!==strpos($_POST[$from],"\r")) {
				G::msg('The email address submitted contains a newline character.  Your message has not been sent.  We check this to prevent automated mailers.');
			} elseif (false!==strpos($_POST[$subject],"\n") || false!==strpos($_POST[$subject],"\r")) {
				G::msg('The subject submitted contains a newline character.  Your message has not been sent.  We check this to prevent automated mailers.');
			} else {
				mail(G::$G['siteEmail'],'['.G::$V->_siteName.'] message: '.$_POST[$subject],
					'Login Info: '.$loginname.' - '.$login_id."\n"
					.'Specified Email Address: '.$_POST[$from]."\n"
					.'Subject: '.$_POST[$subject]."\n"
					.'Message: '."\n".$_POST[$message],
					'From: "'.$_POST[$from].'" <'.G::$G['siteEmail'].">\n"
					."Reply-To: ".$_POST[$from]."\nX-Mailer: PHP/" . phpversion()
					);
				G::msg('Your message has been sent.');

				require_once SITE.CORE.'/models/ContactLog.php';

				$C=new ContactLog(array(
					'from'=>$_POST[$from],
					'subject'=>$_POST[$subject],
					'to'=>G::$G['siteEmail'],
					'body'=>$_POST[$message],
					'login_id'=>$login_id
				),true);
				$C->save();
			}
		}else{
			G::msg('Use the form below . . .');
		}
	}
	public function do_contactLog($params){
		if(!G::$S->roleTest('Home/ContactLog'))return parent::do_403($params);

		G::$V->_template='ContactLog.php';
		G::$V->_title=G::$V->_siteName.': Contact Log';

		require_once SITE.CORE.'/models/ContactLog.php';
		$C=new ContactLog();
		G::$V->log=$C->search(0,100,'id',true);
	}
}
