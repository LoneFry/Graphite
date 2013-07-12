<?php
/**
 * Account Controller - performs user account related actions
 * File : /^/controllers/AccountController.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/**
 * AccountController class - performs user account related actions
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class AccountController extends Controller {
	protected $action = 'login';

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_login($argv) {
		G::$V->_template = 'Account.Login.php';
		G::$V->_title    = G::$V->_siteName.' : Check-in';

		G::$V->msg='';
		if (isset($_POST['l']) && isset($_POST['p'])) {
			G::$V->l = $_POST['l'];
			if (G::$S->authenticate($_POST['l'], $_POST['p'])) {
				G::$V->_template = 'Account.Loggedin.php';
			} else {
				G::$V->msg = 'Login Failed.';
			}
		} elseif (G::$S->Login) {
			G::$V->l = G::$S->Login->loginname;
		} else {
			G::$V->l = '';
		}
		G::$V->_URI = isset($_POST['_URI']) ? $_POST['_URI']
					: (isset($_GET['_URI']) ? $_GET['_URI'] : CONT);
		G::$V->_Lbl = isset($_POST['_Lbl']) ? $_POST['_Lbl']
					: (isset($_GET['_Lbl']) ? $_GET['_Lbl'] : 'Home');
	}

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_logout($argv) {
		G::$V->_template = 'Account.Logout.php';
		G::$V->_title    = G::$V->_siteName.' : Check-out';

		G::$S->deauthenticate();

		G::$V->_URI = isset($_POST['_URI']) ? $_POST['_URI'] : CONT;
		G::$V->_Lbl = isset($_POST['_Lbl']) ? $_POST['_Lbl'] : 'Home';
	}

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_recover($argv) {
		G::$V->_template = 'Account.Recover.php';
		G::$V->_title    = G::$V->_siteName.' : Recover Password';

		G::$V->msg = '';
		if (G::$S->Login) {
			G::$V->msg = "You are already Checked-in as <b>"
							.G::$S->Login->loginname."</b>.";
		}
		if (isset($_POST['loginname'])) {
			$Login = new Login(array('email' => $_POST['loginname']));
			$Login->fill();
			if (0 == $Login->login_id) {
				$Login = new Login(array('loginname' => $_POST['loginname']));
				$Login->fill();
			}
			if (0 == $Login->login_id) {
				G::$V->msg = 'Unable to find <b>'
					.htmlspecialchars($_POST['loginname'])
					.'</b>, please try again.';
			} else {
				$Login->password = $password='resetMe'.floor(rand(100, 999));
				$Login->flagChangePass = 1;
				$r = $Login->save();
				if (false === $r) {
					G::$V->msg = 'An Error occured trying to update your account.';
				} elseif (null === $r) {
					G::$V->msg = 'No changes detected, not trying to update your account.';
				} else {
					$to = $Login->email;
					$message = "\n\nA password reset has been requested for your [".G::$V->_siteName."] account.  "
						."The temporary password is below.  After you login you will be required to change your password."
						."\n\nLoginName: ".$Login->loginname
						."\nPassword: ".$password
						."\n\nIf you have any questions, please reply to this email to contact support.";
					$headers = array(
							'Message-ID'   => date("YmdHis").uniqid().'@'.$_SERVER['SERVER_NAME'],
							'To'           => $to,
							'Subject'      => '['.G::$V->_siteName.'] Password Reset',
							'From'         => G::$G['siteEmail'],
							'Reply-To'     => G::$G['siteEmail'],
							'MIME-Version' => '1.0',
							'Content-Type' => 'text/plain; charset=us-ascii',
							'X-Mailer'     => 'PHP/'.phpversion(),
					);
					$header = '';
					foreach ($headers as $k => $v) {
						$header .= $k.': '.$v."\r\n";
					}
					if (imap_mail($to, $headers['Subject'], $message, $header)) {
						G::$V->msg = 'A new password has been mailed to you.  When you get it, login below.';
						G::$V->_template = 'Account.Login.php';
						G::$V->_URI = CONT;
						G::$V->_Lbl = 'Home';
						G::$V->l = $Login->loginname;
					} else {
						G::$V->msg = 'Mail sending failed, please contact support for your password reset.';
					}
				}
			}
		}
	}

	/**
	 * action
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_edit($argv) {
		if (!G::$S->Login) {
			G::$V->_URI = CONT.'Account/edit';
			G::$V->_Lbl = 'Account Settings';
			return $this->do_login($argv);
		}

		G::$V->_template = 'Account.Edit.php';
		G::$V->_title    = G::$V->_siteName.' : Account Settings';

		if (isset($_POST['comment']) && isset($_POST['email']) &&
			isset($_POST['password1']) && isset($_POST['password2'])
		) {

			G::$S->Login->comment = $_POST['comment'];
			G::$S->Login->email = $_POST['email'];
			if ('' != $_POST['password1']) {
				$error = Security::validate_password($_POST['password1']);
				if ($_POST['password1'] != $_POST['password2']) {
					G::msg('Passwords do not match, please try again.', 'error');
				} elseif (G::$S->Login->test_password($_POST['password1'])) {
					G::msg('You cannot re-use your old password!', 'error');
				} elseif (true !== $error) {
					G::msg($error, 'error');
				} else {
					G::$S->Login->password = $_POST['password1'];
					G::$S->Login->flagChangePass = 0;
					$pass = true;
				}
			}
			if (true === $saved = G::$S->Login->save()) {
				if (isset($pass) && true === $pass) {
					G::msg('Your password was updated.');
				} else {
					G::msg('Your password was NOT updated.');
				}
				G::msg('Your account was updated.');
			} elseif (null === $saved) {
				G::msg('No changes detected.  Your account was not updated.');
			} else {
				G::msg('Update Failed :(', 'error');
			}
		}

		G::$V->email = G::$S->Login->email;
		G::$V->comment = G::$S->Login->comment;
	}
}
