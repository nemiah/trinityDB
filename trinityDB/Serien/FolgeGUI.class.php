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
 *  2010, trinityDB - https://sourceforge.net/p/opentrinitydb/
 */
class FolgeGUI extends Folge implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("Folge");

		$gui->setType("description", "textarea");

		$gui->setLabel("lastupdate", "Last update");
		$gui->setLabel("airDate", "Air date");

		$gui->setParser("lastupdate", "FolgeGUI::lastUpdateParser");

		$gui->setStandardSaveButton($this);

		$gui->setShowAttributes(array("season", "episode", "airDate", "lastupdate", "description"));
		$gui->setIsDisplayMode(true);

		return str_replace("<div class=\"backgroundColor1 Tab\"", "<div class=\"backgroundColor1 Tab\" style=\"display:none;\"", $gui->getEditHTML());;
	}

	public function renameFile($originalFile){
		$S = new Serie($this->A("SerieID"));

		if(Util::isWindowsHost())
			$originalFile = utf8_decode($originalFile);

		if(rename($S->A("dir")."/".$originalFile, $S->A("dir")."/".$this->getNewFileName($S, $this->getSuffix($originalFile))))
			echo "message:'renaming successfull'";
		
	}

	public static function lastUpdateParser($w){
		return Util::CLDateParser($w)." ".Util::CLTimeParser($w);
	}
}
?>