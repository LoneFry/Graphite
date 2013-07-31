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
    protected $action = 'list';

    /**
     * action
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_list($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.list.php';
        G::$V->_title    = 'Administrative Options';
    }

    /**
     * action
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_Login($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.Login.php';
        G::$V->_title    = 'Select Login';

        if (isset($argv[1])) {
            $l = Login::forInitial($argv[1]);
            if ($l && 1 < count($l)) {
                G::$V->list = $l;
            } elseif ($l && 1 == count($l)) {
                $L = array_shift($l);
                return $this->do_LoginEdit(array($L->login_id), array());
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
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_LoginAdd($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.LoginAdd.php';
        G::$V->_title    = 'Add Login';

        if (isset($request['loginname']) && isset($request['realname'])
            && isset($request['pass1']) && isset($request['pass2'])
            && isset($request['email1']) && isset($request['email2'])
            && isset($request['sessionStrength']) && isset($request['flagChangePass'])
            && isset($request['disabled'])
        ) {
            $insert = true;
            if ($request['email1'] != $request['email2']) {
                G::msg(
                    Localizer::translate('admin.loginadd.msg.emailmismatch'),
                    'error'
                );
                $insert = false;
            }
            $request['email'] = $request['email1'];

            if ('' == $request['pass1']) {
                G::msg(
                    Localizer::translate('admin.loginadd.msg.passwordempty'),
                    'error'
                );
                $insert = false;
            } elseif ($request['pass1'] != $request['pass2']) {
                G::msg(
                    Localizer::translate('admin.loginadd.msg.passwordmismatch'),
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
                    Localizer::translate(
                        'admin.loginadd.msg.loginnameinvalid',
                        htmlspecialchars($request['loginname'])
                    ),
                    'error'
                );
                $insert = false;
            }
            if (!$L->email) {
                G::msg(
                    Localizer::translate(
                        'admin.loginadd.msg.emailinvalid',
                        htmlspecialchars($request['email'])
                    ),
                    'error'
                );
                $insert = false;
            }

            if ($insert && $result = $L->insert()) {
                G::msg(Localizer::translate('admin.loginadd.msg.success'));
                return $this->do_LoginEdit(array($L->login_id), array());
            } elseif ($insert && (null === $result)) {
                G::msg(
                    Localizer::translate('admin.loginadd.msg.nochange')
                );
            } else {
                if (G::$M->errno == 1062) {
                    G::msg(
                        Localizer::translate(
                            'admin.loginadd.msg.loginnameexists',
                            $L->loginname
                        ),
                        'error'
                    );
                }
                G::msg(
                    Localizer::translate('admin.loginadd.msg.fail'),
                    'error'
                );
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
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_LoginEdit($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.LoginEdit.php';
        G::$V->_title    = 'Edit Login';

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
                    Localizer::translate(
                        'admin.loginedit.msg.logininvalid',
                        htmlspecialchars($request['loginname'])
                    ),
                    'error'
                );
                $update = false;
            }
            if ($request['pass1'] != $request['pass2']) {
                G::msg(
                    Localizer::translate('admin.loginedit.msg.passwordmismatch'),
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
                    Localizer::translate('admin.loginedit.msg.emailmismatch'),
                    'error'
                );
                $update = false;
            }
            if ($old_email == $L->email && $old_email != $request['email1']) {
                G::msg(
                    Localizer::translate(
                        'admin.loginedit.msg.emailinvalid',
                        htmlspecialchars($request['email1'])
                    ),
                    'error'
                );
                $update = false;
            }

            if ($update && $result = $L->update()) {
                G::msg(Localizer::translate('admin.loginedit.msg.success'));
            } elseif ($update && (null === $result)) {
                G::msg(Localizer::translate('admin.loginedit.msg.nochange'));
            } else {
                if (G::$M->errno == 1062) {
                    G::msg(
                        Localizer::translate(
                            'admin.loginedit.msg.loginnameexists',
                            htmlspecialchars($request['email1'])
                        ),
                        'error'
                    );
                }
                G::msg(
                    Localizer::translate('admin.loginedit.msg.fail'),
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
            G::msg(Localizer::translate('admin.loginedit.msg.grantroles', $i));
            $i = 0;
            foreach ($Roles as $k => $v) {
                if ($L->roleTest($Roles[$k]->label) && !isset($request['grant'][$k])) {
                    $Roles[$k]->revoke($L->login_id);
                    $i++;
                }
            }
            G::msg(Localizer::translate('admin.loginedit.msg.revokeroles', $i));
            $L->load();
        }

        G::$V->L = $L;
        G::$V->Roles = $Roles;
        G::$V->letters = Login::initials();
        G::$V->referrer = $L->getReferrer();

        $LL = new LoginLog(array('login_id' => $L->login_id));
        G::$V->log = $LL->search(100, 0, 'pkey', true);

    }

    /**
     * action
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_Role($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.Role.php';
        G::$V->_title    = 'Select Role';

        $l = new Role();
        G::$V->list = $l->search(50, 0, 'label');
    }

    /**
     * action
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_RoleAdd($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.RoleAdd.php';
        G::$V->_title    = 'Add Role';

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
        G::$V->R = $R;
    }

    /**
     * action
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_RoleEdit($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Role')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.RoleEdit.php';
        G::$V->_title    = 'Edit Role';

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

        G::$V->R = $R;
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
        G::$V->Logins = $L->search(1000, 0, 'loginname');
        G::$V->members = $members;

        G::$V->creator = $R->getCreator();
    }

    /**
     * action
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return mixed
     */
    public function do_loginLog($argv = array(), $request = array()) {
        if (!G::$S->roleTest('Admin/Login')) {
            return parent::do_403($argv);
        }

        G::$V->_template = 'Admin.LoginLog.php';
        G::$V->_title    = G::$V->_siteName.': Login Log';

        require_once SITE.'/^/models/LoginLog.php';
        $LL = new LoginLog();
        G::$V->log = $LL->search(100, 0, 'pkey', true);
    }
}
