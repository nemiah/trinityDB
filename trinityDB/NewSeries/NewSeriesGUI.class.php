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
 *  2007, 2008, 2009, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class NewSeriesGUI extends NewSeries implements iGUIHTML2 {
	function  __construct($ID) {

		if(strpos($ID, "loadBanner") !== false){
			$image = "http://".BPS::getProperty("NewSeriesGUI", "loadBanner");

			$content = file_get_contents($image);

			$this->A = new stdClass();
			$this->A->Banner = "image/jpg:::".strlen($content).":::".base64_encode(addslashes($content));

			#echo $this->A->Banner;
			BPS::unsetProperty("NewSeriesGUI", "loadBanner");
			return;
		}

		parent::__construct($ID);
	}

	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("NewSeries");

		$gui->setStandardSaveButton($this);
	
		return $gui->getEditHTML();
	}

	function showInfo($name, $language){
		$Ad = new thetvdbcomAdapter();

		$info = $Ad->getInfo($name, $language);

		if($info === null) die("<p>Sorry, there is no information available for '$name($language)'</p>");

		BPS::setProperty("NewSeriesGUI", "loadBanner", str_replace("http://", "", $info->Banner));

		$B = new Button("save\nseries", "./trinityDB/Serien/Serien.png");
		$B->rmePCR("Serie", "-1", "newSeriesFromID", array("'$info->Name'", "'$language'", "'$info->SeriesID'"), "contentManager.loadFrame('contentLeft', 'Serie', transport.responseText);");
		#$B->style("float:right;");

		echo "
			<div style=\"margin-left:10px;width:765px;border-right-width:1px;border-right-style:solid;\" class=\"borderColor1\">
				$B
				<div style=\"height:20px;\"></div>
				<img src=\"".DBImageGUI::imageLink("NewSeriesGUI", "loadBanner".$info->SeriesID, "Banner")."\" style=\"width:758px;height:140px;\" /><br />
				<p style=\"-moz-column-count: 3;-moz-column-gap: 1em;-moz-column-rule: 1px solid black;-webkit-column-count: 3;-webkit-column-gap: 1em;-webkit-column-rule: 1px solid black;\">".$info->Overview."</p>
			</div>";
	}
}
?>