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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mIncomingPrettifyGUI extends anyC implements iGUIHTMLMP2, icontextMenu {

	public function getHTML($id, $page){
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);

		$gui->name("IncomingPrettify");
		$gui->displayMode("BrowserLeft");
		
		#$gui->attributes(array());

		#$B = $gui->addSideButton("add new\nrule", "new");
		#$B->popup("editPopupIncomingPrettifyGUI", "Prettify rule", "IncomingPrettify", -1, "getEditPopup", "-1");
		#$B->editInPopup();

		$gui->activateFeature("editInPopup", $this);

		$gui->attributes(array("IncomingPrettifyFind", "IncomingPrettifyIsActive"));

		$gui->parser("IncomingPrettifyIsActive", "Util::catchParser");
		$gui->parser("IncomingPrettifyFind", "mIncomingPrettifyGUI::findParser");

		$gui->colWidth("IncomingPrettifyIsActive", 20);

		$B = $gui->addSideButton("run\nPrettifyer", "navigation");
		$B->popup("prettifyer", "Prettifyer", "mIncomingPrettify", "-1", "runRules");

		$B = $gui->addSideButton("Options", "system");
		$B->contextMenu("mIncomingPrettify", "options", "Options");

		return $gui->getBrowserHTML($id);
	}

	public function runRules(){
		$AC = new anyC();
		$AC->setCollectionOf("IncomingPrettify");
		$AC->addAssocV3("IncomingPrettifyIsActive", "=", "1");

		$I = new mIncoming();
		$files = $I->getNewFiles();
		echo "<div style=\"max-height:400px;overflow:auto;font-size:10px;padding:5px;\">";

		$run = mUserdata::getUDValueS("trinityDBPrettifyExecute", "0") == "1";

		if(!$run)
			echo "<p style=\"color:red;margin-bottom:10px;\">The following operations are NOT executed, this is only a preview!<br />To execute the operations, you'll have to enable it in the options.</p>";

		foreach ($files AS $path){
			$newName = basename($path);
			if(strpos($newName, ".part") == strlen($newName) - 5) continue;
			if(strpos($newName, ".mkv") != strlen($newName) - 4) continue;
			
			while($P = $AC->getNextEntry()){
				$newName = preg_replace("/".str_replace(".", "\.", $P->A("IncomingPrettifyFind"))."/e".($P->A("IncomingPrettifyCaseSensitive") == "1" ? "" : "i"), str_replace(array("//", "."), array("\\", "\."), $P->A("IncomingPrettifyReplace")), $newName);

			}

			$AC->resetPointer();
			if($newName != basename($path)){
				
				$color = "";
				if($run) {
					$renamed = str_replace(basename($path), $newName, $path);

					if(file_exists($renamed))
						$color = "color:red;";
					else
						if(rename($path, $renamed))
							$color = "color:green;";
						else
							$color = "color:red;";
				}
				
				echo "<span style=\"$color\">".basename($path)." -><br />".basename($newName)."</span><br /><br />";
			}
		}
		echo "</div>";
	}

	public static function findParser($w, $E){
		return "<span style=\"font-size:10px;\">".$w."<br />=&gt; ".$E->A("IncomingPrettifyReplace")."</span>";
	}

	public function getContextMenuHTML($identifier){
		$F = new HTMLForm("prettifyerForm", array("execute"));

		$F->setType("execute", "checkbox");
		$F->setLabel("execute", "Execute?");
		$F->setValue("execute", mUserdata::getUDValueS("trinityDBPrettifyExecute", "0"));

		$F->setSaveRMEPCR("save", "./images/i2/save.gif", "mIncomingPrettify", "-1", "saveContextMenu", "function() { contextMenu.stop(); }");

		echo $F;
	}

	public function saveContextMenu($identifier, $key = ""){
		mUserdata::setUserdataS("trinityDBPrettifyExecute", $identifier);
	}
}
?>