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
		addClassPath(Util::getRootPath()."trinityDB/JD");
	}
	
	function getScript(){
		return "var g = {
	sameHeight: function(element){
		g.doSameHeight(element);
		
		$(window).resize(function(){ g.doSameHeight(element); });
	},
	
	doSameHeight: function(element){
		var maxHeight = 0;
		$(element).css('height', '');
		$(element).each(function(k, v){
			if($(v).outerHeight() > maxHeight)
				maxHeight = $(v).outerHeight();
		});
		$(element).css('height', maxHeight);
	}
	
};";
	}
	
	function getLabel(){
		return "Serien";
	}
	
	function getTitle(){
		return $this->getLabel();
	}
	
	function getCMSHTML() {
		$mode = $_GET["mode"];
		return $this->$mode();
	}
	
	function browser(){
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
			$ACF->addOrderV3("airDate", "ASC");
			$ACF->setLimitV3(1);
			#$ACF->setFieldsV3(array("TIMESTAMP(airDate) AS ts"));
			$next = $ACF->getNextEntry();
			
			//$imgData = DBImageGUI::resizeMax(DBImageGUI::getData($S->A("cover")), 150, 221);
			$html .= "<div style=\"vertical-align:top;display:inline-block;width:33%;margin-bottom:2%;\">
				<img style=\"float:left;margin-right:20px;width:150px;height:220px;margin-bottom:5px;\" src=\"./index.php?D=trinityDB/Serien&M=getCover&P0=".$S->A("SerieID")."\" />
				<div>
					<h2 style=\"margin-top:0px;padding-top:0px;\">".$S->A("name")."</h2>
					".($next != null ? "<p>Nächste Folge:<br />".Util::CLDateParserL(strtotime($next->A("airDate")))."</p>" : "")."
					
				</div>
			</div>";
		}
		
		return $html;
	}
	
	function newEpisodes(){
		$html = "<h1>Demnächst</h1>";
		
		$AC = anyC::get("Folge");
		$AC->addAssocV3("UNIX_TIMESTAMP(STR_TO_DATE(airDate, '%Y-%m-%d 18:00'))", ">=", time());
		$AC->addAssocV3("UNIX_TIMESTAMP(STR_TO_DATE(airDate, '%Y-%m-%d 18:00'))", "<=", (time() + 3600 * 24 * 7));
		$AC->addAssocV3("RSSFilterID", ">", "0");
		$AC->addJoinV3("Serie", "SerieID", "=", "SerieID");
		$AC->addOrderV3("UNIX_TIMESTAMP(STR_TO_DATE(airDate, '%Y-%m-%d 18:00'))", "ASC");
		
		$i = 0;
		$lastAD = null;
		while($S = $AC->getNextEntry()){
			if($lastAD != $S->A("airDate")){
				if($lastAD !== null)
					$html .= "</div>";
				
				$date = strtotime($S->A("airDate"));
				
				$html .= "<div class=\"comingUp\" style=\"box-sizing:border-box;".($i % 2 == 0 ? "background-color: #F2F2F2;" : "")."padding:10px;display:inline-block;width:calc(100% / 7);vertical-align:top;\"><h2 style=\"margin-top:0;padding-top:0;\">".  mb_substr(Util::CLWeekdayName(date("w", $date)), 0, 2).", ".Util::CLDateParser($date)."</h2>";
				
				$i++;
			}
			$html .= $S->A("name")." <span style=\"color:grey;\">S".($S->A("season") < 10 ? "0" : "").$S->A("season")."E".($S->A("episode") < 10 ? "0" : "").$S->A("episode")."</span><br>";
			
			$lastAD = $S->A("airDate");
		}
		$html .= "</div>".OnEvent::script("g.sameHeight('.comingUp');");
		
		$html .= "<h1>Neue Folgen</h1>";
		
		$AC = anyC::get("JDownload");
		$AC->addAssocV3("JDownloadDate", ">", time() - 3600 * 24 * 7);
		$AC->addOrderV3("JDownloadDate", "DESC");
		$AC->addAssocV3("JDownloadSerieID", "!=", "0");
		$AC->addJoinV3("Serie", "JDownloadSerieID", "=", "SerieID");
		while($D = $AC->getNextEntry()){
			if($D->A("JDownloadSerieID") != "0" AND $D->A("cover") != "" AND trim($D->A("coverThumb")) == ""){
				$S = new Serie($D->A("JDownloadSerieID"));
				$S->changeA("coverThumb", DBImageGUI::stringifyDataS("image/png", DBImageGUI::getData($S->A("cover")), 150, 220));
				$S->saveMe(true, false);
				
				$D->changeA("coverThumb", $S->A("coverThumb"));
			}
			preg_match("/S([0-9]+)E([0-9]+)/", $D->A("JDownloadRenameto"), $matches);
			
			$ACF = anyC::get("Folge", "SerieID", $D->A("SerieID"));
			$ACF->addAssocV3("season", "=", $matches[1]);
			$ACF->addAssocV3("episode", "=", $matches[2]);
			$ACF->setLimitV3(1);
			$F = $ACF->getNextEntry();
			
			$B = new Button("Abspielen", "play", "touch");
			$B->onclick("parent.UPnP.search('".Util::makeFilename(str_replace(" ", ".", $D->A("JDownloadRenameto")))."');");
			
			$html .= "
			<div style=\"display:inline-block;width:33%;margin-bottom:2%;vertical-align:top;\">
				<div style=\"margin-right:20px;float:left;width:150px;\">
					<img style=\"width:150px;height:220px;margin-bottom:5px;\" src=\"./index.php?D=trinityDB/Serien&M=getCover&P0=".$D->A("SerieID")."\" />
						$B
				</div>
				<h2 style=\"margin-top:0px;padding-top:0px;\">".$D->A("JDownloadRenameto")."</h2>
				<p style=\"color:grey;\">".($F != null ? $F->A("description") : "Keine Beschreibung")." <small>".Util::CLDateParser($D->A("JDownloadDate"))."</small></p>
			</div>";

		}
		return $html;
	}
	
	
	public function getCover($data){
		$I = new DBImageGUI();
		
		$I->getHTML("Serie:::".$data["P0"].":::coverThumb");
	}
}

?>