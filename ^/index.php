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
 * File        : /^/index.php
 *                website MVC entry point
 ****************************************************************************/

require_once __DIR__.'/includeme.php';
require_once LIB.'/Dispatcher.php';
require_once LIB.'/View.php';

G::$C = new Dispatcher(G::$G['CON']);
G::$V = new View(G::$G['VIEW']);
G::$C->Act();
G::close();
G::$V->render();
