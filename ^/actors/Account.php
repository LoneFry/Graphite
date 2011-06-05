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
 *                Account Actor class - performs user account related actions
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

class AAccount extends Actor{
	protected $action='login';
	
	public function do_login($params){
		G::$V->template='Account.Login.php';
		G::$V->title=G::$V->siteName.' : Check-in';
		
		G::$V->msg='';
		if(isset($_POST['l']) && isset($_POST['p']) && isset($_POST['h'])){
			G::$V->l=$_POST['l'];
			if(G::$S->authenticate($_POST['l'],$_POST['p'],$_POST['h'])){
				G::$V->template='Account.Loggedin.php';
			}else{
				G::$V->msg='Login Failed.';
			}
		}elseif(G::$S->Login){
			G::$V->l=G::$S->Login->loginname;
		}else{
			G::$V->l='';
		}
		if(!isset(G::$V->sURI))G::$V->sURI=isset($_POST['sURI'])?$_POST['sURI']:CONT;
		if(!isset(G::$V->sLbl))G::$V->sLbl=isset($_POST['sLbl'])?$_POST['sLbl']:'Home';
	}

	public function do_logout($params){
		G::$V->template='Account.Logout.php';
		G::$V->title=G::$V->siteName.' : Check-out';
		
		G::$S->deauthenticate();

		G::$V->sURI=isset($_POST['sURI'])?$_POST['sURI']:CONT;
		G::$V->sLbl=isset($_POST['sLbl'])?$_POST['sLbl']:'Home';
	}
	
	public function do_recover($params){
		G::$V->template='Account.Recover.php';
		G::$V->title=G::$V->siteName.' : Recover Password';
		
		G::$V->msg='';
		if(G::$S->Login){
			G::$V->msg="You are already Checked-in as <b>".G::$S->Login->loginname."</b>.";
		}
		if(isset($_POST['loginname'])){
			$Login=new Login(array('email'=>$_POST['loginname']));
			$Login->fill();
			if(0==$Login->login_id){
				$Login=new Login(array('loginname'=>$_POST['loginname']));
				$Login->fill();
			}
			if(0==$Login->login_id){
				G::$V->msg='Unable to find <b>'.htmlspecialchars($_POST['loginname']).'</b>, please try again.';
			}else{
				$Login->password=$password='resetMe'.floor(rand(100,999));
				$Login->flagChangePass=1;
				if(false===$Login->save()){
					G::$V->msg='An Error occured trying to update your account.';
				}elseif(null===$Login->save()){
					G::$V->msg='No changes detected, not trying to update your account.';
				}else{
					$to=$Login->email;
					$message="\n\nA password reset has been requested for your [".G::$V->siteName."] account.  "
						."The temporary password is below.  After you login you will be required to change your password."
						."\n\nLoginName: ".$Login->loginname
						."\nPassword: ".$password
						."\n\nIf you have any questions, please reply to this email to contact support.";
					$headers=array(
							'Message-ID'=>date("YmdHis").uniqid().'@'.$_SERVER['SERVER_NAME'],
							'To'=>$to,
							'Subject'=>'['.G::$V->siteName.'] Password Reset',
							'From'=>G::$G['siteEmail'],
							'Reply-To'=>G::$G['siteEmail'],
							'MIME-Version'=>'1.0',
							'Content-Type'=>'text/plain; charset=us-ascii',
							'X-Mailer'=>'PHP/'.phpversion()
					);
					$header='';
					foreach($headers as $k => $v){
						$header.=$k.': '.$v."\r\n";
					}
					if(imap_mail($to,$headers['Subject'],$message,$header)){
						G::$V->msg='A new password has been mailed to you.  When you get it, login below.';
						G::$V->template='Account.Login.php';
						G::$V->sURI=CONT;
						G::$V->sLbl='Home';
						G::$V->l=$Login->loginname;
					}else{
						G::$V->msg='Mail sending failed, please contact support for your password reset.';
					}
				}
			}
		}
	}
	
	public function do_edit($params){
		if(!G::$S->Login){
			G::$V->sURI=CONT.'Account/edit';
			G::$V->sLbl='Account Settings';
			return $this->do_login($params);
		}
		
		G::$V->template='Account.Edit.php';
		G::$V->title=G::$V->siteName.' : Account Settings';
		
		G::$V->msg='';
		if (isset($_POST['comment']) && isset($_POST['email']) && 
			isset($_POST['password1']) && isset($_POST['password2'])) {
		
			G::$S->Login->comment=$_POST['comment'];
			G::$S->Login->email=$_POST['email'];
			if($_POST['password1']==$_POST['password2'] && sha1('')!=$_POST['password1'] && strlen($_POST['password1'])>=4) {
				G::$S->Login->password=$_POST['password1'];
				G::$S->Login->flagChangePass=0;
				$pass=true;
			}
			if (true===G::$S->Login->save()) {
				G::$V->msg='Your account '.(isset($pass)&&true===$pass?'including':'except').' your password was updated.';
			} elseif (null===G::$S->Login->save()) {
				G::$V->msg='No changes detected.  Your account was not updated.';
			} else {
				G::$V->msg='Update Failed :(';
			}
		}
		
		G::$V->email=G::$S->Login->email;
		G::$V->comment=G::$S->Login->comment;
	}
}
