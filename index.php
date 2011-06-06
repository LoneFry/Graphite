<?php
/*****************************************************************************
 * Project     : Graphite
 *                Simple MVC web-application framework
 * Created By  : LoneFry
 *                dev@lonefry.com
 * License     : CC BY-NC-SA
 *                Creative Commons Attribution-NonCommercial-ShareAlike
 *                http://creativecommons.org/licenses/by-nc-sa/3.0/
 *
 * File        : /index.php
 *                website MVC entry point
 ****************************************************************************/

require_once '^/includeme.php';
require_once LIB.'/Controller.php';
require_once LIB.'/View.php';

G::$C=new Controller(G::$G['CON']);
G::$V=new View(G::$G['VIEW']);
G::$C->Act();
G::$V->_login_id=G::$S->Login?G::$S->Login->login_id:0;
G::$V->_loginname=G::$S->Login?G::$S->Login->loginname:'world';
G::$S->close();
G::$M->close();
G::$m->close();
G::$V->render();
