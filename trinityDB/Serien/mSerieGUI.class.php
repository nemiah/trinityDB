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

class mSerieGUI extends anyC implements iGUIHTMLMP2, iCategoryFilter {


	public function getHTML($id, $page){
		$this->addOrderV3("name");
		$this->filterCategories();
		$this->loadMultiPageMode($id, $page, 0);
		
		$gui = new HTMLGUIX($this);
		$gui->version("mSerie");

		$gui->options(true, true, true, true);
		$gui->name("Serie");
		
		$gui->attributes(array("name", "sprache"));

		$gui->parser("name", "mSerieGUI::nameParser");

		$Tab = new HTMLSideTable("left");

		$B = new Button("check for\nupdates", "./trinityDB/Serien/Updates.png");
		$B->rmePCR("mSerie", "", "checkUpdates", "", "Popup.display('Updates', transport);");
		$Tab->addRow($B);

		try {
			return ($id == -1 ? $Tab : "").$gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}

	public function getAvailableCategories(){
		$AC = new anyC();
		$AC->setCollectionOf("Serie");
		$AC->addGroupV3("status");

		$status = array();
		while($S = $AC->getNextEntry())
			$status[$S->A("status")] = $S->A("status");

		return $status;
	}

	public function getCategoryFieldName(){
		return "status";
	}

	public function checkUpdates(){
		$adapter = new thetvdbcomAdapter();
		$adapter->update(true);
	}

	public static function nameParser($w, $E){
		$B = new Button("display episodes","./images/i2/folder.png");
		$B->style("float:left;margin-right:5px;");
		$B->type("icon");
		$B->onclick("contentManager.loadFrame('contentLeft','mFolge',-1,0,'mFolgeGUI;SerieID:".$E->getID()."');");
		return $B.$w;
	}

	public function getACHTML($attributeName, $query){
		$gui = new HTMLGUI();
		
		switch($attributeName){
			case "quickSearchmSerie":
				
				$this->setSearchStringV3($query);
				$this->setSearchFieldsV3(array("name"));
				$this->setLimitV3("10");
				$this->lCV3();
				
				$gui->setAttributes($this->collector);
				$gui->setShowAttributes(array("name", "sprache"));
				
				
				$_SESSION["BPS"]->registerClass(get_class($gui));
				$_SESSION["BPS"]->setACProperty("targetFrame","contentLeft");
				$_SESSION["BPS"]->setACProperty("targetPlugin","Serie");
				$gui->autoCheckSelectionMode(get_class($this));
				echo $gui->getACHTMLBrowser("quickSearchLoadFrame");
			break;

		}
	}
}
?>