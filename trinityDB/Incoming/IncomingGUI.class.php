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
 *  2007, 2008, 2009, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class IncomingGUI extends Incoming implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("Incoming");

		$gui->setLabel("IncomingDir", "Dir");
		$gui->setLabel("IncomingUseForDownloads", "Downloads");
		$gui->setLabel("IncomingUseForMoving", "Move files");

		$gui->setFieldDescription("IncomingUseForMoving", "Check this, if you want to move the files from this directory with the move scripts.");
		$gui->setFieldDescription("IncomingUseForDownloads", "This directory is used for downloads.");

		$gui->setType("IncomingUseForMoving", "checkbox");
		$gui->setType("IncomingUseForDownloads", "checkbox");

		$gui->setStandardSaveButton($this);
	
		return $gui->getEditHTML();
	}
}
?>