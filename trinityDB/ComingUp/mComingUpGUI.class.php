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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mComingUpGUI extends UnpersistentClass implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$D = new Datum();
		while(date("w", $D->time()) != 1)
			$D->subDay();

		$DEnd = clone $D;
		$DEnd->addMonth();

		$ac = new anyC();
		$ac->setCollectionOf("Folge");
		$ac->addJoinV3("Serie", "SerieID", "=", "SerieID");
		$ac->setFieldsV3(array("t2.name", "t1.name AS EName", "UNIX_TIMESTAMP(airDate) AS AD", "airDate"));
		$ac->addAssocV3("UNIX_TIMESTAMP(airDate)", ">=", $D->time());
		$ac->addAssocV3("UNIX_TIMESTAMP(airDate)", "<=", $DEnd->time());
		$ac->addOrderV3("UNIX_TIMESTAMP(airDate)", "ASC");
		$ac->addOrderV3("t2.name");

		$episodes = array();
		while($E = $ac->getNextEntry()){
			if(!isset($episodes[$E->A("airDate")])) $episodes[$E->A("airDate")] = "";

			$episodes[$E->A("airDate")] .= "<a style=\"text-decoration:none;".($E->A("AD") < time() - 3600 * 24 ? "color:grey;" : "")."\" href=\"javascript:contentManager.editInPopup('Folge', ".$E->getID().", 'Display episode details');\">".$E->A("name")."</a><br />";
		}



		$T = new HTMLTable(7, "Coming up...");
		for($i = 0; $i < 7; $i++)
			$T->setColWidth ($i + 1, round(700 / 7));
		$T->setTableStyle("width:700px;margin-left:10px;");
		$T->addHeaderRow(array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"));

		$collection = array();
		while($D->time() < $DEnd->time()){

			if(count($collection) == 7){
				$T->addRow($collection);
				for($i = 1; $i <= 7; $i++)
					$T->addCellStyle ($i, "vertical-align:top;");

				$T->addRowStyle("height:80px;");

				$collection = array();
			}

			$content = "<span style=\"font-size:16px;float:right;font-weight:bold;".($D->time() < time() - 3600 * 24 ? "color:grey;" : "")."\">".date("d", $D->time())."</span>";

			if(isset($episodes[date("Y-m-d", $D->time())]))
				$content .= "<div style=\"clear:both;\"><small>".$episodes[date("Y-m-d", $D->time())]."</small></div>";

			$collection[] = $content;

			$D->addDay();
		}

		if(count($collection) > 0){
			$T->addRow($collection);
				$T->addRowStyle("height:80px;");
			
			for($i = 1; $i <= 7; $i++)
				$T->addCellStyle ($i, "vertical-align:top;");
		}

		return $T;
	}


}
?>