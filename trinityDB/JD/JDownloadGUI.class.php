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
	
		$gui->parser("JDownloadURL", "parserURL");
		$gui->parser("JDownloadFilename", "parserFilename");
		$gui->parser("JDownloadFilesize", "parserSize");
		$gui->parser("JDownloadSerieID", "parserSerie");
		$gui->parser("JDownloadJDID", "parserJD");
		
		$gui->label("JDownloadSerieID", "Serie");
		$gui->label("JDownloadJDID", "JD");
		
		$B = $gui->addSideButton("Erneut\nherunterladen", "down");
		$B->popup("", "Erneut herunterladen", "JDownload", $this->getID(), "reDownload");

		$gui->optionsEdit(false, false);
		return $gui->getEditHTML();
	}
	
	public static function parserFilename($w){
		return "<span style=\"display:block;width:100%;word-break:break-all;\">$w</span>";
	}
	
	public static function parserURL($w){
		return "<small>$w</small>";
	}
	
	public static function parserJD($w){
		$JD = new JD($w);
		
		return $JD->A("JDName");
	}
	
	public static function parserSerie($w){
		$Serie = new Serie($w);
		
		return $Serie->A("name");
	}
	
	public static function parserSize($w){
		return Util::formatByte($w, 3);
	}
	
	/*function getFilesize(){
		$DL = new JD($this->A("JDownloadJDID"));
		$DL->filesize($this->A("JDownloadFilename"));
	}*/
	
	function reDownload(){
		$JD = new JD($this->A("JDownloadJDID"));
		$s = $JD->download($this->A("JDownloadFilename"), $this->A("JDownloadURL"), $this->A("JDownloadRenameto"), new Serie($this->A("JDownloadSerieID")));
		if($s)
			echo "<p>Download gestartet</p>";
		else
			echo "<p>Fehler</p>";
	}
}
?>