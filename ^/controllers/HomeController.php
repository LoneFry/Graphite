<?php
/**
 * Home Controller - A default Home and Contact page Controller class
 * File : /^/controllers/HomeController.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

require_once LIB.'/Controller.php';

/**
 * HomeController class - A default Home and Contact page Controller class
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class HomeController extends Controller {
	protected $action = 'home';

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_home($argv) {
		G::$V->_template = 'Home.php';
		G::$V->_title    = G::$V->_siteName;
	}

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_contact($argv) {
		G::$V->_template = 'Home.Contact.php';
		G::$V->_title    = G::$V->_siteName.': Contact';
		G::$V->seed    =$seed    =(int)(isset($_POST['apple'])?$_POST['apple']:microtime(true));
		G::$V->from    =$from    =substr(md5($seed), -6);
		G::$V->subject =$subject =md5($from);
		G::$V->message =$message =md5($subject);
		G::$V->honey   =$honey   =md5($message);
		G::$V->honey2  =$honey2  =md5($honey);
		G::$V->_head .= '
		<style type="text/css">
			.c'.$honey.' {display:none;}
		</style>
';

		if (isset($_POST[$from])
			&& isset($_POST[$subject])
			&& isset($_POST[$message])
			&& isset($_POST[$honey])
			&& isset($_POST[$honey2])
		) {
			$loginname = G::$S->Login?G::$S->Login->loginname:'[not logged in]';
			$login_id  = G::$S->Login?G::$S->Login->login_id:0;
			if ('' != $_POST[$honey] || '' != $_POST[$honey2]) {
				G::msg('The field labeled "Leave Blank" was not left blank.  '
					   .'Your message has not been sent.  '
					   .'We check this to prevent automated mailers.');
			} elseif (false !== strpos($_POST[$from], "\n") || false !== strpos($_POST[$from], "\r")) {
				G::msg('The email address submitted contains a newline '
					   .'character.  Your message has not been sent.  '
					   .'We check this to prevent automated mailers.');
			} elseif (false !== strpos($_POST[$subject], "\n") || false !== strpos($_POST[$subject], "\r")) {
				G::msg('The subject submitted contains a newline character.  '
					   .'Your message has not been sent.  '
					   .'We check this to prevent automated mailers.');
			} else {
				mail(G::$G['siteEmail'], G::$G['contactFormSubject'].$_POST[$subject],
					'Login Info: '.$loginname.' - '.$login_id."\n"
					.'Specified Email Address: '.$_POST[$from]."\n"
					.'Subject: '.$_POST[$subject]."\n"
					.'Message: '."\n".$_POST[$message],
					'From: "'.G::$G['VIEW']['_siteName'].'" <'.G::$G['siteEmail'].">\n"
					."Reply-To: ".$_POST[$from]."\nX-Mailer: PHP/" . phpversion()
					);
				G::msg('Your message has been sent.');

				require_once SITE.CORE.'/models/ContactLog.php';

				$C = new ContactLog(array(
					'from'     => $_POST[$from],
					'subject'  => $_POST[$subject],
					'to'       => G::$G['siteEmail'],
					'body'     => $_POST[$message],
					'login_id' => $login_id,
				), true);
				$C->save();
			}
		} else {
			G::msg('Use the form below . . .');
		}
	}

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_contactLog($argv) {
		if (!G::$S->roleTest('Home/ContactLog')) {
			return parent::do_403($argv);
		}

		G::$V->_template = 'Home.ContactLog.php';
		G::$V->_title    = G::$V->_siteName.': Contact Log';

		require_once SITE.CORE.'/models/ContactLog.php';
		G::$V->log = ContactLog::some(100, 0, 'id', true);
	}
}

