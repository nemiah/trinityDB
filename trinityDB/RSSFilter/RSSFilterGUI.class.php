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
class RSSFilterGUI extends RSSFilter implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("RSSFilter");

		$gui->setShowAttributes(array(
			"RSSFilterName",
			"RSSFilterFeed",
			"RSSFilterAdapter",
			"RSSFilterJDID",
			"RSSFilterAutoDL",
			"RSSFilterProviderRapidshare",
			"RSSFilterProviderNetload",
			"RSSFilterProviderUploaded"));
		
		$gui->setLabel("RSSFilterName", "Name");
		$gui->setLabel("RSSFilterFeed", "Feed URL");
		$gui->setLabel("RSSFilterAdapter", "Adapter");
		$gui->setLabel("RSSFilterJDID", "DL with");
		$gui->setLabel("RSSFilterProviderRapidshare", "Rapidshare.com");
		$gui->setLabel("RSSFilterProviderNetload", "Netload");
		$gui->setLabel("RSSFilterProviderUploaded", "Uploaded");
		$gui->setLabel("RSSFilterAutoDL", "Auto-DL?");

		$gui->setType("RSSFilterProviderRapidshare", "checkbox");
		$gui->setType("RSSFilterProviderNetload", "checkbox");
		$gui->setType("RSSFilterAutoDL", "checkbox");
		$gui->setType("RSSFilterProviderUploaded", "checkbox");

		$gui->insertSpaceAbove("RSSFilterJDID");

		$gui->setFieldDescription("RSSFilterAutoDL", "Currently only works when using JDownloader RC, Qnap and pyLoad. Will have no effect otherwise.");

		$gui->selectWithCollection("RSSFilterJDID", new mJDGUI(), "JDName", "nicht herunterladen");

		$gui->setStandardSaveButton($this);

		$Tab = new HTMLSideTable("right");

		$B = new Button("show filtered\nfeed", "./trinityDB/RSSFilter/Filtered.png");
		$B->onclick("window.open('./trinityDB/RSSFilter/FilteredFeed.php?RSSFilterID=".$this->getID()."', 'Filtered feed');");
		$Tab->addRow($B);

		$FB = new FileBrowser();
		$FB->addDir(Util::getRootPath()."trinityDB/RSSFilter");

		$Adapters = $FB->getAsLabeledArray("iFeedFilter", ".class.php", true);

		$gui->setType("RSSFilterAdapter", "select");
		$gui->setOptions("RSSFilterAdapter", array_values($Adapters), array_keys($Adapters));

		return $Tab.$gui->getEditHTML();
	}
}
?>