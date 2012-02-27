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

class mNewSeriesGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){

		$bps = $this->getMyBPSData();


		$LangTab = new HTMLTable(1, "Language selection:");
		$LangTab->addRow("German");
		$LangTab->addRowEvent("click", "contentManager.loadFrame('contentRight', 'mNewSeries', -1, 0, 'mNewSeriesGUI;lang:de');");
		$LangTab->addRowStyle("cursor:pointer;");
		if($bps != -1 AND $bps["lang"] == "de") $LangTab->addRowClass("backgroundColor1");

		$LangTab->addRow("English");
		$LangTab->addRowEvent("click", "contentManager.loadFrame('contentRight', 'mNewSeries', -1, 0, 'mNewSeriesGUI;lang:en');");
		$LangTab->addRowStyle("cursor:pointer;");
		if($bps != -1 AND $bps["lang"] == "en") $LangTab->addRowClass("backgroundColor1");

		if($bps == -1) return $LangTab;
		
		$T = new HTMLTable(1);
		$T->setTableStyle("width:370px;float:left;");

		$FeedSerien = array();

		$i = 0;
		$Series = array();
		$RSS = new mRSSFilterGUI();
		while($R = $RSS->getNextEntry()){
			$new = $R->filterNew();

			foreach ($new as $S => $v) {
				if($v->language != $bps["lang"]) continue;

				$Series[$v->name] = "rmeP('NewSeries', '-1', 'showInfo', ['$v->name', '$v->language'], 'if(checkResponse(transport)) { $(\'Row$i\').className = \'backgroundColor1\'; $(\'contentLeft\').update(transport.responseText); }');";

			}
		}

		asort($Series);

		foreach($Series AS $name => $action){

				$T->addRow(array($name));
				$T->addRowStyle("cursor:pointer;");
				$T->addRowEvent("click", $action);
				$T->setRowID("Row$i");
				$i++;
		}

		return $LangTab."<div style=\"margin-left:0px;height:500px;overflow:auto;\">".$T."</div>";

	}

}
?>