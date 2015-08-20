<?php
/**
 * website MVC entry point
 * File : /^/index.php
 *
 * PHP version 5.3
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

$_Profiler->mark('init');
require_once __DIR__.'/includeme.php';
G::$C = new Dispatcher(G::$G['CON']);
G::$V = new View(G::$G['VIEW']);
$_Profiler->stop('init');

$_Profiler->mark('Controller');
G::$C->Act();
$_Profiler->stop('Controller');

$_Profiler->mark('Clean-up');
G::close();
$_Profiler->stop('Clean-up');

$_Profiler->mark('View');
G::$V->preoutput();
G::$V->output();
