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

require_once __DIR__.'/includeme.php';
require_once SITE.'/^/lib/Dispatcher.php';
require_once SITE.'/^/lib/View.php';

G::$C = new Dispatcher(G::$G['CON']);
G::$V = new View(G::$G['VIEW']);
G::$C->Act();
G::close();
G::$V->render();
