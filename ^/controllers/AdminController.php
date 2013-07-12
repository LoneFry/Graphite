<?php
/**
 * Admin Controller - performs Administrative actions
 * File : /^/controllers/AdminController.php
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
 * AdminController class - performs Administrative actions
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class AdminController extends Controller {
    protected $action = 'list';

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_list($argv) {
        if (!G::$S->roleTest('Admin')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.list.php';
        G::$V->_title    = 'Administrative Options';
    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_Login($argv) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.Login.php';
        G::$V->_title    = 'Select Login';

        if (isset($argv[1])) {
            $l = Login::forInitial($argv[1]);
            if ($l && 1<count($l)) {
                G::$V->list = $l;
            } elseif ($l && 1 == count($l)) {
                $L = array_shift($l);
                return $this->do_LoginEdit(array($L->login_id));
            }
        } else {
            $l = new Login();
            G::$V->list = $l->search(50, 0, 'loginname');
        }
        G::$V->letters = Login::initials();
    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_LoginAdd($argv) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.LoginAdd.php';
        G::$V->_title    = 'Add Login';

        if (isset($_POST['loginname']) && isset($_POST['realname']) &&
            isset($_POST['pass1']) && isset($_POST['pass2']) &&
            isset($_POST['email1']) && isset($_POST['email2']) &&
            isset($_POST['sessionStrength']) && isset($_POST['flagChangePass']) &&
            isset($_POST['disabled'])
        ) {
            $insert = true;
            if ($_POST['email1'] != $_POST['email2']) {
                G::msg('While Adding: The two email address fields do not match, which one is correct?', 'error');
                $insert = false;
            }
            $_POST['email'] = $_POST['email1'];

            if ('' == $_POST['pass1']) {
                G::msg('While Adding: You must specify a password.', 'error');
                $insert = false;
            } elseif ($_POST['pass1'] != $_POST['pass2']) {
                G::msg('While Adding: The two password fields do not match, which one is correct?', 'error');
                $insert = false;
            } elseif (isset(G::$G['SEC']['passwords']['enforce_in_admin'])
                && G::$G['SEC']['passwords']['enforce_in_admin']
                && true !== $error = Security::validate_password($_POST['pass1'])
            ) {
                G::msg($error, 'error');
                $insert = false;
            } else {
                $_POST['password'] = $_POST['pass1'];
            }

            $L = new Login($_POST, true);
            if (!$L->loginname) {
                G::msg('While Adding: Invalid loginname supplied: '.htmlspecialchars($_POST['loginname']), 'error');
                $insert = false;
            }
            if (!$L->email) {
                G::msg('While Adding: Invalid email supplied: '.htmlspecialchars($_POST['email']), 'error');
                $insert = false;
            }

            if ($insert && $result=$L->insert()) {
                G::msg('Login Added');
                return $this->do_LoginEdit(array($L->login_id));
            } elseif ($insert && (null === $result)) {
                G::msg('Nothing to save.  Try making a change this time.');
            } else {
                if (G::$M->errno == 1062) {
                    G::msg('Loginname already exists: '.$L->loginname, 'error');
                }
                G::msg('Login Add Failed', 'error');
            }
        } else {
            $L = new Login(true);
        }
        G::$V->L = $L;
        G::$V->letters = Login::initials();
    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_LoginEdit($argv) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.LoginEdit.php';
        G::$V->_title    = 'Edit Login';

        //If not passed a number, defer to search/list
        if (!isset($argv[1]) || !is_numeric($argv[1])) {
            return $this->do_Login($argv);
        }

        $L = new Login($argv[1]);
        $L->load();

        //handle changes to the Login
        if (isset($_POST['login_id']) && $_POST['login_id']==$L->login_id &&
            isset($_POST['loginname']) && isset($_POST['realname']) &&
            isset($_POST['pass1']) && isset($_POST['pass2']) &&
            isset($_POST['email1']) && isset($_POST['email2']) &&
            isset($_POST['sessionStrength']) && isset($_POST['flagChangePass']) &&
            isset($_POST['disabled'])
        ) {
            $update = true;
            $old_loginname = $L->loginname;
            $old_email = $L->email;
            $L->loginname = $_POST['loginname'];
            $L->realname = $_POST['realname'];
            $L->email = $_POST['email1'];
            $L->sessionStrength = $_POST['sessionStrength'];
            $L->flagChangePass = $_POST['flagChangePass'];
            $L->disabled = $_POST['disabled'];

            if ($old_loginname == $L->loginname && $old_loginname != $_POST['loginname']) {
                G::msg('While Editing: Invalid loginname supplied: '.htmlspecialchars($_POST['loginname']), 'error');
                $update = false;
            }
            if ($_POST['pass1'] != $_POST['pass2']) {
                G::msg('While Editing: The two password fields do not match, which one is correct?', 'error');
                $update = false;
            } elseif (isset(G::$G['SEC']['passwords']['enforce_in_admin'])
                && G::$G['SEC']['passwords']['enforce_in_admin']
                && true !== $error = Security::validate_password($_POST['pass1'])
            ) {
                G::msg($error, 'error');
                $update = false;
            } else {
                //blank means don't change password
                if ($_POST['pass1'] != '') {
                    $L->password = $_POST['pass1'];
                }
            }

            if ($_POST['email1'] != $_POST['email2']) {
                G::msg('While Editing: The two email address fields do not match, which one is correct?', 'error');
                $update = false;
            }
            if ($old_email == $L->email && $old_email != $_POST['email1']) {
                G::msg('While Editing: Invalid email supplied: '.htmlspecialchars($_POST['email1']), 'error');
                $update = false;
            }

            if ($update && $result = $L->update()) {
                G::msg('Login Edited');
            } elseif ($update && (null === $result)) {
                G::msg('No changes to Login detected.');
            } else {
                if (G::$M->errno == 1062) {
                    G::msg('Loginname already exists: '.$L->loginname, 'error');
                }
                G::msg('Login Edit Failed', 'error');
            }
        }

        //TODO: make a better way to do grants that doesn't involve loading the whole role list
        require_once SITE.CORE.'/models/Role.php';
        $R = new Role();
        $Roles = $R->search(1000, 0, 'label');
        //handle grant/revoke changes
        if (isset($_POST['grant']) && is_array($_POST['grant'])) {
            $i = 0;
            foreach ($_POST['grant'] as $k => $v) {
                if (1 == $v && !$L->roleTest($Roles[$k]->label)) {
                    $Roles[$k]->grant($L->login_id);
                    $i++;
                }
            }
            G::msg("Granted $i Roles to Login.");
            $i = 0;
            foreach ($Roles as $k => $v) {
                if ($L->roleTest($Roles[$k]->label) && !isset($_POST['grant'][$k])) {
                    $Roles[$k]->revoke($L->login_id);
                    $i++;
                }
            }
            G::msg("Revoked $i Roles from Login.");
            $L->load();
        }

        G::$V->L = $L;
        G::$V->Roles = $Roles;
        G::$V->letters = Login::initials();
        G::$V->referrer = $L->getReferrer();

        require_once SITE.CORE.'/models/LoginLog.php';
        $LL = new LoginLog(array('login_id' => $L->login_id));
        G::$V->log = $LL->search(100, 0, 'pkey', true);

    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_Role($argv) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.Role.php';
        G::$V->_title    = 'Select Role';

        require_once SITE.CORE.'/models/Role.php';
        $l = new Role();
        G::$V->list = $l->search(50, 0, 'label');
    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_RoleAdd($argv) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.RoleAdd.php';
        G::$V->_title    = 'Add Role';

        require_once SITE.CORE.'/models/Role.php';
        if (isset($_POST['label']) && isset($_POST['description']) &&
            isset($_POST['disabled'])
        ) {
            $R = new Role($_POST, true);

            if ($result=$R->insert()) {
                G::msg('Role Added');
                return $this->do_RoleEdit(array($R->role_id));
            } elseif (null === $result) {
                G::msg('Nothing to save.  Try making a change this time.');
            } else {
                if (G::$M->errno == 1062) {
                    G::msg('Role already exists: '.$R->label, 'error');
                }
                G::msg('Role Add Failed', 'error');
            }
        } else {
            $R = new Role(true);
        }
        G::$V->R = $R;
    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_RoleEdit($argv) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.RoleEdit.php';
        G::$V->_title    = 'Edit Role';

        //If not passed a number, defer to search/list
        if (!isset($argv[1]) || !is_numeric($argv[1])) {
            return $this->do_Role($argv);
        }

        require_once SITE.CORE.'/models/Role.php';
        $R = new Role($argv[1]);
        $R->load();

        // handle changes to the role
        if (isset($_POST['role_id']) && $_POST['role_id']==$R->role_id &&
            isset($_POST['label']) && isset($_POST['description']) &&
            isset($_POST['disabled'])
        ) {
            $R->label = $_POST['label'];
            $R->description = $_POST['description'];
            $R->disabled = $_POST['disabled'];

            if ($result = $R->update()) {
                G::msg('Role Edited');
            } elseif (null === $result) {
                G::msg('No modifications to Role detected.');
            } else {
                if (G::$M->errno == 1062) {
                    G::msg('Role already exists: '.$R->label, 'error');
                }
                G::msg('Role Edit Failed', 'error');
            }
        }

        G::$V->R = $R;
        $members = $R->getMembers();

        //handle grant/revoke changes
        if (isset($_POST['grant']) && is_array($_POST['grant'])) {
            $i = 0;
            foreach ($_POST['grant'] as $k => $v) {
                if (1 == $v && !isset($members[$k])) {
                    $R->grant($k);
                    $members[$k] = G::$S->Login->login_id;
                    $i++;
                }
            }
            G::msg("Granted Role to $i Logins.");
            $i = 0;
            if (is_array($members)) {
                foreach ($members as $k => $v) {
                    if (!isset($_POST['grant'][$k])) {
                        $R->revoke($k);
                        unset($members[$k]);
                        $i++;
                    }
                }
            }
            G::msg("Revoked Role from $i Logins.");
        }

        //TODO: make a better way to do grants that doesn't involve loading the whole loginlist
        $L = new Login();
        G::$V->Logins = $L->search(1000, 0, 'loginname');
        G::$V->members = $members;

        G::$V->creator = $R->getCreator();
    }

    /**
     * action
     *
     * @param array $argv web request parameters
     *
     * @return mixed
     */
    public function do_loginLog($argv) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.LoginLog.php';
        G::$V->_title    = G::$V->_siteName.': Login Log';

        require_once SITE.CORE.'/models/LoginLog.php';
        $LL = new LoginLog();
        G::$V->log = $LL->search(100, 0, 'pkey', true);
    }
}

