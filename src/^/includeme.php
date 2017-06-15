<?php
/**
 * website base include file
 * File : /^/includeme.php
 *
 * PHP version 5.6
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

defined('NOW') or define('NOW', microtime(true));
require_once __DIR__.'/lib/Profiler.php';
$_Profiler = Profiler::getInstance(NOW);
$_Profiler->mark('includeme');
// the root of this website
define('SITE', dirname(dirname(__FILE__)));
// Graphite Version indicator, for scripts interacting herewith
define('GVER', 5);

// to save from having to work around magic quotes, just refuse to work with it
if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
    die('disable magic quotes');
}

// Create default values for missing $_SERVER variables
// This is most useful for running scripts from CLI with `php -f`
if (!isset($_SERVER['SERVER_NAME'])) {
    if ('/var/www/vhosts/' == substr(__DIR__, 0, 16)) {
        $_SERVER['SERVER_NAME'] = substr(__DIR__, 16, strpos(__DIR__, '/', 17) - 16);
    } elseif ('/mnt/vhosts/' == substr(__DIR__, 0, 12)) {
        $_SERVER['SERVER_NAME'] = substr(__DIR__, 12, strpos(__DIR__, '/', 13) - 12);
    } else {
        $_SERVER['SERVER_NAME'] = '';
    }
}
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = __FILE__;
}
if (!isset($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
define('G_REMOTE_ADDR', $_SERVER['REMOTE_ADDR']);
if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

require_once SITE.'/^/lib/G.php';
require_once SITE.'/^/config.php';
require_once SITE.'/^/lib/AutoLoader.php';
require_once SITE.'/^/lib/functions.php';

if (file_exists(SITE."/plugins/autoload.php")) {
    include_once SITE."/plugins/autoload.php";
}
AutoLoader::registerDirectory();
spl_autoload_register(array('AutoLoader', 'loadClass'));
G::$Factory = new Factory();
DataBroker::setDict(G::$G['db']['ProviderDict']);

Localizer::setLanguage(G::$G['language']);

define('MODE', G::$G['MODE']);      // controls a few things that assist dev

if ('dev' == MODE) {
    error_reporting(E_ALL | E_STRICT);
}
if (isset(G::$G['timezone'])) {
    date_default_timezone_set(G::$G['timezone']);
}

if (file_exists(SITE."/assets/version.php")) {
    $version = include SITE."/assets/version.php";
} else {
    $version = '0000.00.00';
}
define('VERSION', $version);

// if no DB host was specified, don't load DB or DB-based Security
if ('' == G::$G['db']['host']) {
    return;
}

$_Profiler->mark('mysql_connect');
// setup DB connection or fail.
G::$m = G::$M = new mysqli_(G::$G['db']['host'],
                            G::$G['db']['user'],
                            G::$G['db']['pass'],
                            G::$G['db']['name'],
                            null,
                            null,
                            G::$G['db']['tabl'],
                            G::$G['db']['log']);
if (isset(G::$G['db']['ro'])
    && isset(G::$G['db']['ro']['user'])
    && '' != G::$G['db']['ro']['user']
) {
    G::$m = new mysqli_(G::$G['db']['ro']['host'],
                        G::$G['db']['ro']['user'],
                        G::$G['db']['ro']['pass'],
                        G::$G['db']['ro']['name'],
                        null,
                        null,
                        G::$G['db']['tabl'],
                        G::$G['db']['log']);
    if (mysqli_connect_error()) {
        G::$m = G::$M;
    } else {
        G::$m->readonly = true;
    }
}
define('G_DB_TABL', G::$G['db']['tabl']);
$_Profiler->stop('mysql_connect');

// If we could not connect to database, display appropriate error
if (!G::$M->open) {
    G::msg('Could not connect to read/write database!', 'error');
    if (!G::$m->open) {
        G::msg('Could not connect to read-only database!', 'error');
        G::$G['CON']['path'] = G::$G['CON']['controller500'].'/500';
        return;
    } else {
        G::msg('Site operating in read-only mode.', 'error');
        G::$M = G::$m;
    }
}

$_Profiler->mark('authenticate');
G::$S = new Security();
if (G::$S->Login && 1 == G::$S->Login->flagChangePass
    && (!isset(G::$G['CON']['path'])
        || 'account/logout' != strtolower(trim(G::$G['CON']['path'], '/')))
) {
    G::msg('You must change your password before you can continue.');
    G::$G['CON']['path'] = 'Account/edit';
}
$_Profiler->stop('authenticate');

/**
 * Load per-application includeme.php files
 */
if (isset(G::$G['includePath'])) {
    foreach (explode(';', G::$G['includePath']) as $v) {
        $s = realpath(SITE.$v.'/includeme.php');
        if (false !== strpos($s, SITE.$v) && file_exists($s)
            && $s != __FILE__
        ) {
            require_once $s;
        }
    }
}
$_Profiler->stop('includeme');
