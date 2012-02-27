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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */
abstract class PersistentClass {
	protected $ID;
	protected $A = null;
	protected $Adapter = null;
	protected $storage = PHYNX_MAIN_STORAGE;#"MySQL";
	protected $noDeleteHideOnly = false;
	
	protected $languageClass;
	protected $texts;
	
	protected $myAdapterClass;
	protected $echoIDOnNew = false;
	
	protected function getMyBPSData(){
		$_SESSION["BPS"]->setActualClass(get_class($this));
		return $_SESSION["BPS"]->getAllProperties();
	}
	
	public function getNextID($getFirstID = "false"){
		$aC = new anyC();
		$aC->setCollectionOf($this->getClearClass());
		$aC->addOrderV3($this->getClearClass()."ID");
		if($getFirstID == "false") $aC->addAssocV3($this->getClearClass()."ID",">",$this->ID);
		$aC->setLimitV3("1");
		
		$t = $aC->getNextEntry();
		if($t == null) die("alert:GlobalMessages.A002");
		die($t->getID());
	}
	
	public function getPreviousID($getLastID = "false"){
		$aC = new anyC();
		$aC->setCollectionOf($this->getClearClass());
		$aC->addOrderV3($this->getClearClass()."ID","DESC");
		if($getLastID == "false") $aC->addAssocV3($this->getClearClass()."ID","<",$this->ID);
		$aC->setLimitV3("1");
		
		$t = $aC->getNextEntry();
		if($t == null) die("alert:GlobalMessages.A003");
		die($t->getID());
	}
	
	public function checkInputID($inputID){
		$n = $this->getClearClass();
		$A = new $n($inputID);
		$A->loadMe();
		if($A->getA() == null) die("alert:GlobalMessages.A004");
		else die($inputID);
	}
	
	public function isNoDelete(){
		return $this->noDeleteHideOnly;
	}
	
	function __construct($ID){
		$this->ID = $ID;
	}

	function newAttributes(){
		$n = $this->getClearClass()."Attributes";
		return new $n();
	}
/*
	function setA(Attributes $A){
		if($this->A == null) {
			$this->A = $A;
			return true;
		} else return false;
	}
	*/
	function getA(){
		return $this->A;
	}
	
	function changeA($name, $value){
		if($this->A == null) $this->loadMe();
		$this->A->$name = $value;
	}
	
	function getID(){ return $this->ID; }
	
	public function isCloneable(){
		return PMReflector::implementsInterface(get_class($this),"iCloneable");
	}
	
	function loadAdapter(){
		if($this->Adapter != null) return;
		/*$adapterToLoad = get_class($this);
		
	    if(strstr($adapterToLoad,"Adapter") OR strstr($adapterToLoad,"GUI"))
           		$adapterToLoad = get_parent_class($adapterToLoad);*/
            
	    $n = $this->myAdapterClass;
		if($this->myAdapterClass != null) $this->Adapter = new $n($this->ID, $this->storage);
		else $this->Adapter = new Adapter($this->ID, $this->storage);
	}

	function setParser($a,$f){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->addParser($a,$f);
	}

	function resetParsers(){
		if($this->Adapter == null) $this->loadAdapter();
		$this->Adapter->resetParsers();
	}
	
	function getClearClass(){
		if(isset($this->table) AND $this->table != null) return $this->table;
		$n = get_class($this);
		if(strstr($n,"GUI")) $n = get_parent_class($this);
		if($n == "PersistentObject") $n = str_replace("GUI","",get_class($this));
		elseif(strstr($n,"GUI")) $n = get_parent_class(get_parent_class($this));
		return $n;
	}

	function loadMe(){
	    $this->loadAdapter();
		if($this->A == null) $this->A = $this->Adapter->loadSingle($this->getClearClass(get_class($this)));
	}
	
	public function loadMeT(){
	    $this->loadAdapter();
		if($this->A == null) {
			$this->A = $this->Adapter->loadSingleT($this->getClearClass(get_class($this)));
			$n = $this->getClearClass(get_class($this))."ID";
			if(isset($this->A->$n)) $this->ID = $this->A->$n;
			unset($this->A->$n);
		}
	}
	
	function loadMeOrEmpty(){
		$this->loadMe();
		/*$this->loadAdapter();
		if($this->A == null) {
			$r = $this->A = $this->Adapter->loadSingle($this->getClearClass(get_class($this)));
			if($r == null) $this->A = $this->newAttributes();
		}*/
		if($this->A == null) $this->A = $this->newAttributes();

	}

	function forceReload(){
	    $this->loadAdapter();
		$this->A = $this->Adapter->loadSingle($this->getClearClass(get_class($this)));
	}

	function newMe($checkUserData = true, $output = false){
		if($checkUserData) mUserdata::checkRestrictionOrDie("cantCreate".str_replace("GUI","",get_class($this)));
		
	    $this->loadAdapter();
	    if($this->A == null) $this->loadMe();
	    
        if($output)
	        if($this->echoIDOnNew) echo $this->ID;
	        else echo "message:GlobalMessages.M003";
	        
        return $this->ID = $this->Adapter->makeNewLine($this->getClearClass(get_class($this)),$this->A);
	}

	function saveMe($checkUserData = true, $output = false){
		if($checkUserData) mUserdata::checkRestrictionOrDie("cantEdit".str_replace("GUI","",get_class($this)));
		$this->loadAdapter();
		#$this->loadMe();
		$this->Adapter->saveSingle($this->getClearClass(get_class($this)),$this->A);
		
	    if($output) echo "message:GlobalMessages.M002";
	}
	
	function deleteMe() {
		mUserdata::checkRestrictionOrDie("cantDelete".str_replace("GUI","",get_class($this)));

	    $this->loadAdapter();
		if(!$this->noDeleteHideOnly) $this->Adapter->deleteSingle($this->getClearClass(get_class($this)));
		else {
			$this->loadMe();
			$this->A->isDeleted = 1;
			$this->saveMe();
		}
	}

	/**
	 * @deprecated This method is deprecated and should not be used. Use loadTranslation instead
	 * @param HTMLGUI $gui
	 */
	function loadGUITranslation(HTMLGUI $gui){
		throw new FunctionDeprecatedException("PersistentClass", "loadGUITranslation");

	}
	
	function loadTranslation($forClass = null){
		if($forClass == null) $forClass = $this->getClearClass();
		if($this->languageClass == null){
			try {
				$n = $forClass."_".$_SESSION["S"]->getUserLanguage();
				$this->languageClass = new $n();
			} catch(ClassNotFoundException $e){
				try {
					$n = $forClass."_de_DE";
					$this->languageClass = new $n();
				} catch(ClassNotFoundException $e){
					return null;
				}
			}
		}
		
		$this->texts = $this->languageClass->getText();

		return $this->languageClass;
	}
	
	function getGUIClass(){
		$n = get_class($this)."GUI";
		$this->loadMe();
		$g = new $n($this->ID);
		$g->setA($this->getA());
		return $g;
	}
	
	public function getXML(){
		$this->loadMe();
		$XML = new XML();
		$XML->setObject($this);
		$XML->setXMLHeader();
		return $XML->getXML();
	}

	public function A($attributeName){
		if($this->A == null) $this->loadMe();
		
		if(!isset($this->A->$attributeName)) return null;
		return $this->A->$attributeName;
	}
}
?>
