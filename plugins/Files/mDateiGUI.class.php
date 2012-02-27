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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class mDateiGUI extends anyC implements iGUIHTMLMP2 {
	public $classID;
	public $className;
	public $viewOnly = false;
	
	public $onAddClass;
	#public $onReloadFrame;
	#public $onReloadClass;
	#public $onReloadID;
	public $onDeleteFunction;
	
	public function getHTML($id, $page){
		$this->addAssocV3("DateiClassID","=",$this->classID);
		$this->addAssocV3("DateiClass","=",$this->className);
		$this->lCV3($id);
		
		$gui = new HTMLGUI();
		$gui->setName("Dateien");
		$gui->setAttributes($this->collector);
		$gui->setCollectionOf($this->collectionOf);
		
		$gui->setShowAttributes(array("DateiName"));
		
		$gui->setParser("DateiName","mDateiGUI::nameParser",array("\$DateiPath","\$DateiIsDir"));
		
		$gui->setIsDisplayMode(true);
		if(!$this->viewOnly) $gui->setDeleteInDisplayMode(true);
		$t = new HTMLTable(1);
		
		if($this->classID != null AND !$this->viewOnly){
			$B = new Button("Datei\nhinzufügen","computer");
			$B->select(true, "mFile", ($this->onAddClass != null ? $this->onAddClass : "'+lastLoadedLeftPlugin+'"), $this->classID, "addFile");
			$B->customSelect("contentRight", $this->classID, "mFile", "$this->onAddClass.addFile");
			$t->addRow($B);
			#$t->addRow(array("<input onclick=\"loadFrameV2('contentRight','mFile','mFileGUI;selectionMode:multiSelection,".($this->onAddClass != null ? $this->onAddClass : "'+lastLoadedLeftPlugin+'").",$this->classID,addFile,'+lastLoadedRightPlugin+',".($this->onReloadFrame != null ? $this->onReloadFrame : "contentLeft").",".($this->onReloadClass != null ? $this->onReloadClass : "'+lastLoadedLeftPlugin+'").",".($this->onReloadID != null ? $this->onReloadID : $this->classID)."');\" type=\"button\" class=\"backgroundColor3 bigButton\" style=\"background-image:url(./images/navi/computer.png);\" value=\"Datei\nhinzufügen\" />"));
		}

		$gui->setJSEvent("onDelete",$this->onDeleteFunction == null ? "function() { reloadLeftFrame(); }" : $this->onDeleteFunction);
			
		try {
			if($this->viewOnly AND $this->numLoaded() == 0) return "";
			return $t->getHTML().$gui->getBrowserHTML($id);
		} catch (Exception $e){ }
	}

	public function deleteUs(){
		while($t = $this->getNextEntry())
			$t->deleteMe();
	}
	
	public static function nameParser($w, $l, $p){
		$p = HTMLGUI::getArrayFromParametersString($p);
		$dl = "<img src=\"./plugins/Files/download.png\" class=\"mouseoverFade\" title=\"Datei herunterladen\" onclick=\"windowWithRme('File','$p[0]','download','');\" style=\"float:right;\" />";
		if($p[1] == "0") return "$dl<img src=\"./images/i2/details.png\" class=\"mouseoverFade\" onclick=\"windowWithRme('File', '$p[0]', 'previewWindow', '');\" style=\"float:left;margin-right:5px;\" />".$w;
		else return "<img src=\"./images/i2/folder.png\" style=\"float:left;margin-right:5px;\" />$w";
	}
}
?>
