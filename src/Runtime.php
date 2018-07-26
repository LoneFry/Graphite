<?php

namespace Graphite\core;

use Graphite\core\data\DataBroker;
use Graphite\core\data\mysqli_;

require_once __DIR__.'/functions.php';

class Runtime {
    /** @var Runtime $instance */
    private static $instance = null;
    /** @var Profiler $Profiler */
    public $Profiler;

    /**
     * Create and return singleton instance
     *
     * @return static
     */
    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct() {
        // Define the start time and start the Profiler
        defined('NOW') or define('NOW', microtime(true));
        $this->Profiler = Profiler::getInstance(NOW);
    }

    /**
     * Prepare and invoke the Controller and View
     */
    public function main() {
        $this->Profiler->mark('init');
        $this->init();
        $this->init_mysqli();
        $this->init_Security();
        G::$C = new Dispatcher(G::$G['CON']);
        G::$V = new View(G::$G['VIEW']);
        $this->Profiler->stop('init');

        $this->Profiler->mark('Controller');
        G::$C->Act();
        $this->Profiler->stop('Controller');

        $this->Profiler->mark('Clean-up');
        G::close();
        $this->Profiler->stop('Clean-up');

        $this->Profiler->mark('View');
        G::$V->preoutput();
        G::$V->output();
    }

    public function init() {
        $this->Profiler->mark(__METHOD__);

        // the root of this website
        define('SITE', $this->guessSiteRoot());
        $this->generateServerDefauts();

        require_once __DIR__.'/config.php';

        G::$Factory = new Factory();
        DataBroker::setDict(G::$G['db']['ProviderDict']);
        Localizer::setLanguage(G::$G['language']);

        // controls a few things that assist dev
        define('MODE', G::$G['MODE'] ?? 'prd');
        if ('dev' == MODE) {
            error_reporting(E_ALL | E_STRICT);
        }
        if (isset(G::$G['timezone'])) {
            date_default_timezone_set(G::$G['timezone']);
        }
        define('VERSION', G::$G['VERSION']);
    }

    public function init_mysqli() {
        // if no DB host was specified, don't load DB or DB-based Security
        if ('' == G::$G['db']['host']) {
            return;
        }

        $this->Profiler->mark(__METHOD__);
        // setup DB connection or fail.
        G::$m = G::$M = new mysqli_(G::$G['db']['host'],
            G::$G['db']['user'],
            G::$G['db']['pass'],
            G::$G['db']['name'],
            null,
            null,
            G::$G['db']['tabl'],
            G::$G['db']['log']);

        if (!empty(G::$G['db']['ro'])
            && !empty(G::$G['db']['ro']['user'])
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

        // If we could not connect to database, display appropriate error
        if (!G::$M->isOpen()) {
            G::msg('Could not connect to read/write database!', 'error');
            if (!G::$m->isOpen()) {
                G::msg('Could not connect to read-only database!', 'error');
                G::$G['CON']['path'] = G::$G['CON']['controller500'].'/500';
            } else {
                G::msg('Site operating in read-only mode.', 'error');
                G::$M = G::$m;
            }
        }
        $this->Profiler->stop(__METHOD__);
    }

    /**
     * Load Security
     */
    public function init_Security() {
        $this->Profiler->mark(__METHOD__);
        // If DB is not connected, don't load DB-based Security
        if (!is_null(G::$M) && !G::$M->isOpen()) {
            return;
        }

        G::$S = new Security();
        if (G::$S->Login && 1 == G::$S->Login->flagChangePass
            && (!isset(G::$G['CON']['path'])
                || 'account/logout' != strtolower(trim(G::$G['CON']['path'], '/')))
        ) {
            G::msg('You must change your password before you can continue.');
            G::$G['CON']['path'] = 'Account/edit';
        }
        $this->Profiler->stop(__METHOD__);
    }

    /**
     * Load per-application includeme.php files
     */
    public function do_includes() {
        $this->Profiler->mark(__METHOD__);

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
        $this->Profiler->stop(__METHOD__);
    }

    /**
     * Create default values for missing $_SERVER variables
     * This is most useful for running scripts from CLI with `php -f`
     */
    public function generateServerDefauts() {
        if (!isset($_SERVER['SERVER_NAME'])) {
            $paths = explode('/', SITE);
            $_SERVER['SERVER_NAME'] = array_pop($paths);
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
    }

    /**
     * Look at our current directory and guess the webroot path
     *
     * @return string Probable directory of webroot
     */
    public function guessSiteRoot() {
        $paths = explode('/', __DIR__);

        if ('/var/www/vhosts/' == substr(__DIR__, 0, 16)) {
            // Assume we're in /var/www/vhosts/[domain]
            $root = implode('/', array_slice($paths, 0, 5));
        } elseif ('/mnt/vhosts/' == substr(__DIR__, 0, 12)) {
            // Assume we're in /mnt/vhosts/[domain]
            $root = implode('/', array_slice($paths, 0, 4));
        } elseif ('/home/' == substr(__DIR__, 0, 6)) {
            // Assume we're in /home/[user]/[domain]
            $root = implode('/', array_slice($paths, 0, 4));
        } else {
            // Assume we're in a composer vendor dir, pick parent of vendor directory
            $root = dirname(substr(__DIR__, 0, strpos(__DIR__, '/graphite/core/')));
        }

        return $root;
    }
}
