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
class JDownloadGUI extends JDownload implements iGUIHTML2 {
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->setParser("JDownloadRenamed", "Util::CLDateTimeParser");
		$this->setParser("JDownloadDate", "Util::CLDateTimeParser");
	}
	
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUIX($this);
		$gui->name("JDownload");
	
		#$B = $gui->addSideButton("Erneut\nherunterladen", "down");
		#$B = $gui->addSideButton("Dateigröße", "down");
		#$B->popup("", "Dateigröße", "JDownload", $this->getID(), "getFilesize");
		
		return $gui->getEditHTML();
	}
	
	function getFilesize(){
		$DL = new JD($this->A("JDownloadJDID"));
		$DL->filesize($this->A("JDownloadFilename"));
	}
}
?>