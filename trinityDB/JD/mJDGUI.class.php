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

class mJDGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mJD");

		$gui->name("DL");
		
		$gui->attributes(array("JDName"));
		
		#$B = $gui->addSideButton("Downloads\nRSS feed", "./trinityDB/JD/rss.png");
		#$B->onclick("window.open('./trinityDB/JD/exportRSS.php');");
		
		$B = $gui->addSideButton("Neuer Download", "new");
		$B->popup("", "Download", "mJD", -1, "downloadPopup");
		
		$B = $gui->addSideButton("Downloads\nanzeigen", "empty");
		$B->loadFrame("contentRight", "mJDownload");
		
		return $gui->getBrowserHTML($id);
	}

	public function downloadPopup(){
		$J = array();
		$AC = anyC::get("JD");
		while($D = $AC->getNextEntry())
			$J[$D->getID()] = $D->A("JDName");
		
		$F = new HTMLForm("tdl", array("link", "JD"));
		
		$F->setType("link", "textarea");
		$F->setType("JD", "select", null, $J);
		
		$F->getTable()->setColWidth(1, 60);
		$F->setSaveRMEPCR("Download", "", "mJD", -1, "download", "function(t){ \$j('#downloadResult').html(t.responseText); }");
		
		$F->setInputStyle("link", "font-size:10px;height:200px;");
		
		echo $F."<pre style=\"padding:5px;\" id=\"downloadResult\"></pre>";
	}

	public function download($JDID, $link){
		$JD = new JD($JDID);
		
		if(trim($link) == "")
			return;
		
		$ex = explode("\n", trim($link));
		
		echo "<div style=\"overflow:auto;max-height:400px;\">";
		
		foreach($ex AS $l)
			echo $l.": ".($JD->download($l) ? "OK" : "FEHLER")."<br>";
		
		echo "</div>";
		
	}
}
?>