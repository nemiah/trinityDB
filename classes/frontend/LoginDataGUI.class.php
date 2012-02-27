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
class LoginDataGUI extends LoginData implements iGUIHTML2 {
	function getHTML($id){
		$gui = $this->getGUI($id);

		$gui->setStandardSaveButton($this,"mLoginData");

		return $gui->getEditHTML();
	}

	private function getGUI($id){
		$this->loadMeOrEmpty();

		if($id == -1)
			$this->A->typ = "LoginData";

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

		$U = new Users();
		$U->addAssocV3("isAdmin", "=", "0");

		$Users = array();
		$Users[-1] = "alle Benutzer";
		while($t = $U->getNextEntry())
			$Users[$t->getID()] = $t->A("name");

		$gui->setType("UserID", "select");
		$gui->setOptions("UserID", array_keys($Users), array_values($Users));

		$dataTypes = LoginData::getNames();
		$gui->setType("name", "select");
		$gui->setOptions("name", array_keys($dataTypes), array_values($dataTypes));

		return $gui;
	}

	public function getPopup(){
		$bps = $this->getMyBPSData();
		
		$gui = $this->getGUI($this->getID());

		$gui->setJSEvent("onSave", "function() { Popup.close('', 'mailServer'); contentManager.reloadFrame('contentRight'); }");

		$gui->setStandardSaveButton($this,"mLoginData");

		$html = "";
		$html2 = "";
		if($bps != -1 AND isset($bps["preset"]) AND $bps["preset"] == "mailServer"){
			$BAbort = new Button("Abbrechen", "stop");
			$BAbort->onclick("Popup.close('', 'mailServer');");
			$BAbort->style("float:right;");
			
			$html = "<p style=\"padding:5px;\">{$BAbort}<small>Sie müssen hier nur Einstellungen vornehmen, wenn Sie diese Anwendung lokal auf einem Windows-Rechner betreiben oder direkt über einen SMTP-Server versenden möchten (z.B. Newsletter).</small></p>";

			$gui->setType("UserID", "hidden");
			$this->changeA("UserID", "-1");

			$gui->setType("name", "hidden");
			$this->changeA("name", "MailServerUserPass");
/*
			$gui->insertSpaceAbove("testMailOnSave");
			$gui->setType("testMailOnSave", "checkbox");
			$gui->setInputJSEvent("testMailOnSave", "onchange", "contentManager.toggleFormFields(this.checked ? 'show' : 'hide', ['testMailOnSaveSender', 'testMailOnSaveRecipient']);");
			$gui->setLabel("testMailOnSave", "testen?");
			$gui->setFieldDescription("testMailOnSave", "Sollen die Daten beim Speichern getestet werden?");

			$gui->setLabel("testMailOnSaveSender", "Absender");
			$gui->setFieldDescription("testMailOnSaveSender", "E-Mail-Adresse");
			$gui->setLabel("testMailOnSaveRecipient", "Empfänger");
			$gui->setFieldDescription("testMailOnSaveRecipient", "E-Mail-Adresse");

			$gui->setLineStyle("testMailOnSaveSender", "display:none;");
			$gui->setLineStyle("testMailOnSaveRecipient", "display:none;");
*/
			$gui->setType("optionen", "hidden");
		} else
			$gui->insertSpaceAbove("server");

		echo $html.$gui->getEditHTML();
	}
}
?>