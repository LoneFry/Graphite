<?php
/**
 * website core configuration file
 * File : /^/config.php
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

/** **************************************************************************
 * General settings
 ****************************************************************************/
G::$G['startTime'] = NOW;

G::$G['timezone'] = 'America/New_York';
G::$G['siteEmail'] = 'apache@localhost';
G::$G['contactFormSubject'] = 'Contact form message: ';
G::$G['MODE'] = 'prd';
// includePath relative to SITE
// each class will append it's own sub directory to each path
G::$G['includePath'] = '/^HTML5;/^';
G::$G['language'] = 'en_us';

// enable the installer -- reverse this when installed
G::$G['installer'] = true;
/** **************************************************************************
 * /General settings
 ****************************************************************************/


/** **************************************************************************
 * Database settings
 ****************************************************************************/
G::$G['db'] = array(
    'host' => '',
    'user' => '',
    'pass' => '',
    'name' => '',
    'tabl' => '',
    'log'  => false
);
G::$G['db']['ro'] = array(
    'host' => '',
    'user' => '',
    'pass' => '',
    'name' => ''
);

G::$G['db']['ProviderDict'] = array(
    'DataModel' => 'MySQLDataProvider',
    'Report'    => 'ReportDataProvider',
);
/** **************************************************************************
 * /Database settings
 ****************************************************************************/


/** **************************************************************************
 * Settings for Security
 ****************************************************************************/
// A blank encyption key prevents Record::encypt() and ::decrypt()
G::$G['SEC']['encryptionKey'] = '';

// Classes to use to produce and test password hash
G::$G['SEC']['hash_class'] = array(
    'PBKDF2PasswordHasher',
    'SHA1PasswordHasher',
);

// parameters for the PBKDF2 hashword generation method
G::$G['SEC']['PBKDF2'] = array(
    'algo'       => 'sha256',
    'iterations' => 1024,
    'salt_len'   => 32,
    'hash_len'   => 32,
    'sections'   => array(
        'algo', 'iterations', 'salt', 'PBKDF2',
    ),
);

// password policies for use by Security::validate_password()
// Security::validate_password() should be called when users change passwords
G::$G['SEC']['passwords'] = array(
    // valid passwords must match these regular expressions
    // If they do not match, the error is returned
    'require' => array(
        array('/^.{6,}$/',
            'Password must be at least six characters long.'),
    ),

    // valid passwords must NOT match these regular expressions
    // If they do match, the error and $matches array are passed to vsprintf
    'deny' => array(
        array('/password|12345/',
            'Password must not contain "%s".'),
    ),

    // whether to enforce policies in the admin forms
    'enforce_in_admin' => !true,
);
// Examples of useful patterns
/*
G::$G['SEC']['passwords']['require'][] = array(
    '/^(?=.*\d)(?=.*[a-zA-Z]).{8,}$/',
    'Password must be at least eight characters long and contain digits and letters.'
    );
 G::$G['SEC']['passwords']['require'][] = array(
    '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/',
    'Password must be at least eight characters long and contain digits, lower and upper letters, and symbols.'
    );
 */

/** **************************************************************************
 * Settings for Security
 ****************************************************************************/


/** **************************************************************************
 * Settings for the Dispatcher
 ****************************************************************************/
// Defaults
G::$G['CON']['controller'] = 'Installer';
G::$G['CON']['controller404'] = 'Default';
G::$G['CON']['controller500'] = 'Default';

// Passed Values
if (isset($_GET['controller'])) {
    G::$G['CON']['controller'] = $_GET['_controller'];
}
if (isset($_GET['action'])) {
    G::$G['CON']['action'] = $_GET['_action'];
}
if (isset($_GET['params'])) {
    G::$G['CON']['params'] = $_GET['_params'];
}
if (isset($_GET['argv'])) {
    G::$G['CON']['argv'] = $_GET['_argv'];
}
if (isset($_SERVER['PATH_INFO'])) {
    G::$G['CON']['path'] = $_SERVER['PATH_INFO'];
} elseif (isset($_POST['_path'])) {
    G::$G['CON']['path'] = $_POST['_path'];
} elseif (isset($_GET['_path'])) {
    G::$G['CON']['path'] = $_GET['_path'];
}
/** **************************************************************************
 * /Settings for the Dispatcher
 ****************************************************************************/


