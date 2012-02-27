<?php
/**
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

/**
 * Entfernen Sie das #-Zeichen vor der nächsten Zeile, um MySQLi zu deaktivieren
 */
#define("PHYNX_MAIN_STORAGE","MySQLo");

/**
 * Entfernen Sie das #-Zeichen vor der nächsten Zeile, um MSSQL (experimentell) zu aktivieren
 */
#define("PHYNX_MAIN_STORAGE","MSSQL");


if(!defined("PHYNX_MAIN_STORAGE"))
	if(function_exists("mysqli_connect"))
		define("PHYNX_MAIN_STORAGE","MySQL");
	else
		define("PHYNX_MAIN_STORAGE","MySQLo");

$GLOBALS["phynxLogPhpErrors"] = true;

if(session_name() == get_cfg_var("session.name"))
	session_name("phynx_".sha1(__FILE__));

if(
	(ini_get("open_basedir") == "" OR strpos(ini_get("open_basedir"), ini_get("session.save_path")) !== false) 
	AND isset($_COOKIE[ini_get("session.name")]) 
	AND !file_exists(ini_get("session.save_path")."/sess_".$_COOKIE[ini_get("session.name")])
	AND (!isset($_COOKIE["phynx_relocate"]) OR time() - $_COOKIE["phynx_relocate"] >= 3)
	AND file_exists(ini_get("session.save_path"))){
		
	unset($_COOKIE[ini_get("session.name")]);
	session_start();
	if(basename($_SERVER["SCRIPT_FILENAME"]) == "index.php") {
		setcookie("phynx_relocate", time(), time() + 600);
		header("location: index.php");
		exit();
	} else die("SESSION EXPIRED");
}
ini_set("zend.ze1_compatibility_mode","Off");

