<?php
/**
 *  This file is part of trinityDB.

 *  trinityDB is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  trinityDB is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class JDGUI extends JD implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("DL");

		$gui->setLabel("JDDLType", "Type");
		$gui->setLabel("JDName", "Name");
		$gui->setLabel("JDHost", "Host");
		$gui->setLabel("JDPort", "Port");
		$gui->setLabel("JDUser", "User");
		$gui->setLabel("JDPassword", "Password");

		$gui->setFieldDescription("JDPort", "Default: JD Web 8765; QNap 8080; JD RC 10025; pyLoad 7227");

		$gui->insertSpaceAbove("JDUser");

		$gui->setShowAttributes(array(
			"JDDLType",
			"JDName",
			"JDHost",
			"JDPort",
			"JDUser",
			"JDPassword"
		));

		$gui->setStandardSaveButton($this);

		$ST = new HTMLSideTable("right");

		$gui->setType("JDDLType", "select");
		$gui->setOptions("JDDLType", array(0, 1, 2, 3), array("JDownloader Web", "QNap Downloader", "JDownloader RC", "pyLoad"));

		$B = $ST->addButton("test\ndownload", "./trinityDB/JD/testLink.png");

		$B->popup("testLink", "test link", "JD", $this->getID(), "testDownloadPopup");

		return $ST.$gui->getEditHTML();
	}

	/*public function testLink(){
		Util::PostToHost($this->A("JDHost"), $this->A("JDPort"), "/link_adder.tmpl", "none", "do=Add&addlinks=".urlencode("http://download.serienjunkies.org/f-a5663c31484a040f/rc_aloha113-720p.html"), "JD", "JD");
	
		echo "connection successful";
	}*/
	
	public function testDownloadPopup(){
		$F = new HTMLForm("tdl", array("link"));
		$F->getTable()->setColWidth(1, 60);
		$F->setSaveRMEPCR("test download", "", "JD", $this->getID(), "testDownload", OnEvent::closePopup("JD", "testLink"));
		
		echo $F;
	}
	
	public function testDownload($link){
		$this->download($link);
	}
}
?>