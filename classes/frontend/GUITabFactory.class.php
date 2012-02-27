<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class GUITabFactory {

	private $className = null;
	private $tabBar = null;

	function  __construct($className) {
		$this->className = $className;
	}

	public function buildTabBar(){
		$widths = Aspect::joinPoint("changeWidths", $this, __METHOD__);
		if($widths == null) $widths = array(700);

		$cTab = mUserdata::getUDValueS("TabBarLastTab$this->className", "none");

		if(count($this->tabBar) == 0) return "";

		$bar = "";
		if(count($this->tabBar) > 0) $bar = "
			<div style=\"width:$widths[0]px;margin-top:30px;padding-left:10px;border-top-width:1px;border-top-style:solid;\" class=\"borderColor1\">";

		foreach($this->tabBar AS $value){
			$B = new Button($value[1], $value[2]);
			$B->type("icon");
			$B->style("float:left;margin-right:5px;");

			

			if(!is_object($value[0])){
				$id = "null";
				$onClick = $value[0];
			} else {
				$id = get_class($value[0]);
				$onClick = "Interface.TabBarActivate(this, '$id', '$this->className');";
			}
			
			$bar .= "
				<div
					id=\"tab_$id\"
					style=\"float:left;width:110px;padding:3px;cursor:pointer;-moz-user-select:none;\"
					onmouseover=\"if(this.className != 'navBackgroundColor') this.className = 'backgroundColor2';\"
					onmouseout=\"if(this.className != 'navBackgroundColor') this.className = '';\"
					onclick=\"$onClick\">
					$B
					<p style=\"margin-top:3px;padding:0px;padding-top:5px;\">$value[1]</p>
				</div>";

			if($id == $cTab)
				$bar .= "<script type=\"text/javascript\">Interface.TabBarActivate($('tab_$id'), '$id');</script>";
		}


		$bar .= "
			</div>
			<div style=\"clear:both;\"></div>";

		foreach($this->tabBar AS $value){
			if(!is_object($value[0])) continue;
			$bar .= "<div id=\"".get_class($value[0])."\" style=\"display:none;\">".$value[0]->getHTML(-1, 0)."</div>";
		}

		return $bar;
	}

	public function addTab($element, $label, $icon){
		if($this->tabBar == null) $this->tabBar = array();

		$this->tabBar[] = array($element, $label, $icon);
	}

	public function addCustomTab($onclick, $label, $icon){
		if($this->tabBar == null) $this->tabBar = array();

		$this->tabBar[] = array($onclick, $label, $icon);
	}
}
?>
