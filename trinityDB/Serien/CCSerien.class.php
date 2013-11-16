<?php
/**
 *  This file is part of IPKartei.

 *  IPKartei is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  IPKartei is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses></http:>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class CCSerien implements iCustomContent {
	function __construct() {
		addClassPath(dirname(__FILE__));
		addClassPath(Util::getRootPath()."open3A/Adressen");
		addClassPath(Util::getRootPath()."open3A/Kunden");
		addClassPath(Util::getRootPath()."open3A/Auftraege");
		addClassPath(Util::getRootPath()."open3A/Stammdaten");
		addClassPath(Util::getRootPath()."open3A/Textbausteine");
		addClassPath(Util::getRootPath()."open3A/Brief");
		/*
		if(!isset($_SESSION["BPS"]))
			$_SESSION["BPS"] = new BackgroundPluginState();*/
	}
	
	function getLabel(){
		return "Serien";
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getCMSHTML() {
		$html = "<h1>Neue Folgen</h1>";
		
		$AC = anyC::get("Folge");
		$AC->addAssocV3("TIMESTAMP(airDate)", ">=", date("Y-m-d", time() - 3600 * 24 * 7));
		$AC->addAssocV3("TIMESTAMP(airDate)", "<", date("Y-m-d"));
		$AC->addOrderV3("airDate", "DESC");
		
		while($F = $AC->getNextEntry()){
			$S = new Serie($F->A("SerieID"));
			
			$html .= "<div style=\"display:inline-block;width:33%;margin-bottom:2%;\">
				<img style=\"float:left;margin-right:20px;width:150px;height:220px;\" src=\"data:image/png;base64,".base64_encode(DBImageGUI::getData($S->A("coverThumb")))."\" />
				<div>
					<h2 style=\"margin-top:0px;padding-top:0px;\">".$S->A("name")."</h2>
					<p>Letzte Folge:<br />".Util::CLDateParserL(strtotime($F->A("airDate")))."</p>
					
				</div>
			</div>";
		}
		
		$html .= "<h1>Serien-Browser</h1>";
		
		$AC = anyC::get("Serie");
		$AC->addAssocV3("status", "=", "Continuing");
		$AC->addOrderV3("status");
		$AC->addOrderV3("name");
		#$AC->setLimitV3(10);
		while($S = $AC->getNextEntry()){
			if($S->A("cover") != "" AND trim($S->A("coverThumb")) == ""){
				$S->changeA("coverThumb", DBImageGUI::stringifyDataS("image/png", DBImageGUI::getData($S->A("cover")), 150, 220));
				$S->saveMe(true, false);
			}
			
			#$ACF = anyC::get("Folge", "SerieID", $S->getID());
			#$ACF->setFieldsV3(array("COUNT(*) AS anzahl"));
			#$Folgen = $ACF->getNextEntry();
			
			$ACF = anyC::get("Folge", "SerieID", $S->getID());
			$ACF->addAssocV3("TIMESTAMP(airDate)", ">=", date("Y-m-d"));
			$ACF->addOrderV3("airDate", "DESC");
			$ACF->setLimitV3(1);
			#$ACF->setFieldsV3(array("TIMESTAMP(airDate) AS ts"));
			$next = $ACF->getNextEntry();
			
			//$imgData = DBImageGUI::resizeMax(DBImageGUI::getData($S->A("cover")), 150, 221);
			$html .= "<div style=\"display:inline-block;width:33%;margin-bottom:2%;\">
				<img style=\"float:left;margin-right:20px;width:150px;height:220px;\" src=\"data:image/png;base64,".base64_encode(DBImageGUI::getData($S->A("coverThumb")))."\" />
				<div>
					<h2 style=\"margin-top:0px;padding-top:0px;\">".$S->A("name")."</h2>
					".($next != null ? "<p>Nächste Folge:<br />".Util::CLDateParserL(strtotime($next->A("airDate")))."</p>" : "")."
					
				</div>
			</div>";
		}
		
		return $html;
	}
	
}

?>