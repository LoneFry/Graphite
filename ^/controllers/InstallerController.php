<?php
/**
 * Installer Controller - Aids Graphite setup by initializing DB and config
 * File : /^/controllers/InstallerController.php
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
 * InstallerController class - Aids Graphite setup by initializing DB and config
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 * @see      /^/lib/Controller.php
 */
class InstallerController extends Controller {
    /** @var string Default action */
    protected $action = 'install';

    /**
     * install action - receives configuration details from user and writes
     * out configuration file
     *
     * @param array $argv    Argument list passed from Dispatcher
     * @param array $request Request_method-specific parameters
     *
     * @return View
     */
    public function do_install(array $argv = array(), array $request = array()) {
        if (true !== G::$G['installer']) {
            G::msg('Installer Disabled', 'error');
            return parent::do_403($argv);
        }

        $this->View->_template = 'Installer.php';
        $this->View->_title    = $this->View->_siteName.' : Install';
        $this->View->_head    .= '
<style type="text/css">
    form#installer {background-color:#e2e2e2;padding:50px;}
    form#installer div{margin:auto;width:500px;}
    form#installer h3{margin:0 -10px 20px -10px;border-bottom:3px solid #2e2e2e;}
    form#installer label{display:block;font:bold 10pt Georgia}
    form#installer input[type=text],
    form#installer input[type=email],
    form#installer input[type=password]{margin-bottom:20px;width:400px;font:bold 16pt Tahoma;}
</style>
';

        if (isset($request['siteName']) && isset($request['loginname'])
            && isset($request['password1']) && isset($request['password2'])
            && isset($request['siteEmail'])
            && isset($request['Host']) && isset($request['User'])
            && isset($request['Pass']) && isset($request['Passb'])
            && isset($request['Tabl'])
            && isset($request['User2'])
            && isset($request['Pass2']) && isset($request['Pass2b'])
        ) {
            $this->View->siteName = $request['siteName'];
            $this->View->loginname = $request['loginname'];
            $this->View->siteEmail = $request['siteEmail'];
            $this->View->Host = $request['Host'];
            $this->View->User = $request['User'];
            $this->View->Name = $request['Name'];
            $this->View->Tabl = $request['Tabl'];
            $this->View->User2 = $request['User2'];
            $this->View->HTML5 = isset($request['HTML5']);
            $this->View->HTML4 = isset($request['HTML4']);
            $this->View->CLI = isset($request['CLI']);

            $install = true;
            if ($request['password1'] != $request['password2']) {
                G::msg('Root Login Passwords Don\'t Match, try again.', 'error');
                $install = false;
            }
            if ($request['Pass'] != $request['Passb']) {
                G::msg('Database Read/Write Passwords Don\'t Match, try again.', 'error');
                $install = false;
            }
            if ($request['Pass2'] != $request['Pass2b']) {
                G::msg('Database Read-Only Passwords Don\'t Match, try again.', 'error');
                $install = false;
            }
            if (false === filter_var($request['siteEmail'], FILTER_VALIDATE_EMAIL)) {
                G::msg('Invalid Site Email provided, try again.', 'error');
                $install = false;
            }
            ob_start();
            G::$M = new mysqli_($request['Host'],
                                $request['User'],
                                $request['Pass'],
                                $request['Name'],
                                null,
                                null,
                                $request['Tabl'],
                                true);
            if (mysqli_connect_error()) {
                G::msg('Unable to connect to Database with Read/Write details, try again.', 'error');
                $install = false;
            }
            if ('' != $request['User2'] && '' != $request['Pass2']) {
                G::$m = new mysqli_($request['Host'],
                                    $request['User2'],
                                    $request['Pass2'],
                                    $request['Name'],
                                    null,
                                    null,
                                    $request['Tabl'],
                                    true);
                if (mysqli_connect_error()) {
                    G::msg('Unable to connect to Database with Read-Only details, try again.', 'error');
                    $install = false;
                }
            } else {
                G::$m = G::$M;
            }
            G::msg(ob_get_clean());
            if ($install) {
                G::$G['db']['tabl'] = G::$M->escape_string($request['Tabl']);
                foreach (array('Login', 'Role', 'LoginLog', 'ContactLog') as $class) {
                    if (false === $class::create()) {
                        G::msg('Failed to create table '.$class::getTable(), 'error');
                        $install = false;
                    } else {
                        G::msg('Created table '.$class::getTable());
                    }
                }
                if (false === G::$M->query("CREATE TABLE IF NOT EXISTS `".Role::getTable('Logins')."` ("
                    ."`role_id` int(10) UNSIGNED NOT NULL DEFAULT 0,"
                    ."`login_id` int(10) UNSIGNED NOT NULL DEFAULT 0,"
                    ."`grantor_id` int(10) UNSIGNED NOT NULL DEFAULT 0,"
                    ."`dateCreated` int(10) UNSIGNED NOT NULL DEFAULT 0,"
                    ."PRIMARY KEY (`role_id`,`login_id`))")
                ) {
                    G::msg('Failed to create table '.Role::getTable('Logins'), 'error');
                    $install = false;
                } else {
                    G::msg('Created table '.Role::getTable('Logins'));
                }

                if (!$install) {
                    G::msg('Not all tables could be created, install ended prematurely.', 'error');
                } else {
                    $L = new Login(array('loginname'   => $request['loginname'],
                                         'password'    => $request['password1'],
                                         'email'       => $request['siteEmail'],
                                         'referrer_id' => 1));
                    if ($login_id = $L->insert()) {
                        G::msg('Created root user: '.$L->loginname);

                        // clear any open session
                        session_start();
                        $_SESSION = array();
                        session_destroy();

                        G::$S = new Security();
                        G::$S->authenticate($L->loginname, $L->password);
                    } else {
                        G::msg('Failed to create root user: '.$L->loginname, 'error');
                    }

                    $roles = array(
                        array('label' => 'Admin', 'description' => 'General use admin Role', 'creator_id' => 1),
                        array('label' => 'Admin/Login', 'description' => 'Can Add/Edit Logins', 'creator_id' => 1),
                        array('label' => 'Admin/Role', 'description' => 'Can Add/Edit Roles', 'creator_id' => 1),
                        array('label' => 'Home/ContactLog', 'description' => 'Can view Contact Log', 'creator_id' => 1),
                        );
                    if (isset($request['CLI'])) {
                        $roles[] = array('label' => 'Gsh', 'description' => 'Access Graphite Shell', 'creator_id' => 1);
                    }
                    foreach ($roles as $v) {
                        $R = new Role($v);
                        if ($R->insert()) {
                            G::msg('Created Role: '.$R->label);
                            $R->grant($login_id);
                        } else {
                            G::msg('Failed to create Role: '.$R->label, 'error');
                        }
                    }

                    $includePath = "'"
                        .(isset($request['HTML5']) ? "/^HTML5;" : '')
                        .(isset($request['HTML4']) ? "/^HTML4;" : '')
                        .(isset($request['CLI']) ? "/^CLI;" : '')
                        ."/^'"
                        ;

                    $config = sprintf($this->config,
                            $_SERVER['SERVER_NAME'],
                            $request['siteEmail'],
                            $request['Host'],
                            $request['User'],
                            $request['Pass'],
                            $request['Name'],
                            $request['Tabl'],
                            $request['User2'],
                            $request['Pass2'],
                            $request['siteName'],
                            $includePath
                            );

                    $filename = 'config.'.$_SERVER['SERVER_NAME'].'.php';
                    if (file_exists(dirname(SITE).'/siteConfigs')) {
                        $path = dirname(SITE).'/siteConfigs';
                    } else {
                        $path = SITE;
                    }
                    if (file_exists($path.'/'.$filename)) {
                        if (!rename($path.'/'.$filename, $path.'/'.date("YmdHis").$filename)) {
                            G::msg('An existing config file could not be moved. Please copy the below config into '
                                .$path.'/'.$filename, 'error');
                            $this->View->config = $config;
                            $install = false;
                        }
                    }
                }

                if ($install) {
                    $f = fopen($path.'/'.$filename, "w");
                    if (!fwrite($f, $config)) {
                        G::msg('The config file could not be written. Please copy the below config into '
                            .$path.'/'.$filename, 'error');
                        $this->View->config = $config;
                        $install = false;
                    } else {
                        G::msg('The config file was written to '.$path.'/'.$filename);
                        G::msg('Install complete <a href="/">Go Home</a>');
                    }
                }
            }
        } else {
            $this->View->siteName = 'Graphite Site';
            $this->View->loginname = 'root';
            $this->View->siteEmail = '';

            $this->View->Host = 'localhost';
            $this->View->User = '';
            $this->View->Pass = '';
            $this->View->Name = '';
            $this->View->Tabl = 'g_'.substr(base_convert(uniqid(), 16, 36), -5).'_';

            $this->View->User2 = '';
            $this->View->Pass2 = '';
        }

        return $this->View;
    }

