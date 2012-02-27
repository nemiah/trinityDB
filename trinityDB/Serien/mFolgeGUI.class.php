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

class mFolgeGUI extends anyC implements iGUIHTMLMP2 {

	public static $files;
	public static $numb3rs;
	public static $found;
	public static $Serie;

	function  __construct() {
		parent::__construct();

		$bps = $this->getMyBPSData();

		$S = new Serie($bps["SerieID"]);

		$F = new mFile();
		$F->setDir($S->A("dir"), true);

		mFolgeGUI::$Serie = $S;

		$E = array();

		while($t = $F->getNextEntry())
			if(!$t->A("FileIsDir")) {
				$newFilename = $t->A("FileName");
				if(Util::isWindowsHost()) $newFilename = utf8_encode($newFilename);
				$E[] = $newFilename;
			}

		mFolgeGUI::$files = $E;
		mFolgeGUI::$found = array();
	}

	public function getHTML($id, $page){
		$bps = $this->getMyBPSData();

		$this->addOrderV3("season");
		$this->addOrderV3("episode");
		$this->addAssocV3("SerieID", "=", $bps["SerieID"]);
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mSerie");
		$gui->displayMode("BrowserLeft");

		$gui->replaceEvent("onEdit", "contentManager.editInPopup('Folge', %CLASSID, 'Display episode details')");

		#$gui->setDisplaySide("left");

		#$gesamt = $this->loadMultiPageMode($id, $page, 0);

		#$gui->isQuickSearchable(str_replace("GUI","",get_class($this)));
		#$gui->setMultiPageMode($gesamt, $page, 0, "contentLeft", str_replace("GUI","",get_class($this)));

		$numb3rs = array();
		while($e = $this->getNextEntry()){
			$episodeTag = "S".($e->A("season") < 10 ? "0" : "").$e->A("season")."E".($e->A("episode") < 10 ? "0" : "").$e->A("episode");
			$episodeTag2 = $e->A("season")."x".($e->A("episode") < 10 ? "0" : "").$e->A("episode");
			$episodeTag3 = $e->A("season").($e->A("episode") < 10 ? "0" : "").$e->A("episode");

			$numb3rs[] = $episodeTag;

			foreach(mFolgeGUI::$files AS $k => $FN){
				$posTag2 = strpos($FN, $episodeTag2);
				
				if(stripos($FN, $episodeTag) !== false) {
					mFolgeGUI::$found[$e->getID()] = $FN;
					continue;
				}
				
				if(stripos($FN, $episodeTag2) !== false) {
					mFolgeGUI::$found[$e->getID()] = $FN;
					continue;
				}
				
				if(stripos($FN, $episodeTag3) !== false) {
					mFolgeGUI::$found[$e->getID()] = $FN;
					continue;
				}

				#elseif($posTag2 !== false AND $FN[$posTag2-1]*1 == 0) {
				#	mFolgeGUI::$found[$e->getID()] = $FN;
				#}
			}
		}

		$this->resetPointer();

		mFolgeGUI::$numb3rs = $numb3rs;

		#print_r(mFolgeGUI::$found);

		$gui->name("Folge");
		#$gui->setObject($this);
		$gui->colWidth("FolgeID", "20px");
		$gui->attributes(array("season","episode","name","FolgeID"));
		$gui->options(false, true, false, true);
/*
		$gui->activateFeature("displayMode", $this, false, false, false);
*/

		$gui->parser("FolgeID","mFolgeGUI::folgeParser");#,array("\$aid","\$name","\$season","\$episode","\$wanted")
		$gui->parser("name","mFolgeGUI::nameParser");#,array("\$aid", "\$season", "\$episode")
/*
		$gui->hideAttribute("SerieID");
		*/
		try {
			return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}

	public static function nameParser($w, $E){
		#$p = HTMLGUI::getArrayFromParametersString($p);

		$Wa = new Button("Wanted?", "./images/i2/".($E->A("wanted") == "0" ? "not" : "")."ok.gif");
		$Wa->type("icon");
		$Wa->style("float:right;");
		$Wa->rmePCR("Folge", $E->getID(), "toggleWanted", "", "contentManager.updateLine('', ".$E->getID().", 'mFolge');");

		$sub = "";

		if(isset(mFolgeGUI::$found[$E->getID()]))
			$sub = mFolgeGUI::$found[$E->getID()];

		$sub = str_replace($E->A("episode"), "<span style=\"color:red;\">".$E->A("episode")."</span>", $sub);

		return $Wa.$w."<br /><small style=\"color:grey;\">$sub</small>";

	}

	public static function folgeParser($w, $E){
		#$p = HTMLGUI::getArrayFromParametersString($p);

		$icon = "stop";

		if(isset(mFolgeGUI::$found[$E->getID()]))
			$icon = "note";

		$F = new Folge($w);
		#return $F->getNewFileName(mFolgeGUI::$Serie, "avi");
		if($F->fileExists(mFolgeGUI::$Serie))
			$icon = "okCatch";


		$B = new Button("","./images/i2/$icon.png");
		$B->type("icon");
		if($icon == "note")
			$B->rmePCR("Folge", $E->getID(), "renameFile", addslashes(mFolgeGUI::$found[$E->getID()]), "contentManager.reloadFrame('contentLeft');");
		
		return $B;

	}

	public function getACHTML($attributeName, $query){
		$gui = new HTMLGUI();
		
		switch($attributeName){
			case "quickSearchmFolge":
				
				$this->setSearchStringV3($query);
				$this->setSearchFieldsV3(array("name"));
				$this->setLimitV3("10");
				$this->lCV3();
				
				$gui->setAttributes($this->collector);
				$gui->setShowAttributes(array("name"));
				
				
				$_SESSION["BPS"]->registerClass(get_class($gui));
				$_SESSION["BPS"]->setACProperty("targetFrame","contentLeft");
				$_SESSION["BPS"]->setACProperty("targetPlugin","Folge");
				$gui->autoCheckSelectionMode(get_class($this));
				echo $gui->getACHTMLBrowser("quickSearchLoadFrame");
			break;

		}
	}
}
?>