<?php
/**
 * website HTML4 skin configuration file
 * File : /^HTML4/config.php
 *
 * PHP version 5.3
 *
 * @category Graphite
 * @package  HTML4
 * @author   LoneFry <dev@lonefry.com>
 * @license  CC BY-NC-SA http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link     http://g.lonefry.com
 */

// do not set configurations for ^HTML4 if ^HTML5 is present.
if (file_exists(dirname(__DIR__).'/^HTML5')) {
    return;
}

G::$G['VIEW']['_link'][] = array('rel' => 'stylesheet','type' => 'text/css','href' => '/^HTML4/css/default.css');

