<?php
/**
 * website base include file
 * File : /^/includeme.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  Core
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

define('NOW', microtime(true));
// the root of this website
define('SITE', dirname(dirname(__FILE__)));
// the RELATIVE path of the core files
define('CORE', substr(dirname(__FILE__), strlen(SITE)));
// the ABSOLUTE path of the lib includes
define('LIB', SITE.CORE.'/lib');
// Graphite Version indicator, for scripts interacting herewith
define('GVER', 5);

// to save from having to work around magic quotes, just refuse to work with it
if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
    die('disable magic quotes');
}

require_once LIB.'/G.php';
require_once SITE.CORE.'/config.php';

define('MODE', G::$G['MODE']);      // controls a few things that assist dev
define('CONT', G::$G['CON']['URL']);// for use in URLs
if ('dev'==MODE) {
    error_reporting(E_ALL | E_STRICT);
}
if (isset(G::$G['timezone'])) {
    date_default_timezone_set(G::$G['timezone']);
}

// if not DB host was specified, don't load DB or DB-based Security
if ('' == G::$G['db']['host']) {
    return;
}

require_once LIB.'/mysqli_.php';
require_once LIB.'/Security.php';

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
    }
}
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

G::$S = new Security();
if (G::$S->Login && 1 == G::$S->Login->flagChangePass
    && (!isset(G::$G['CON']['path'])
        || 'account/logout' != strtolower(trim(G::$G['CON']['path'], '/'))
    )
) {
    G::msg('You must change your password before you can continue.');
    G::$G['CON']['path'] = 'Account/edit';
}

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

