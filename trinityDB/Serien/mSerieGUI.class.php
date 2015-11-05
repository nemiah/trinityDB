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
	
	public static function getCalendarDetails($className, $classID, $T = null) {
		$K = new Kalender();
		if($T == null){
			$AC = anyC::get("Folge", "FolgeID", $classID);
			$AC->addJoinV3("Serie", "SerieID", "=", "SerieID");
			$AC->setFieldsV3(array("t1.name", "season", "episode", "t1.description", "airDate"));
			$T = $AC->getNextEntry();
		}
		
		$ex = explode("-", $T->A("airDate"));
		$day = mktime(8, 0, 0, $ex[1], $ex[2], $ex[0]);

		$KE = new KalenderEvent($className, $classID, $K->formatDay($day),"1800", $T->A("name")." S".($T->A("season") < 10 ? "0" : "").$T->A("season")."E".($T->A("episode") < 10 ? "0" : "").$T->A("episode"));
		
		#$KE->repeat(true, "yearly");
		$KE->endDay($K->formatDay($day));
		$KE->endTime("1845");
		$KE->icon("./fheME/FAdressen/birthday.png");
		$KE->summary($T->A("description"));
		return $KE;
	}

	public static function getCalendarData($firstDay, $lastDay) {
		$K = new Kalender();

		$AC = anyC::get("Folge");
		$AC->addAssocV3("UNIX_TIMESTAMP(STR_TO_DATE(airDate, '%Y-%m-%d')) + 120", ">=", $firstDay);
		$AC->addAssocV3("UNIX_TIMESTAMP(STR_TO_DATE(airDate, '%Y-%m-%d'))", "<=", $lastDay);
		$AC->addJoinV3("Serie", "SerieID", "=", "SerieID");
		#$AC->addAssocV3("type", "=", "default");
		#$AC->addAssocV3("AuftragID", "=", "-1");
		#$AC->addAssocV3("geb", "!=", "0");
		
		while($t = $AC->getNextEntry())
			$K->addEvent(self::getCalendarDetails("mSerieGUI", $t->getID(), $t));
		
		return $K;
	}
}
?>