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

class mJDownloadGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		#$this->setParser("JDownloadDate", "");
		
		$this->addOrderV3("JDownloadDate", "DESC");
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mJD");
		$gui->screenHeight();
		
		$gui->name("JDownload");
		
		$gui->attributes(array("JDownloadDate", "JDownloadRenameto"));
		
		$gui->parser("JDownloadDate", "parserDate");
		
		return $gui->getBrowserHTML($id);
	}

	public static function parserDate($w, $l, $E){
		$r = "";
		if($E->A("JDownloadRenamed") == 0)
			$r .= OnEvent::script ("\$j('#BrowsermJDownload".$E->getID()."').css('background-color', 'rgba(235, 64, 0, 0.4)');");
		
		return Util::CLDateTimeParser($w).$r;
	}
	
}
?>