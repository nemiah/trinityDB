<?php
/*
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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class Session {
	static $instance;
	private $currentUser = null;
	private $afterLoginFunctions = array();
	//public $menu;
	
	function __construct(){	
		$this->currentUser = null;
		//$_SESSION["menu"] = MenuGUI::getSI();
		$_SESSION["messages"] = new SysMessages();
		$_SESSION["messages"]->addMessage(__CLASS__."-Singleton loaded");
	}
	
	public function getDBData($newFolder = null){
		if($newFolder == null) $newFolder = Util::getRootPath()."system/DBData/";
		if(!isset($_SERVER["HTTP_HOST"])) $_SERVER["HTTP_HOST"] = "*";
		$data = new mInstallation();
		if($newFolder != "") $data->changeFolder($newFolder);
		$data->setAssocV3("httpHost","=",$_SERVER["HTTP_HOST"]);
		#$data->loadCollectionV2();
		
		$n = $data->getNextEntry();
		
		if($n == null) {
			#$data = new mInstallation();
			#if($newFolder != "") $data->changeFolder($newFolder);
			$data->setAssocV3("httpHost","=","*");
			$n = $data->getNextEntry();
		}
		
		if($n != null){
			$n->changeFolder($newFolder);
			$d = $n->getA();
		} else {
			$I = new Installation(-1);
			$I->changeFolder($newFolder);
			$I->makeNewInstallation();
			$d = $I->getA();
		}
		$I2 = new Installation(-1);
		$s = PMReflector::getAttributesArray($I2->newAttributes());
		
		$t = array();
		foreach($s as $key => $value)
			$t[$value] = $d->$value;
			
			
		return $t;
	}
	
	public static function getSI() {
		if (!Session::$instance)
			Session::$instance = new Session();
		return Session::$instance;
	}
	
	public function getCurrentUser(){
		return $this->currentUser;
	}
	
	public function checkIfUserLoggedIn(){
		return $this->currentUser == null;
	}
	
	public function checkIfUserIsAllowed($plugin){
		if($plugin == "Menu") return true;
		if($plugin == "Messages") return true;
		if($plugin == "JSLoader") return true;
		if($plugin == "Printers") return true;
		if($plugin == "Credits") return true;
		if($plugin == "Desktop") return true;
		if($plugin == "DesktopLink") return true;
		if($plugin == "Userdata" AND $this->isUserAdmin()) return true;
		if($plugin == "BackupManager" AND $this->isUserAdmin()) return true;

		return ($this->isUserAdmin() == $_SESSION["CurrentAppPlugins"]->getIsAdminOnly($plugin));
	}
	
	public function isUserAdmin(){
		$UA = $this->currentUser->getA();
		return $UA->isAdmin;
	}

	public function isAltUser(){
		return get_class($this->currentUser) == "phynxAltLogin";
	}
	
	public function setLoggedInUser($U){
		$UA = $U->getA();
		$_SESSION["messages"]->addMessage("User $UA->name logged in, letting system know...");
		$this->currentUser = $U;
	}
	
	public function runOnLoginFunctions(){
		ob_start();
		foreach($this->afterLoginFunctions as $key => $value) {
			try {
				$c = new $key;
				if(!method_exists($c, $value)) continue;
			} catch(Exception $e){
				continue;
			}
			$f = "@".$key."::".$value."();";
			eval($f);
		}
		ob_end_clean();
	}
	
	public function logoutUser(){
		$this->currentUser = null;
	}
	/*
	public function checkForMainStorage(){
		
		$user = new User(1);
		#try {
		@$user->getA();
		#}
		#catch (DatabaseNotSelectedException $e) { return false; }
		#catch (NoDBUserDataException $e) { return false; }
		#catch (StorageException $e) { return true; }
		return true;
	}*/

	public static function isPluginLoaded($pluginName){

		if(isset($_SESSION["viaInterface"]) AND $_SESSION["viaInterface"] == true)
			return class_exists($pluginName, false);

		if(!isset($_SESSION["CurrentAppPlugins"])) return false;
		return in_array($pluginName,$_SESSION["CurrentAppPlugins"]->getAllPlugins());
	}

	public static function currentUser(){
		return $_SESSION["S"]->getCurrentUser();
	}

	public function checkForPlugin($pluginName){
		if(!isset($_SESSION["CurrentAppPlugins"])) return false;
		return in_array($pluginName,$_SESSION["CurrentAppPlugins"]->getAllPlugins());
	}

	public function registerOnLoginFunction($class, $function){
		$this->afterLoginFunctions[$class] = $function;
	}

	public static function getLanguage(){
		return $_SESSION["S"]->getUserLanguage();
	}

	public function getUserLanguage(){
		if($this->currentUser == null) {
			return Util::lang_getfrombrowser(array("de", "en", "it"), "de_DE");
			#return "de_DE";
		}
		$l = $this->currentUser->getA()->language;
		return $l == "" ? "de_DE" : $l;
	}
	
	public function init($application){
		$_SESSION[$application] = array();
		$_SESSION["BPS"] = new BackgroundPluginState();
		$_SESSION["JS"] = new JSLoader();
		$_SESSION["CurrentAppPlugins"] = new AppPlugins();
		$_SESSION["CurrentAppPlugins"]->scanPlugins();
		$_SESSION["applications"]->setActiveApplication($application);
		$_SESSION["CurrentAppPlugins"]->scanPlugins();
		$_SESSION["classPaths"] = array();
		$this->runOnLoginFunctions();
	}
	
	public function switchApplication($application){
		ob_start();
		$U = new UsersGUI();
		
		$c = $this->getCurrentUser();
		$d = array();
		$d["loginUsername"] = $c->getA()->username;
		$d["loginSHAPassword"] = $c->getA()->SHApassword;
		$d["loginSprache"] = $c->getA()->language;
		$d["anwendung"] = $application;
		$U->doLogin($d);
		ob_end_clean();
	}
	
	function getAgent()	{
		if (strstr($_SERVER['HTTP_USER_AGENT'],'Opera'))
			return "Opera";

		if (strstr($_SERVER['HTTP_USER_AGENT'],'MSIE'))
			return "IE";
			
		if (strstr($_SERVER['HTTP_USER_AGENT'],'Firefox'))
			return "Firefox";
			
		if (strstr($_SERVER['HTTP_USER_AGENT'],'Mozilla'))
			return "Mozilla";
		
		return "unknown";
	}
}
?>
