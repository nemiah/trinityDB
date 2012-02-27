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
class IncomingPrettifyGUI extends IncomingPrettify implements iGUIHTML2 {
	function getHTML($id){
		
		
		$gui = new HTMLGUIX($this);
		$gui->name("IncomingPrettify");
		$gui->displayMode("popup");
		
		$gui->label("IncomingPrettifyFind", "Find");
		$gui->label("IncomingPrettifyReplace", "Replace");
		$gui->label("IncomingPrettifyIsActive", "Active?");
		$gui->label("IncomingPrettifyCaseSensitive", "Case sensitive?");

		$gui->type("IncomingPrettifyIsActive", "checkbox");
		$gui->type("IncomingPrettifyCaseSensitive", "checkbox");

		$gui->descriptionField("IncomingPrettifyReplace", "You may use //1, //2 and //3 for the found occurences");

		return $gui->getEditHTML();
	}

	public function getEditPopup($id){
		echo $this->getHTML($id);
	}
}
?>