<?php
/**
 *  This file is part of plugins.

 *  plugins is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  plugins is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class Merlin extends PersistentObject {
	public function getSettings(){
		$myPlugins = explode(";:;", $this->A("MerlinPlugins"));
		$allPlugins = $myPlugins;
		$allApps = explode(";:;", $this->A("MerlinApps"));
		
		$selections = $this->A("MerlinSelections");
		$specifics = $this->A("MerlinSpecificFiles");

		$parents = explode(";:;", $this->A("MerlinsParents"));
		if(count($parents) > 0)
			foreach($parents AS $parentID){
				if($parentID == "") continue;
				$M = new Merlin($parentID);

				$ParentData = $M->getSettings();

				$allPlugins = array_merge($allPlugins, $ParentData["allPlugins"]);
				$allApps = array_merge($allApps, $ParentData["applications"]);

				$selections .= "\n".$ParentData["selections"];
				$specifics .= "\n".$ParentData["specifics"];
			}

		$allPlugins = array_unique($allPlugins);
		$allApps = array_unique($allApps);
		
		$selections = implode("\n", array_unique(explode("\n", $selections)));
		$specifics = implode("\n", array_unique(explode("\n", $specifics)));
		
		sort($allPlugins);
		return array("allPlugins" => $allPlugins, "myPlugins" => $myPlugins, "selections" => trim($selections), "specifics" => trim($specifics), "applications" => $allApps);
	}
}
?>