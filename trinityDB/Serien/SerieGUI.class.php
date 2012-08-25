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
class SerieGUI extends Serie implements iGUIHTML2, icontextMenu {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("Serie");

		$gui->setType("adapter", "hidden");
		#$gui->setType("lastupdate", "readonly");
		$gui->setType("siteID", "hidden");
		$gui->setType("url", "hidden");
		$gui->setType("description", "textarea");

		$gui->setLabel("sprache", "Language");
		$gui->setLabel("lastupdate", "Last update");
		$gui->setLabel("RSSFilterID", "RSS feed");
		$gui->setLabel("altFileName1", "alt. file name");
		$gui->setLabel("altFeedName1", "alt. feed name");

		$gui->setFieldDescription("altFileName1", "This is useful if the filenames do not match the full series name. trinityDB will then look for both names on the harddrive. For example 'switch' instead of 'Switch Reloaded'.");
		$gui->setFieldDescription("altFeedName1", "This is useful if the feed names do not match the full series name. trinityDB will then look for both names in the feeds. For example 'Human.Target.2010' instead of 'Human Target (2010)'.");

		$gui->setParser("lastupdate", "SerieGUI::lastUpdateParser");

		$gui->setType("quality", "select");
		$gui->setOptions("quality", array_keys(self::getQualities()), array_values(self::getQualities()));

		$gui->setType("sprache", "select");
		$gui->setOptions("sprache", array("en", "de"), array("english", "deutsch"));

		$gui->setStandardSaveButton($this);

		$gui->insertSpaceAbove("RSSFilterID");

		$gui->selectWithCollection("RSSFilterID", new mRSSFilterGUI(), "RSSFilterName", "none");

		$tab = new HTMLSideTable("right");

		$B = new Button("Download\nepisodes","./trinityDB/Serien/Updates.png");
		$B->rmePCR("Serie", $this->ID, "downloadEpisodes", array("1"), "Popup.display('Download-Status', transport);");

		$S = new Button("Settings", "./images/i2/settings.png");
		$S->type("icon");
		$S->style("float:right;margin-right:-20px;");
		$S->contextMenu("Serie", "download", "Settings");

		$tab->addRow($S.$B);


		$B = new Button("Show\nepisodes","./trinityDB/Serien/Folge.png");
		$B->onclick("contentManager.loadFrame('contentLeft','mFolge',-1,0,'mFolgeGUI;SerieID:".$this->getID()."');");
		$tab->addRow($B);


		#$B = new Button("Find new\nepisodes","./trinityDB/RSSFilter/RSSFilter.png");
		#$B->rmePCR("Serie", $this->ID, "checkRSS", "", "Popup.display('Episoden-Status', transport);");
		#$tab->addRow($B);


		$B = new Button("Check\nepisodes","okCatch");
		$B->rmePCR("Serie", $this->ID, "checkAllEpisodes", "", "Popup.display('Episoden-Status', transport);");
		$tab->addRow($B);

		return $tab.$gui->getEditHTML();
	}

	public static function getQualities($nr = null){
		$qs = array(0 => "any", 1 => "360p", 6 => "480p", 2 => "720p", 3 => "1080i", 4 => "1080p", 5 => "iTunes");

		if($nr != null) return $qs[$nr];
		return $qs;
	}

	public static function lastUpdateParser($w){
		return Util::CLDateParser($w)." ".Util::CLTimeParser($w);
	}

	public function getContextMenuHTML($identifier){
		$F = new HTMLForm("dlSettings", array("dlCover"));

		$F->setType("dlCover", "checkbox");
		$F->setLabel("dlCover", "DL Cover?");
		$F->setValue("dlCover", mUserdata::getGlobalSettingValue("trinityDBdlCover", "0"));

		$F->setSaveRMEPCR("save", "./images/i2/save.gif", "Serie", "-1", "saveContextMenu", "function() { phynxContextMenu.stop(); }");

		echo $F;
	}

	public function saveContextMenu($identifier, $key){
		$F = new Factory("Userdata");
		$F->sA("UserID", "-1");
		$F->sA("name", "trinityDBdlCover");

		$U = $F->exists(true);
		if($U !== false){
			$U->changeA ("wert", $identifier);
			$U->saveMe();
		} else {
			$F->sA("wert", $identifier);
			$F->store();
		}

		mUserdata::setUserdataS("trinityDBdlCover", $identifier);
	}
}
?>