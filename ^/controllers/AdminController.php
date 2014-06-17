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
    /** @var string Default action */
    protected $action = 'list';

    /**
     * Display list of available Admin actions
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_list(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.list.php';
        $this->View->_title    = 'Administrative Options';

        return $this->View;
    }

    /**
     * Choose a Login to administrate
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_Login(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.Login.php';
        $this->View->_title    = 'Select Login';

        if (isset($argv[1])) {
            $l = Login::forInitial($argv[1]);
            if ($l && 1 < count($l)) {
                $this->View->list = $l;
            } elseif ($l && 1 == count($l)) {
                $L = array_shift($l);
                return $this->do_LoginEdit(array('login', $L->login_id), array());
            }
        } else {
            $l = new Login();
            $this->View->list = $l->search(50, 0, 'loginname');
        }
        $this->View->letters = Login::initials();

        return $this->View;
    }

    /**
     * Add a Login
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_LoginAdd(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.LoginAdd.php';
        $this->View->_title    = 'Add Login';
        $this->View->_script('/^/js/validate-email.min.js');

        if (isset($request['loginname']) && isset($request['realname'])
            && isset($request['pass1']) && isset($request['pass2'])
            && isset($request['email1']) && isset($request['email2'])
            && isset($request['sessionStrength']) && isset($request['flagChangePass'])
            && isset($request['disabled'])
        ) {
            $insert = true;
            if ($request['email1'] != $request['email2']) {
                G::msg(
                    G::_('admin.loginadd.msg.emailmismatch'),
                    'error'
                );
                $insert = false;
            }
            $request['email'] = $request['email1'];

            if ('' == $request['pass1']) {
                G::msg(
                    G::_('admin.loginadd.msg.passwordempty'),
                    'error'
                );
                $insert = false;
            } elseif ($request['pass1'] != $request['pass2']) {
                G::msg(
                    G::_('admin.loginadd.msg.passwordmismatch'),
                    'error'
                );
                $insert = false;
            } elseif (isset(G::$G['SEC']['passwords']['enforce_in_admin'])
                && G::$G['SEC']['passwords']['enforce_in_admin']
                && true !== $error = Security::validate_password($request['pass1'])
            ) {
                G::msg($error, 'error');
                $insert = false;
            } else {
                $request['password'] = $request['pass1'];
            }

            $L = new Login($request, true);
            if (!$L->loginname) {
                G::msg(
                    G::_(
                        'admin.loginadd.msg.loginnameinvalid',
                        htmlspecialchars($request['loginname'])
                    ),
                    'error'
                );
                $insert = false;
            }
            if (!$L->email) {
                G::msg(
                    G::_(
                        'admin.loginadd.msg.emailinvalid',
                        htmlspecialchars($request['email'])
                    ),
                    'error'
                );
                $insert = false;
            }

            if ($insert && $result = $L->insert()) {
                G::msg(G::_('admin.loginadd.msg.success'));
                return $this->do_LoginEdit(array($L->login_id), array());
            } elseif ($insert && (null === $result)) {
                G::msg(
                    G::_('admin.loginadd.msg.nochange')
                );
            } else {
                if (G::$M->errno == 1062) {
                    G::msg(
                        G::_(
                            'admin.loginadd.msg.loginnameexists',
                            $L->loginname
                        ),
                        'error'
                    );
                }
                G::msg(
                    G::_('admin.loginadd.msg.fail'),
                    'error'
                );
            }
        } else {
            $L = new Login(true);
        }
        $this->View->L = $L;
        $this->View->letters = Login::initials();

        return $this->View;
    }

    /**
     * Edit a Login
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_LoginEdit(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.LoginEdit.php';
        $this->View->_title    = 'Edit Login';
        $this->View->_script('/^/js/validate-email.min.js');

        // If not passed a number, defer to search/list
        if (!isset($argv[1]) || !is_numeric($argv[1])) {
            return $this->do_Login($argv);
        }

        $L = new Login($argv[1]);
        $L->load();

        // handle changes to the Login
        if (isset($request['login_id']) && $request['login_id'] == $L->login_id
            && isset($request['loginname']) && isset($request['realname'])
            && isset($request['pass1']) && isset($request['pass2'])
            && isset($request['email1']) && isset($request['email2'])
            && isset($request['sessionStrength']) && isset($request['flagChangePass'])
            && isset($request['disabled'])
        ) {
            $update = true;
            $old_loginname = $L->loginname;
            $old_email = $L->email;
            $L->loginname = $request['loginname'];
            $L->realname = $request['realname'];
            $L->email = $request['email1'];
            $L->sessionStrength = $request['sessionStrength'];
            $L->flagChangePass = $request['flagChangePass'];
            $L->disabled = $request['disabled'];

            if ($old_loginname == $L->loginname && $old_loginname != $request['loginname']) {
                G::msg(
                    G::_(
                        'admin.loginedit.msg.logininvalid',
                        htmlspecialchars($request['loginname'])
                    ),
                    'error'
                );
                $update = false;
            }
            if ($request['pass1'] != $request['pass2']) {
                G::msg(
                    G::_('admin.loginedit.msg.passwordmismatch'),
                    'error'
                );
                $update = false;
            } elseif (isset(G::$G['SEC']['passwords']['enforce_in_admin'])
                && G::$G['SEC']['passwords']['enforce_in_admin']
                && true !== $error = Security::validate_password($request['pass1'])
            ) {
                G::msg($error, 'error');
                $update = false;
            } else {
                // blank means don't change password
                if ($request['pass1'] != '') {
                    $L->password = $request['pass1'];
                }
            }

            if ($request['email1'] != $request['email2']) {
                G::msg(
                    G::_('admin.loginedit.msg.emailmismatch'),
                    'error'
                );
                $update = false;
            }
            if ($old_email == $L->email && $old_email != $request['email1']) {
                G::msg(
                    G::_(
                        'admin.loginedit.msg.emailinvalid',
                        htmlspecialchars($request['email1'])
                    ),
                    'error'
                );
                $update = false;
            }

            if ($update && $result = $L->update()) {
                G::msg(G::_('admin.loginedit.msg.success'));
            } elseif ($update && (null === $result)) {
                G::msg(G::_('admin.loginedit.msg.nochange'));
            } else {
                if (G::$M->errno == 1062) {
                    G::msg(
                        G::_(
                            'admin.loginedit.msg.loginnameexists',
                            htmlspecialchars($request['email1'])
                        ),
                        'error'
                    );
                }
                G::msg(
                    G::_('admin.loginedit.msg.fail'),
                    'error'
                );
            }
        }

        // TODO: make a better way to do grants that doesn't involve loading the whole role list
        $R = new Role();
        $Roles = $R->search(1000, 0, 'label');
        // handle grant/revoke changes
        if (isset($request['grant']) && is_array($request['grant'])) {
            $i = 0;
            foreach ($request['grant'] as $k => $v) {
                if (1 == $v && !$L->roleTest($Roles[$k]->label)) {
                    $Roles[$k]->grant($L->login_id);
                    $i++;
                }
            }
            G::msg(G::_('admin.loginedit.msg.grantroles', $i));
            $i = 0;
            foreach ($Roles as $k => $v) {
                if ($L->roleTest($Roles[$k]->label) && !isset($request['grant'][$k])) {
                    $Roles[$k]->revoke($L->login_id);
                    $i++;
                }
            }
            G::msg(G::_('admin.loginedit.msg.revokeroles', $i));
            $L->load();
        }

        $this->View->L = $L;
        $this->View->Roles = $Roles;
        $this->View->letters = Login::initials();
        $this->View->referrer = $L->getReferrer();

        $LL = new LoginLog(array('login_id' => $L->login_id));
        $this->View->log = $LL->search(100, 0, 'pkey', true);

        return $this->View;
    }

    /**
     * Choose a Role to administrate
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_Role(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.Role.php';
        $this->View->_title    = 'Select Role';

        $l = new Role();
        $this->View->list = $l->search(50, 0, 'label');

        return $this->View;
    }

    /**
     * Add a Role
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_RoleAdd(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.RoleAdd.php';
        $this->View->_title    = 'Add Role';

        if (isset($request['label']) && isset($request['description'])
            && isset($request['disabled'])
        ) {
            $R = new Role($request, true);

            if ($result = $R->insert()) {
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
        $this->View->R = $R;

        return $this->View;
    }

    /**
     * Edit a Role
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_RoleEdit(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.RoleEdit.php';
        $this->View->_title    = 'Edit Role';

        // If not passed a number, defer to search/list
        if (!isset($argv[1]) || !is_numeric($argv[1])) {
            return $this->do_Role($argv);
        }

        $R = new Role($argv[1]);
        $R->load();

        // handle changes to the role
        if (isset($request['role_id']) && $request['role_id'] == $R->role_id
            && isset($request['label']) && isset($request['description'])
            && isset($request['disabled'])
        ) {
            $R->label = $request['label'];
            $R->description = $request['description'];
            $R->disabled = $request['disabled'];

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

        $this->View->R = $R;
        $members = $R->getMembers();

        // handle grant/revoke changes
        if (isset($request['grant']) && is_array($request['grant'])) {
            $i = 0;
            foreach ($request['grant'] as $k => $v) {
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
                    if (!isset($request['grant'][$k])) {
                        $R->revoke($k);
                        unset($members[$k]);
                        $i++;
                    }
                }
            }
            G::msg("Revoked Role from $i Logins.");
        }

        // TODO: make a better way to do grants that doesn't involve loading the whole loginlist
        $L = new Login();
        $this->View->Logins = $L->search(1000, 0, 'loginname');
        $this->View->members = $members;

        $this->View->creator = $R->getCreator();

        return $this->View;
    }

    /**
     * View Login Log
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_loginLog(array $argv = array(), array $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        $this->View->_template = 'Admin.LoginLog.php';
        $this->View->_title    = $this->View->_siteName.': Login Log';

        require_once SITE.'/^/models/LoginLog.php';
        $LL = new LoginLog();
        $this->View->log = $LL->search(100, 0, 'pkey', true);

        return $this->View;
    }
}
