<?php
/**
 * Account Controller - performs user account related actions
 * File : /^/controllers/AccountController.php
 *
 * PHP version 5.6
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
    /** @var string Default action */
    protected $action = 'login';

    private $_redirect = 'Dashboard';

    /**
     * Process Login form
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_login(array $argv = array(), array $request = array()) {
        $this->View->setTemplate('login', 'loginForm.php');
        $this->View->_template = 'Account.Login.php';
        $this->View->_title    = 'Check-in : ' . $this->View->_siteName;

        $this->View->msg = '';
        $this->View->l = '';
        if (isset($request['l']) && isset($request['p'])) {
            $this->View->l = $request['l'];
            if (G::$S->authenticate($request['l'], $request['p'])) {
                $this->View->_template = 'Account.Loggedin.php';
                $request['_URI'] = $this->requestPath($request['_URI']);
            } else {
                $this->View->msg = G::msg('Login Failed.', 'error');
            }
        } elseif (G::$S->Login) {
            $this->View->l = G::$S->Login->loginname;
        }

        if (!G::$S->Login) {
            $this->View->setTemplate('header', 'header.php');
            $this->View->setTemplate('footer', 'footer.php');
        }

        $this->View->_URI = isset($request['_URI'])
            ? $request['_URI']
            : (isset($argv['_URI'])
                ? $argv['_URI']
                : '/');
        $this->View->_Lbl = isset($request['_Lbl'])
            ? $request['_Lbl']
            : (isset($argv['_Lbl'])
                ? $argv['_Lbl']
                : 'Home');

        return $this->View;
    }

    /**
     * Logout, end session
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_logout(array $argv = array(), array $request = array()) {
        $this->View->_template = 'Account.Logout.php';
        $this->View->_title    = 'Check-out : ' . $this->View->_siteName;

        G::$S->deauthenticate();

        $this->View->_URI = isset($request['_URI']) ? $request['_URI'] : '/';
        $this->View->_Lbl = isset($request['_Lbl']) ? $request['_Lbl'] : 'Home';

        return $this->View;
    }

    /**
     * Password Recovery option
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_recover(array $argv = array(), array $request = array()) {
        $this->View->_template = 'Account.Recover.php';
        $this->View->_title    = 'Recover Password : ' . $this->View->_siteName;

        $this->View->msg = '';
        if (G::$S->Login) {
            $this->View->msg = "You are already Checked-in as <b>"
                            .G::$S->Login->loginname."</b>.";
        }
        if (isset($request['loginname'])) {
            $Login = new Login(array('email' => $request['loginname']));
            $this->DB->fill($Login);
            if (0 == $Login->login_id) {
                $Login = new Login(array('loginname' => $request['loginname']));
                $this->DB->fill($Login);
            }
            if (0 == $Login->login_id) {
                $this->View->msg = 'Unable to find <b>'
                    .htmlspecialchars($request['loginname'])
                    .'</b>, please try again.';
            } else {
                $Login->password = $password = 'resetMe'.floor(rand(100, 999));
                $Login->flagChangePass = 1;
                $diff = $Login->getDiff();
                $r = $this->DB->save($Login);
                if (false === $r) {
                    $this->View->msg = 'An Error occurred trying to update your account.';
                } elseif (null === $r) {
                    $this->View->msg = 'No changes detected, not trying to update your account.';
                } else {
                    Config::log('Logins', $Login->login_id, $diff);

                    /** @var Postmaster $Post */
                    $Post = G::build('Postmaster', Postmaster::HELP_ALERT);
                    $Post->to = $Login->email;
                    $Post->subject = '['.$this->View->_siteName.'] Password Reset';
                    $Post->from = G::$G['siteEmail'];
                    $Post->replyTo = G::$G['siteEmail'];
                    $Post->textBody = "\n\nA password reset has been requested for"
                        ." your [".$this->View->_siteName."] account.  "
                        ."The temporary password is below.  After you login"
                        ." you will be required to change your password."
                        ."\n\nLoginName: ".$Login->loginname
                        ."\nPassword: ".$password
                        ."\n\nIf you have any questions, please reply to this email to contact support.";
                    if ($Post->send()) {
                        $this->View->msg = 'A new password has been mailed to you.  When you get it, login below.';
                        $this->View->_template = 'Account.Login.php';
                        $this->View->l = $Login->loginname;
                        $this->_redirect('/Account/edit');
                    } else {
                        $this->View->msg = 'Mail sending failed, please contact support for your password reset.';
                    }
                }
            }
        }
        $this->View->_URI = isset($request['_URI'])
            ? $request['_URI']
            : (isset($argv['_URI'])
                ? $argv['_URI']
                : '/');
        $this->View->_Lbl = isset($request['_Lbl'])
            ? $request['_Lbl']
            : (isset($argv['_Lbl'])
                ? $argv['_Lbl']
                : 'Home');
        return $this->View;
    }

    /**
     * Edit current user's settings
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_edit(array $argv = array(), array $request = array()) {
        if (!G::$S->Login) {
            $this->View->_URI = '/Account/edit';
            $this->View->_Lbl = 'Account Settings';
            return $this->do_login($argv);
        }

        $this->View->_template = 'Account.Edit.php';
        $this->View->_title    = 'Account Settings : ' . $this->View->_siteName;

        if (isset($request['comment']) && isset($request['email'])
            && isset($request['password1']) && isset($request['password2'])
        ) {
            $save = true;
            G::$S->Login->comment = $request['comment'];
            G::$S->Login->email = $request['email'];
            if ('' != $request['password1']) {
                // Don't save if an error occurred with the password
                $save = false;
                $error = Security::validate_password($request['password1']);
                if ($request['password1'] != $request['password2']) {
                    G::msg('Passwords do not match, please try again.', 'error');
                } elseif (G::$S->Login->test_password($request['password1'])) {
                    G::msg('You cannot re-use your old password!', 'error');
                } elseif (true !== $error) {
                    G::msg($error, 'error');
                } else {
                    G::$S->Login->password = $request['password1'];
                    G::$S->Login->flagChangePass = 0;
                    $pass = true;
                    $save = true;
                }
            }
            if (($save === true) && (true === $saved = $this->DB->save(G::$S->Login))) {
                if (isset($pass) && true === $pass) {
                    G::msg('Your password was updated.');
                }
                G::msg('Your account was updated.');
            } elseif (!isset($saved) && false === $save) {
                G::msg('Update Failed!', 'error');
            } else {
                G::msg('No changes detected.  Your account was not updated.');
            }
        }
        $this->View->path = isset($request['path']) ? $request['path'] : '/' . $argv['_path'];
        $this->View->email = G::$S->Login->email;
        $this->View->comment = G::$S->Login->comment;

        return $this->View;
    }
}
