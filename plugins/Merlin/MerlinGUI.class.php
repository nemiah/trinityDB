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
class MerlinGUI extends Merlin implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();

		$Settings = $this->getSettings();

		$gui = new HTMLGUI2();
		$gui->setObject($this);
		$gui->setName("Merlin");

		$gui->setLabel("MerlinDirName", "Verzeichnis");
		$gui->setLabel("MerlinHasChildren", "hat Kinder?");
		$gui->setLabel("MerlinsParents", "Eltern");
		$gui->setLabel("MerlinApps", "Anwendungen");
		$gui->setLabel("MerlinPlugins", "Plugins");
		$gui->setLabel("MerlinSelections", "Selektoren");
		$gui->setLabel("MerlinMakeZip", "zippen?");
		$gui->setLabel("MerlinDeployDemoBox", "DemoBox?");
		$gui->setLabel("MerlinSpecificFiles", "specific-Dateien");
		$gui->setLabel("MerlinDeployToSubdir", "Subdir");

		$IApps = new HTMLInput("MerlinApps", "select-multiple", $this->A("MerlinApps"), $_SESSION["applications"]->getApplicationsList());
		$IApps->style("height:156px;");


		$parents = array();
		$C = new anyC();
		$C->setCollectionOf("Merlin");
		$C->addAssocV3("MerlinHasChildren", "=", "1");
		while($P = $C->getNextEntry())
			$parents[$P->getID()] = basename($P->A("MerlinDirName"));

		$IParents = new HTMLInput("MerlinsParents", "select-multiple", $this->A("MerlinsParents"), $parents);
		$IParents->style("height:100px;");

		$gui->setType("MerlinHasChildren", "checkbox");
		$gui->setType("MerlinMakeZip", "checkbox");
		$gui->setType("MerlinDeployDemoBox", "checkbox");
		$gui->setType("MerlinHasChildren", "checkbox");
		$gui->setType("MerlinPlugins", "hidden");
		$gui->setType("MerlinApps", "custom");
		$gui->setType("MerlinsParents", "custom");
		$gui->setType("MerlinSelections", "textarea");
		$gui->setType("MerlinSpecificFiles", "textarea");

		$gui->setOptions("MerlinApps", $IApps);
		$gui->setOptions("MerlinsParents", $IParents);

		$gui->setInputStyle("MerlinSpecificFiles", "font-size:10px;height:200px;");

		#$gui->selectWithCollection("MerlinsParent", $C, "MerlinDirName", "kein Elter");

		$gui->setStandardSaveButton($this);



		#print_r($Settings);

		/*$selectedPlugins = explode(";:;", $this->A("MerlinPlugins"));
		$parentPlugins = array();
		$selections = "";

		$parents = explode(";:;", $this->A("MerlinsParents"));
		foreach($parents AS $parentID){
			$M = new Merlin($parentID);
			$parentPlugins = array_merge($parentPlugins, explode(";:;", $M->A("MerlinPlugins")));
			$selectedPlugins = array_merge($selectedPlugins, explode(";:;", $M->A("MerlinPlugins")));
			$selections .= "\n".$M->A("MerlinSelections");
		}
*/
		$selectedPlugins = $Settings["allPlugins"];#$Settings["myPlugins"];
		$parentPlugins = array_diff($Settings["allPlugins"], $Settings["myPlugins"]);
//		echo "<pre style=\"font-size:10px;\">";
//		print_r($selectedPlugins);
//		echo "\n\nall:\n";
//		print_r($allPlugins);
//		echo "\n\nparent:\n";
//		print_r($parentPlugins);
//		echo "</pre>";
		
		$gui->setFieldDescription("MerlinSelections", "<pre>".trim(str_replace($this->A("MerlinSelections"),"",$Settings["selections"]))."</pre>");
		$gui->setFieldDescription("MerlinSpecificFiles", "<pre>".trim(str_replace($this->A("MerlinSpecificFiles"),"",$Settings["specifics"]))."</pre>");

		#$selectedPlugins = array_unique($selectedPlugins);
		#$parentPlugins = array_unique($parentPlugins);


		$apps = $_SESSION["applications"]->getApplicationsList();
		$apps["plugins"] = "plugins";

		$tables = "";
		foreach($apps AS $app){
			$AP = new AppPlugins();
			$AP->scanPlugins($app);
			$plugins = $AP->getAllPlugins();

			$T = new HTMLTable(2);
			$T->addColStyle(2, "width:20px;");

			$withPlugin = false;

			foreach($plugins AS $Name => $Plugin){
				$value = "0";
				if(in_array("{$app}_$Plugin", $selectedPlugins)){
					$value = "1";
					$withPlugin = true;
				}

				$CB = new HTMLInput("{$app}_$Plugin", "checkbox", $value);
				$CB->onchange("Merlin.checkBox();");
				if(in_array("{$app}_$Plugin", $parentPlugins)) $CB->isDisabled(true);

				$BLink = "";
				if(strpos($AP->getFolderOfPlugin($Plugin), "../") !== false){
					$BLink = new Button("Link", "./images/i2/link.png");
					$BLink->type("icon");
					$BLink->style("float:right;");
				}

				$T->addRow(array($BLink.$Plugin, $CB));
			}

			$tables .= "<div onclick=\"if($('{$app}Container').style.display == 'none') new Effect.BlindDown('{$app}Container', {duration: 0.5}); else new Effect.BlindUp('{$app}Container', {duration: 0.5});\" style=\"padding:5px;margin-top:10px;cursor:pointer;\" class=\"backgroundColor1\">$app</div><div style=\"".($withPlugin ? "" : "display:none;")."\" id=\"{$app}Container\">".$T."</div>";
		}

		$TD = new HTMLTable(1);
		$B = new Button("jetzt\nveröffentlichen", "./plugins/Merlin/deployNow32.png");
		$B->onclick("if(!confirm('Jetzt deployen?')) return; $('contentLeft').update('<p>Bitte warten...</p>'); ");
		$B->rmePCR("Merlin", $this->ID, "deploy", "true", "$('contentLeft').update(transport.responseText);");
		$TD->addRow($B);

		return "<div style=\"margin-top:-9px;float:right;margin-right:-420px;width:420px;\"><form id=\"FormPlugins\">".$tables."</form></div>".$TD.$gui->getEditHTML();
	}

	public function deploy($debug = "false"){
		$pc = new phynxCore();

		$pc->toSubdir($this->A("MerlinDeployToSubdir") != "" ? $this->A("MerlinDeployToSubdir") : false);
		$pc->deployTo($this->A("MerlinDirName"));
		$pc->debug($debug == "true");
		$pc->applications(explode(";:;", $this->A("MerlinApps")));
		$pc->plugins(explode(";:;", $this->A("MerlinPlugins")));
		$pc->parents(explode(";:;", $this->A("MerlinsParents")));
		$pc->selections(explode("\n", $this->A("MerlinSelections")));
		$pc->zip($this->A("MerlinMakeZip") == "1");
		$pc->demoBox($this->A("MerlinDeployDemoBox") == "1");
		$pc->specifics(explode("\n", $this->A("MerlinSpecificFiles")));
		$zipLoc = $pc->make();

		return $zipLoc;
	}
}
?>