    /** @var string Initial config template */
    protected $config = <<<'ENDOFCONFIG'
<?php
/** **************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /config.%1$s.php
 *                domain-specific configuration file
 ****************************************************************************/

/** **************************************************************************
 * General settings
 ****************************************************************************/
G::$G['MODE'] = 'prd'; // prd,tst,dev... used to flag debug behaviors
G::$G['siteEmail'] = '%2$s';

// Include Path: a list of paths under the webroot to check for included
// controllers, models, templates
// list in priority order, first found is used
// for example: G::$G['includePath'] = '/^MyApp;/^';
G::$G['includePath'] = %11$s;

// disable the installer
G::$G['installer'] = false;
/** **************************************************************************
 * /General settings
 ****************************************************************************/


/** **************************************************************************
 * Database settings
 ****************************************************************************/
G::$G['db'] = array(
    'host' => '%3$s',
    'user' => '%4$s',
    'pass' => '%5$s',
    'name' => '%6$s',
    'tabl' => '%7$s',
    'log'  => false
);
// leave ['ro']['user'] blank to indicate only RW credentials used
G::$G['db']['ro'] = array(
    'host' => G::$G['db']['host'],
    'user' => '%8$s',
    'pass' => '%9$s',
    'name' => G::$G['db']['name']
);
/** **************************************************************************
 * /Database settings
 ****************************************************************************/


/** **************************************************************************
 * Settings for the Dispatcher
 ****************************************************************************/
G::$G['CON']['controller'] = 'Home';
/** **************************************************************************
 * /Settings for the Dispatcher
 ****************************************************************************/


/** **************************************************************************
 * Settings for the View
 ****************************************************************************/
// display vars
G::$G['VIEW']['_siteName'] = '%10$s';
/** **************************************************************************
 * /Settings for the View
 ****************************************************************************/

ENDOFCONFIG;

}
