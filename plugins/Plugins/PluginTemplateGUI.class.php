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
class PluginTemplateGUI extends PluginTemplate implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();

		$P = new Plugin(-1);
		$P = $P->newAttributes();


		$variables = PMReflector::getAttributesArray($P);
		$variables[] = "PluginTablesXML";
		$iVariables = "";
		unset($variables[array_search("PluginTables", $variables)]);
		$variables = array_merge(array("PluginTable"), $variables);

		foreach ($variables as $k => $v) {
			if(strpos($v, "ID") !== false) continue;
			$iVariables .= "<span onclick=\"Util.insert_text('{".$v."}',false,$('editorTextarea'));\" style=\"cursor:pointer;\">{".$v."}</span><br />";
		}

		$html = "
			<script type=\"text/javascript\">new Draggable('TBVarsContainer',{handle:'TBVarsHandler', zindex: 2000});</script>
			<div
				style=\"position:absolute;z-index:2000;margin-left:450px;width:200px;border-width:1px;border-style:solid;\"
				class=\"backgroundColor0 borderColor1\"
				id=\"TBVarsContainer\"
			>
			<div class=\"cMHeader backgroundColor1\" id=\"TBVarsHandler\">Variablen:</div>
			<div>
				<p><small>Sie können folgende Variablen in Ihrem Text verwenden (bitte beachen Sie Groß- und Kleinschreibung):</small></p>
				<p id=\"TBVars\">$iVariables</p>
			</div>
			</div>";

		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("PluginTemplate");

		$gui->setLabel("PluginTemplateType", "Typ");
		$gui->setLabel("PluginTemplateName", "Name");
		$gui->setLabel("PluginTemplateText", "Text");

		$gui->setType("PluginTemplateType", "select");
		$gui->setType("PluginTemplateText", "TextEditor");
		$gui->setOptions("PluginTemplateType", array(0,1,2,3), array("Plugin-Beschreibung","PersistentObject","ObjectGUI","CollectionGUI"));

		$gui->setStandardSaveButton($this, "mPluginTemplate");
	
		return $html.$gui->getEditHTML();
	}
}
?>