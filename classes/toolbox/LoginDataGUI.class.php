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
class LoginDataGUI extends Userdata implements iGUIHTML2 {
	public function loadMe(){
		parent::loadMe();

		$values = split("::::",$this->A->wert);

		$this->A->server = "";
		$this->A->benutzername = "".isset($values[0]) ? $values[0] : "";
		$this->A->passwort = "".isset($values[1]) ? $values[1] : "";
		$this->A->optionen = "";


		foreach($values AS $va){
			if(strpos($va, "o:") === 0)
				$this->A->optionen = ereg_replace("^o:","",$va);

			if(strpos($va, "s:") === 0)
				$this->A->server = ereg_replace("^s:","",$va);
		}
	}
	function getHTML($id){
		
		$this->loadMeOrEmpty();

		if($id == -1){
			$this->A->typ = "LoginData";
		}


		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("LoginData");

		$gui->setLabel("UserID", "Benutzer");
		$gui->setLabel("name", "Typ");

		$gui->setType("typ", "hidden");
		$gui->setType("wert", "hidden");
		$gui->setType("passwort", "password");



		$onkeyup = "$('wert').value = $('benutzername').value+'::::'+$('passwort').value+($('server').value != '' ? '::::s:'+$('server').value : '')+($('optionen').value != '' ? '::::o:'+$('optionen').value : '')";
		$gui->setInputJSEvent("benutzername", "onkeyup", $onkeyup);
		$gui->setInputJSEvent("server", "onkeyup", $onkeyup);
		$gui->setInputJSEvent("passwort", "onkeyup", $onkeyup);
		$gui->setInputJSEvent("optionen", "onkeyup", $onkeyup);

		$gui->insertSpaceAbove("server");

		$U = new Users();
		$U->addAssocV3("isAdmin", "=", "0");

		$Users = array();
		$Users[-1] = "alle Benutzer";
		while($t = $U->getNextEntry())
			$Users[$t->getID()] = $t->A("name");

		$gui->setType("UserID", "select");
		$gui->setOptions("UserID", array_keys($Users), array_values($Users));

		$dataTypes = mLoginDataGUI::getNames();
		$gui->setType("name", "select");
		$gui->setOptions("name", array_keys($dataTypes), array_values($dataTypes));

		$gui->setStandardSaveButton($this,"mLoginData");
		
		return $gui->getEditHTML();
	}

	public function saveMe($checkUserData = true, $output = false){

		print_r($this->A);

		#parent::saveMe($checkUserData, $output);
	}
}
?>