/** **************************************************************************
 * Settings for the View
 ****************************************************************************/

// display vars
G::$G['VIEW']['_siteName'] = 'Graphite Site';
G::$G['VIEW']['_siteURL'] = 'http://'.$_SERVER['SERVER_NAME'];
G::$G['VIEW']['_loginURL'] = '/Account/login';
G::$G['VIEW']['_logoutURL'] = '/Account/Logout';
G::$G['VIEW']['_header'] = 'header.php';
G::$G['VIEW']['_footer'] = 'footer.php';
G::$G['VIEW']['_debug'] = 'footer.debug.php';

G::$G['VIEW']['_meta'] = array(
    "description" => "Graphite MVC framework",
    "keywords"    => "Graphite,MVC,framework",
    "generator"   => "Graphite MVC Framework",
);
G::$G['VIEW']['_script'] = array(
    // '/path/to/script.js',
);
G::$G['VIEW']['_link'] = array(
    array('rel' => 'shortcut icon','type' => 'image/vnd.microsoft.icon','href' => '/favicon.ico'),
);
G::$G['VIEW']['_siteClass'] = '';

// login redirection vars
G::$G['VIEW']['_URI'] = isset($_POST['_URI']) ? $_POST['_URI'] : $_SERVER['REQUEST_URI'];
G::$G['VIEW']['_Lbl'] = isset($_POST['_Lbl']) ? $_POST['_Lbl'] : 'to the page you requested';

/** **************************************************************************
 * /Settings for the View
 ****************************************************************************/


/** **************************************************************************
 * Per-Application Default Settings
 *  Check each ^directory/ for a config
 *  Each application config should limit itself to G::$G[APPNAME]
 ****************************************************************************/
if ($_dir = opendir(SITE)) {
    while (false !== $_file = readdir($_dir)) {
        if ('^' == $_file[0] && '^' != $_file) {
            if (file_exists(SITE.'/'.$_file.'/config.php')) {
                include_once SITE.'/'.$_file.'/config.php';
            }
        }
    }
}
/** **************************************************************************
 * /Per-Application Settings
 ****************************************************************************/


/** **************************************************************************
 * Per-Domain Settings for multi-domain sites
 *  If you are not hosting a site on multiple domains, you can cautiously
 *  use this file as your only configuration file
 * We'll check for two files
 *  1. 'secrets.' which should not be in your repo, and contains credentials
 *  2. 'config.' which could be in your repo, and contains general configs
 * We'll check two places
 *  1. [webroot]/../siteConfigs/ which houses config files out of webroot
 *  2. [webroot] which is webroot
 * We'll check two versions of the current domain
 *  1. The SERVER_NAME according to $_SERVER['SERVER_NAME']
 *  2. The directory name of [webroot], applicable in most vhosting setups
 ****************************************************************************/
$tmppath = explode('/', SITE);
foreach (['secrets.','config.'] as $tmpfile) {
    foreach ([$_SERVER['SERVER_NAME'], end($tmppath)] as $tmpdomain) {
        if (file_exists(dirname(SITE).'/siteConfigs/'.$tmpfile.$tmpdomain.'.php')) {
            include_once dirname(SITE).'/siteConfigs/'.$tmpfile.$tmpdomain.'.php';
            continue 2;
        } elseif (file_exists(SITE.'/'.$tmpfile.$tmpdomain.'.php')) {
            include_once SITE.'/'.$tmpfile.$tmpdomain.'.php';
            continue 2;
        }
    }
}
unset($tmppath);
unset($tmpfile);
unset($tmpdomain);
/** **************************************************************************
 * /Per-Domain Settings for multi-domain sites
 ****************************************************************************/
