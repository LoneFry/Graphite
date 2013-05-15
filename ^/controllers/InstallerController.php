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
 * File        : /^/controllers/InstallerController.php
 *                Account Controller class - performs user account related actions
 ****************************************************************************/
require_once LIB.'/mysqli_.php';
require_once LIB.'/Security.php';

class InstallerController extends Controller {
	protected $action = 'install';

	/**
	 * install action - receives configuration details from user and writes
	 * out configuration file
	 *
	 * @param array $argv web request parameters
	 *
	 * @return mixed
	 */
	public function do_install($argv) {
		if (true !== G::$G['installer']) {
			G::msg('Installer Disabled', 'error');
			return parent::do_403($argv);
		}

		G::$V->_template = 'Installer.php';
		G::$V->_title    = G::$V->_siteName.' : Install';
		G::$V->_head    .= '
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

		if (isset($_POST['siteName']) && isset($_POST['loginname']) &&
		   isset($_POST['password1']) && isset($_POST['password2']) &&
		   isset($_POST['siteEmail']) &&
		   isset($_POST['Host']) && isset($_POST['User']) &&
		   isset($_POST['Pass']) && isset($_POST['Passb']) &&
		   isset($_POST['Tabl']) &&
		   isset($_POST['User2']) &&
		   isset($_POST['Pass2']) && isset($_POST['Pass2b'])
		) {
			G::$V->siteName = $_POST['siteName'];
			G::$V->loginname = $_POST['loginname'];
			G::$V->siteEmail = $_POST['siteEmail'];
			G::$V->Host = $_POST['Host'];
			G::$V->User = $_POST['User'];
			G::$V->Name = $_POST['Name'];
			G::$V->Tabl = $_POST['Tabl'];
			G::$V->User2 = $_POST['User2'];
			G::$V->HTML5 = isset($_POST['HTML5']);
			G::$V->HTML4 = isset($_POST['HTML4']);
			G::$V->CLI = isset($_POST['CLI']);

			$install = true;
			if ($_POST['password1'] != $_POST['password2']) {
				G::msg('Root Login Passwords Don\'t Match, try again.', 'error');
				$install = false;
			}
			if ($_POST['Pass'] != $_POST['Passb']) {
				G::msg('Database Read/Write Passwords Don\'t Match, try again.', 'error');
				$install = false;
			}
			if ($_POST['Pass2'] != $_POST['Pass2b']) {
				G::msg('Database Read-Only Passwords Don\'t Match, try again.', 'error');
				$install = false;
			}
			if (false === filter_var($_POST['siteEmail'], FILTER_VALIDATE_EMAIL)) {
				G::msg('Invalid Site Email provided, try again.', 'error');
				$install = false;
			}
			ob_start();
			G::$M = new mysqli_($_POST['Host'],
								$_POST['User'],
								$_POST['Pass'],
								$_POST['Name'],
								null,
								null,
								$_POST['Tabl'],
								true);
			if (mysqli_connect_error()) {
				G::msg('Unable to connect to Database with Read/Write details, try again.', 'error');
				$install = false;
			}
			if ('' != $_POST['User2'] && '' != $_POST['Pass2']) {
				G::$m = new mysqli_($_POST['Host'],
									$_POST['User2'],
									$_POST['Pass2'],
									$_POST['Name'],
									null,
									null,
									$_POST['Tabl'],
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
				G::$G['db']['tabl'] = G::$M->escape_string($_POST['Tabl']);
				if (false === G::$M->query("CREATE TABLE IF NOT EXISTS `".G::$G['db']['tabl']."Logins` ("
										   ."`login_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,"
										   ."`loginname` varchar(255) NOT NULL,"
										   ."`password` varchar(255) NOT NULL,"
										   ."`realname` varchar(255) NOT NULL DEFAULT '',"
										   ."`email` varchar(255) NOT NULL DEFAULT '',"
										   ."`comment` varchar(255) NOT NULL DEFAULT '',"
										   ."`sessionStrength` tinyint(1) UNSIGNED NOT NULL DEFAULT '2',"
										   ."`lastIP` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`UA` varchar(40) NOT NULL DEFAULT '',"
										   ."`dateModified` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`dateActive` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`dateLogin` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`dateLogout` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`dateCreated` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`referrer_id` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`disabled` bit NOT NULL DEFAULT 0,"
										   ."`flagChangePass` bit NOT NULL DEFAULT 0,"
										   ."PRIMARY KEY (`login_id`),"
										   ."UNIQUE KEY `loginname` (`loginname`))")
				) {
					G::msg('Failed to create table '.G::$G['db']['tabl'].'Logins', 'error');
					$install = false;
				} else {
					G::msg('Created table '.G::$G['db']['tabl'].'Logins');
				}
				if (false === G::$M->query("CREATE TABLE IF NOT EXISTS `".G::$G['db']['tabl']."Roles_Logins` ("
										   ."`role_id` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`login_id` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`grantor_id` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`dateCreated` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."PRIMARY KEY (`role_id`,`login_id`))")
				) {
					G::msg('Failed to create table '.G::$G['db']['tabl'].'Roles_Logins', 'error');
					$install = false;
				} else {
					G::msg('Created table '.G::$G['db']['tabl'].'Roles_Logins');
				}
				if (false === G::$M->query("CREATE TABLE IF NOT EXISTS `".G::$G['db']['tabl']."Roles` ("
										   ."`role_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,"
										   ."`label` varchar(255) NOT NULL,"
										   ."`description` varchar(255) NOT NULL,"
										   ."`creator_id` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`disabled` bit NOT NULL DEFAULT 0,"
										   ."`dateModified` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."`dateCreated` int(11) UNSIGNED NOT NULL DEFAULT '0',"
										   ."PRIMARY KEY (`role_id`),"
										   ."UNIQUE KEY `label` (`label`))")
				) {
					G::msg('Failed to create table '.G::$G['db']['tabl'].'Roles', 'error');
					$install = false;
				} else {
					G::msg('Created table '.G::$G['db']['tabl'].'Roles');
				}
				if (false === G::$M->query("CREATE TABLE IF NOT EXISTS `".G::$G['db']['tabl']."LoginLog` ("
										   ."`pkey` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,"
										   ."`login_id` int(11) UNSIGNED NOT NULL,"
										   ."`ip` int(11) UNSIGNED NOT NULL,"
										   ."`ua` varchar(255) NOT NULL,"
										   ."`iDate` int(11) UNSIGNED NOT NULL,"
										   ."PRIMARY KEY (`pkey`))")
				) {
					G::msg('Failed to create table '.G::$G['db']['tabl'].'LoginLog', 'error');
					$install = false;
				} else {
					G::msg('Created table '.G::$G['db']['tabl'].'LoginLog');
				}
				if (false === G::$M->query("CREATE TABLE IF NOT EXISTS `".G::$G['db']['tabl']."ContactLog` ("
										   ."`id` int UNSIGNED NOT NULL AUTO_INCREMENT,"
										   ."`from` varchar(255) NOT NULL,"
										   ."`date` int UNSIGNED NOT NULL DEFAULT '0',"
										   ."`subject` varchar(255) NOT NULL,"
										   ."`to` varchar(255) NOT NULL,"
										   ."`body` text NOT NULL,"
										   ."`IP` int UNSIGNED NOT NULL DEFAULT '0',"
										   ."`login_id` int UNSIGNED NOT NULL DEFAULT '0',"
										   ."`flagDismiss` bit NOT NULL DEFAULT 0,"
										   ."PRIMARY KEY (`id`),"
										   ."KEY `flagDismiss` (`flagDismiss`))")
				) {
					G::msg('Failed to create table '.G::$G['db']['tabl'].'ContactLog', 'error');
					$install = false;
				} else {
					G::msg('Created table '.G::$G['db']['tabl'].'ContactLog');
				}

				if (!$install) {
					G::msg('Not all tables could be created, install ended prematurely.', 'error');
				} else {
					include_once SITE.CORE.'/models/Login.php';
					Login::prime();//just in case Login was primed earlier
					$L = new Login(array('loginname'   => $_POST['loginname'],
										 'password'    => $_POST['password1'],
										 'email'       => $_POST['siteEmail'],
										 'referrer_id' => 1));
					if ($login_id=$L->insert()) {
						G::msg('Created root user: '.$L->loginname);

						//clear any open session
						session_start();
						$_SESSION = array();
						session_destroy();

						G::$S = new Security();
						G::$S->authenticate($L->loginname, $L->password);
					} else {
						G::msg('Failed to create root user: '.$L->loginname, 'error');
					}

					include_once SITE.CORE.'/models/Role.php';
					Role::prime();

					$roles = array(
						array('label' => 'Admin', 'description' => 'General use admin Role', 'creator_id' => 1),
						array('label' => 'Admin/Login', 'description' => 'Can Add/Edit Logins', 'creator_id' => 1),
						array('label' => 'Admin/Role', 'description' => 'Can Add/Edit Roles', 'creator_id' => 1),
						array('label' => 'Home/ContactLog', 'description' => 'Can view Contact Log', 'creator_id' => 1),
						);
					if (isset($_POST['CLI'])) {
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
						.(isset($_POST['HTML5']) ? "/^HTML5;" : '')
						.(isset($_POST['HTML4']) ? "/^HTML4;" : '')
						.(isset($_POST['CLI']) ? "/^CLI;" : '')
						."'.CORE"
						;

					$config = sprintf($this->config,
							$_SERVER['SERVER_NAME'],
							$_POST['siteEmail'],
							$_POST['Host'],
							$_POST['User'],
							$_POST['Pass'],
							$_POST['Name'],
							$_POST['Tabl'],
							$_POST['User2'],
							$_POST['Pass2'],
							$_POST['siteName'],
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
							G::$V->config = $config;
							$install = false;
						}
					}
				}

				if ($install) {
					$f = fopen($path.'/'.$filename, "w");
					if (!fwrite($f, $config)) {
						G::msg('The config file could not be written. Please copy the below config into '
							.$path.'/'.$filename, 'error');
						G::$V->config = $config;
						$install = false;
					} else {
						G::msg('The config file was written to '.$path.'/'.$filename);
						G::msg('Install complete <a href="/">Go Home</a>');
					}
				}
			}
		} else {
			G::$V->siteName = 'Graphite Site';
			G::$V->loginname = 'root';
			G::$V->siteEmail = '';

			G::$V->Host = 'localhost';
			G::$V->User = '';
			G::$V->Pass = '';
			G::$V->Name = '';
			G::$V->Tabl = 'g_'.substr(base_convert(uniqid(), 16, 36), -5).'_';

			G::$V->User2 = '';
			G::$V->Pass2 = '';
		}
	}


	protected $config=<<<'ENDOFCONFIG'
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
G::$G['MODE'] = 'prd'; //prd,tst,dev... used to flag debug behaviors
G::$G['siteEmail'] = '%2$s';

//Include Path: a list of paths under the webroot to check for included
//controllers, models, templates
//list in priority order, first found is used
//for example: G::$G['includePath'] = '/^MyApp;'.CORE;
G::$G['includePath'] = %11$s;

//disable the installer
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
//leave ['ro']['user'] blank to indicate only RW credentials used
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
//display vars
G::$G['VIEW']['_siteName'] = '%10$s';
/** **************************************************************************
 * /Settings for the View
 ****************************************************************************/

ENDOFCONFIG;

}


