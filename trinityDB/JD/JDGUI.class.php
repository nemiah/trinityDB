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
		
		$gui = new HTMLGUIX($this);
		$gui->name("DL");

		$gui->attributes(array(
			"JDDLType",
			"JDName",
			"JDHost",
			"JDPort",
			"JDUser",
			"JDPassword",
			"JDWgetFilesDir",
			"JDLinkParser",
			"JDLinkParserUser",
			"JDLinkParserPassword"
		));
		
		$gui->label("JDDLType", "Type");
		$gui->label("JDName", "Name");
		$gui->label("JDHost", "Host");
		$gui->label("JDPort", "Port");
		$gui->label("JDUser", "User");
		$gui->label("JDPassword", "Password");
		$gui->label("JDWgetFilesDir", "Wget files dir");
		
		$gui->label("JDLinkParser", "Parser");
		$gui->label("JDLinkParserUser", "User");
		$gui->label("JDLinkParserPassword", "Password");

		$gui->descriptionField("JDPort", "Default: JD Web 8765; QNap 8080; JD RC 10025; pyLoad 7227");

		$gui->space("JDUser");
		$gui->space("JDLinkParser", "Link");
		
		$FB = new FileBrowser();
		$FB->addDir(__DIR__);
		$gui->type("JDLinkParser", "select", array_merge(array("" => "None"), $FB->getAsLabeledArrayF("iLinkParser", ".class.php", true)));
		
		$gui->type("JDDLType", "select", array("JDownloader Web", "QNap Downloader", "JDownloader RC", "pyLoad", "wget"));

		$gui->toggleFields("JDDLType", "4", array("JDWgetFilesDir"), array("JDHost", "JDPort", "JDUser", "JDPassword"));
		
		$B = $gui->addSideButton("test\ndownload", "./trinityDB/JD/testLink.png");
		$B->popup("testLink", "test link", "JD", $this->getID(), "testDownloadPopup");

		return $gui->getEditHTML();
	}

	/*public function testLink(){
		Util::PostToHost($this->A("JDHost"), $this->A("JDPort"), "/link_adder.tmpl", "none", "do=Add&addlinks=".urlencode("http://download.serienjunkies.org/f-a5663c31484a040f/rc_aloha113-720p.html"), "JD", "JD");
	
		echo "connection successful";
	}*/
	
	public function testDownloadPopup(){
		$F = new HTMLForm("tdl", array("link"));
		$F->getTable()->setColWidth(1, 60);
		$F->setSaveRMEPCR("test download", "", "JD", $this->getID(), "testDownload", "function(t){ \$j('#downloadResult').html(t.responseText); }");
		
		echo $F."<pre style=\"padding:5px;\" id=\"downloadResult\"></pre>";
	}
	
	public function testDownload($link){
		echo $this->download($link);
	}
}
?>