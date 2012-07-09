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
 * File        : /^CLI/config.php
 *                website CLI configuration file
 ****************************************************************************/


/** **************************************************************************
 * CLI command list
 *  list which commands are available in the Graphite Shell, and their actor
 *  G::$G['CLI']['command'] = array('actor', 'help description', refresh);
 ****************************************************************************/

G::$G['CLI']['login']  = array('Account', 'show login form', true);
G::$G['CLI']['logout'] = array('Account', 'logout', true);
G::$G['CLI']['date']   = array('Gsh', 'output current datetime');
G::$G['CLI']['clear']  = array('Gsh', 'clear the history buffer', true);
G::$G['CLI']['echo']   = array('Gsh', 'write arguments to standard output');
G::$G['CLI']['help']   = array('Gsh', 'display this message');
G::$G['CLI']['argv']   = array('Gsh', 'print_r arguments');

/** **************************************************************************
 * /CLI command list 
 ****************************************************************************/
