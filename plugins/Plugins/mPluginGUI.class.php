<?php
/**
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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mPluginGUI extends anyC implements iGUIHTMLMP2 {
	public function getHTML($id, $page){
		$this->addOrderV3("PluginApplication");
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->name("Plugin");

		$gui->attributes(array("PluginApplication","PluginMenuName"));

		$B = new Button("Plugin-\nTemplates","template");
		$B->onclick("contentManager.loadFrame('contentRight','mPluginTemplate');");

		$gui->parser("PluginMenuName", "mPluginGUI::nameParser");

		$tab = new HTMLTable(1);
		$tab->addRow($B);

		try {
			return ($id == -1 ? $tab : "").$gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}

	public static function nameParser($w, $E){
		if($w == "") return $E->A("PluginTables");
		return $w;
	}
}
?>