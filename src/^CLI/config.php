<?php
/**
 * website CLI configuration file
 * File : /^CLI/config.php
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
 * CLI command list
 *  list which commands are available in the Graphite Shell, and their controller
 *  G::$G['CLI']['command'] = array('controller', 'help description', refresh);
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
