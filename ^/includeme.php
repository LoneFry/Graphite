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
 * File        : /includeme.php
 *                website base include file
 ****************************************************************************/

define('NOW',microtime(true));
//the root of this website
define('SITE',dirname(dirname(__FILE__)));  
//the RELATIVE path of the core files
define('CORE',substr(dirname(__FILE__),strlen(SITE)));
//the ABSOLUTE path of the lib includes
define('LIB' ,SITE.CORE.'/lib');   

//to save from having to work around magic quotes, just refuse to work with it
if(get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
	die('disable magic quotes');
}

require_once LIB.'/G.php';
require_once SITE.CORE.'/config.php';

define('MODE',G::$G['MODE']);      //controls a few things that assist dev
define('CONT',G::$G['CON']['URL']);//for use in URLs
if('dev'==MODE)error_reporting(E_ALL | E_STRICT);
if(isset(G::$G['timezone'])){
	date_default_timezone_set(G::$G['timezone']);
}

require_once LIB.'/mysqli_.php';
require_once LIB.'/Security.php';

//if not DB host was specified, don't load DB or DB-based Security
if(''==G::$G['db']['host']){return;}

//setup DB connection or fail.
G::$m=G::$M=new mysqli_(G::$G['db']['host'],G::$G['db']['user'],G::$G['db']['pass'],G::$G['db']['name'],null,null,G::$G['db']['tabl'],G::$G['db']['log']);
if (mysqli_connect_error()) {
	die("MySQL Connect failed: ".mysqli_connect_error());
}
if(isset(G::$G['db']['ro'])){
	G::$m=new mysqli_(G::$G['db']['ro']['host'],G::$G['db']['ro']['user'],G::$G['db']['ro']['pass'],G::$G['db']['ro']['name'],null,null,G::$G['db']['tabl'],G::$G['db']['log']);
	if (mysqli_connect_error()){
		G::$m=G::$M;
	}
}

G::$S=new Security();
if(G::$S->Login && 1==G::$S->Login->flagChangePass){
	G::msg('You must change your password before you can continue.');
	G::$G['CON']['path']='Account/edit';
}

//header("Cache-control: private");//sometimes needed to allow IE to view src