header("Content-Type: text/html; charset=UTF-8");
error_reporting(E_ALL);
set_error_handler("log_error");
if(function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Berlin');


if(!function_exists("array_fill_keys")){
	require dirname(__FILE__)."/basics.php";
	emoFatalError("I'm sorry, but your PHP version is too old.", "You need at least PHP version 5.2.0 to run this program.<br />You are using ".phpversion().". Please talk to your provider about this.", "phynx");
}
function log_error($errno, $errmsg, $filename, $linenum) {
	#if(defined('E_DEPRECATED') AND $errno == E_DEPRECATED) return;
	if(!$GLOBALS["phynxLogPhpErrors"]) return;
	
	if(strpos($filename, "PortscanGUI.class.php") !== false AND strpos($errmsg,"fsockopen") !== false) return;
	
	$errortype = Array(
		E_ERROR => 'Error',
		E_WARNING => 'Warning',
		E_PARSE => 'Parsing Error',
		E_NOTICE => 'Notice',
		E_CORE_ERROR => 'Core Error',
		E_CORE_WARNING => 'Core Warning',
		E_COMPILE_ERROR => 'Compile Error',
		E_COMPILE_WARNING => 'Compile Warning',
		E_USER_ERROR => 'User Error',
		E_USER_WARNING => 'User Warning',
		E_USER_NOTICE => 'User Notice',
		E_STRICT => 'Runtime Notice'
	);

	if(defined('E_RECOVERABLE_ERROR'))
		$errortype[E_RECOVERABLE_ERROR] = 'Catchable Fatal Error';

	if(defined('E_DEPRECATED'))
		$errortype[E_DEPRECATED] = 'Function Deprecated';
	
	if(strpos($errmsg, "mysql_pconnect() [<a href='function.mysql-pconnect'>function.mysql-pconnect</a>]: Access denied") !== false AND strpos($filename,"classes/backend/DBStorageU.class.php") !== null) return;
	if(strpos($errmsg, "mysqli::mysqli() [<a href='function.mysqli-mysqli'>function.mysqli-mysqli</a>]: (28000/1045): Access denied") !== false AND strpos($filename,"classes/backend/DBStorage.class.php") !== null) return;
	if(strpos($errmsg, "mysqli::mysqli() [<a href='function.mysqli-mysqli'>function.mysqli-mysqli</a>]: (42000/1044): Access denied") !== false AND strpos($filename,"classes/backend/DBStorage.class.php") !== null) return;

	if(!isset($_SESSION["phynx_errors"]))
		$_SESSION["phynx_errors"] = array();
	
	$_SESSION["phynx_errors"][] = array($errortype[$errno], $errmsg, $filename, $linenum);
}

/*
class Timer {
	private $start = 0;
	private $lastecho = 0;
	
	function __construct() {
		$this->start = microtime(true);
		$this->lastecho = microtime(true);
	}
	
	function display() {
		$used = microtime(true) - $this->start;
		echo round($used * 1000)."ms; dt = ".round((microtime(true) - $this->lastecho) * 1000)."ms<br />";
		$this->lastecho = microtime(true);
	}
	
	function set(){
		$this->lastecho = microtime(true);
	}
	
	function dd(){
		echo "dt = ".round((microtime(true) - $this->lastecho) * 1000)."ms<br />";
	}
}

$T = new Timer(); */
session_start();

if(!isset($_SESSION["classPaths"])) 
	$_SESSION["classPaths"] = array();
	
function registerClassPath($className, $classPath){
	$_SESSION["classPaths"][$className] = $classPath;
}


function __autoload($class_name) {

	$root = str_replace("system".DIRECTORY_SEPARATOR."connect.php", "", __FILE__);

	if(isset($_SESSION["classPaths"][$class_name])) {
		$path = $_SESSION["classPaths"][$class_name];

		require_once($path);
		return 1;
	}
	
	$standardPaths = array();
	$standardPaths[] = $root."classes/backend/";
	$standardPaths[] = $root."classes/frontend/";
	$standardPaths[] = $root."classes/toolbox/";
	$standardPaths[] = $root."classes/interfaces/";
	$standardPaths[] = $root."libraries/";
	$standardPaths[] = $root."specifics/";
	$standardPaths[] = $root."classes/exceptions/";

	foreach($standardPaths as $k => $v){
		$path = "$v".$class_name.'.class.php';

		if(is_file($path)) {
			require_once $path;
			registerClassPath($class_name, $path);
			return 1;
		}
	}
	if(isset($_SESSION["CurrentAppPlugins"]) AND count($_SESSION["CurrentAppPlugins"]->getFolders()) > 0) {

		foreach($_SESSION["CurrentAppPlugins"]->getFolders() as $key => $value){
			$path = $root."plugins/$value/$class_name.class.php";

			if(is_file($path)){
				require_once $path;
				registerClassPath($class_name, $path);
				return 1;
			}
			
			if($_SESSION["applications"]->getActiveApplication() != "nil"){
				$path = $root."".$_SESSION["applications"]->getActiveApplication()."/$value/$class_name.class.php";

				if(is_file($path)){
					require_once $path;
					registerClassPath($class_name, $path);
					return 1;
				}
			}
		}
	} else {
		$fp = opendir($root."plugins/");
		while(($file = readdir($fp)) !== false) {
			if(is_dir($root."plugins/$file")) {
				
				$fp2 = opendir($root."plugins/$file/");
				while(($file2 = readdir($fp2)) !== false) {
					$path = $root."plugins/$file/$file2/$class_name.class.php";
					if(is_file($path)){
						require_once $path;
						registerClassPath($class_name, $path);
						return 1;
					}
				}
				
			}
		}
	}

	if($class_name == "FPDF"){
		require_once $root."libraries/fpdf/fpdf.php";
		return 1;
	}
	
	if($class_name == "htmlMimeMail5"){
		require_once $root."libraries/mailer/htmlMimeMail5.php";
		return 1;
	}
	
	if(preg_match("/^i[A-Z].*/", $class_name)) {
		$_SESSION["messages"]->addMessage("Warning: Creating interface $class_name");
		eval('interface '.$class_name.' { } ');
	}
	else eval('class '.$class_name.' { ' .
		'    public function __construct() { ' .
		'        throw new ClassNotFoundException("'.$class_name.'"); ' .
		'    } ' .
		'} ');
	
}


function init(){
	$_SESSION["S"] = new Session();
	
	$_SESSION["applications"] = new Applications();
	$_SESSION["applications"]->scanApplications();
	
	$_SESSION["JS"] = new JSLoader();
	
	$_SESSION["CurrentAppPlugins"] = new AppPlugins();
	$_SESSION["CurrentAppPlugins"]->scanPlugins();
	
	if(!isset($_SESSION["DBData"])) 
		$_SESSION["DBData"] = $_SESSION["S"]->getDBData();

	/*$f = fopen("/tmp/phynx.log","w");
	fwrite($f, print_r($_SESSION, true));
	fclose($f);*/
	
	if($_SESSION["S"]->checkForPlugin("mAutoLogin"))
		mAutoLogin::doAutoLogin();
}

if(!isset($_SESSION["S"]) OR !isset($_SESSION["applications"]) OR $_SESSION["applications"]->numAppsLoaded() == 0)
	init();

?>
