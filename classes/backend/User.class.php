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
class User extends PersistentObject {
	
	public function getName(){
		if($this->A == null) $this->loadMe();
		return $this->A->name;
	}
	
	public function getA(){
		if($this->A == null) $this->loadMe();
		return $this->A;
	}
	
	function loadMe($empty = true){
		if($this->getID() > 20000){
			$S = Users::getAppServerClient();
			$this->setA($S->getUserById(Session::currentUser()->getA(), $this->ID));

			return;
		}
	    parent::loadMe();
		if($empty AND $this->A != null) $this->A->SHApassword = "";
	}
	
	function saveMe($checkUserData = true, $output = false){
		$U = new User($this->ID);
		$U->loadMe(false);
		if(mUserdata::getGlobalSettingValue("encryptionKey") == null) mUserdata::setUserdataS("encryptionKey", Util::getEncryptionKey(), "eK", -1);
		if($this->A->SHApassword != "") $this->A->SHApassword = sha1($this->A->SHApassword);
		else $this->A->SHApassword = $U->getA()->SHApassword;

		if($checkUserData) mUserdata::checkRestrictionOrDie("cantEdit".str_replace("GUI","",get_class($this)));

		$this->loadAdapter();
		$this->Adapter->saveSingle2($this->getClearClass(get_class($this)),$this->A);
		echo "message:GlobalMessages.M002";
	}
	
	function newMe($checkUserData = true, $output = false){
		if(mUserdata::getGlobalSettingValue("encryptionKey") == null) mUserdata::setUserdataS("encryptionKey", Util::getEncryptionKey(), "eK", -1);
		$this->A->SHApassword = sha1($this->A->SHApassword);
		parent::newMe($checkUserData, $output);
	}
	
	public function convertPassword(){
		$this->loadMe();
		if($this->A->password == ";;;-1;;;") return;
		
		$this->A->SHApassword = $this->A->password;
		$this->A->password = ";;;-1;;;";
		
		$this->saveMe();
	}
	
	public function newAttributes(){
		$A = new stdClass();
		
		$A->UserID = "";
		$A->name = "";
		$A->username = "";
		$A->password = "";
		$A->isAdmin = "";
		$A->SHApassword = "";
		$A->language = "";
		$A->UserEmail = "";
		$A->UserICQ = "";
		$A->UserJabber = "";
		$A->UserSkype = "";
		$A->UserTel = "";
		
		return $A;
	}
	
	public function copyUserRestrictions($fromUserId){
		$mUD = new mUserdata();
		$mUD->addAssocV3("UserID","=",$fromUserId);
		$mUD->lCV3();
		
		$cUD = new mUserdata();
		$cUD->addAssocV3("UserID","=",$this->ID);
		$cUD->addAssocV3("typ","=","uRest","AND","1");
		$cUD->addAssocV3("typ","=","relab","OR","1");
		$cUD->addAssocV3("typ","=","hideF","OR","1");
		$cUD->addAssocV3("typ","=","pSpec","OR","1");
		$cUD->addAssocV3("typ","=","pHide","OR","1");
		$cUD->lCV3();
		
		if($cUD->getNextEntry() != null) die("Target userdata not empty!");
		
		while(($t = $mUD->getNextEntry())){
			$A = $t->getA();
			$A->UserID = $this->ID;
			$nU = new Userdata(-1);
			$nU->setA($A);
			$nU->newMe();
		}
	}
}
?>
