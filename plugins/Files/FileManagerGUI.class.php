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
 *  2007 - 2017, Furtmeier Hard- und Software - Support@Furtmeier.IT
 */
class FileManagerGUI extends mFileGUI implements iGUIHTMLMP2 {

	public function setOwner($className, $classID){
		BPS::setProperty("FileManagerGUI", "root", $className.($classID != "" ? "ID".($classID < 10 ? "0" : "").($classID < 100 ? "0" :"").($classID < 1000 ? "0" : "").$classID : ""));
	}

	/*public function getTinyMCEManager($fieldName, $type){
		$this->setOwner("MailContent", "");
		
		$B = new Button("Aktualisieren", "../images/navi/refresh.png", "icon");
		$B->onclick("document.location.reload();");
		$html = "$B$fieldName:$type";
		
		
		$pathing = $this->setPath();
		if($pathing !== true)
			return $pathing;
		
		$this->hideDirs(true);

		$gui = new HTMLGUIX();

		$gui->object($this);
		$gui->name("Datei");
		$gui->colWidth("FileIsDir", 20);

		$gui->attributes(array("FileIsDir","FileName"));

		$gui->parser("FileIsDir","mFileGUI::popupIsDirParser2");
		$gui->parser("FileName","mFileGUI::nameParser2");

		#$gui->addToEvent("onDelete", BPS::getProperty("FileManagerGUI", "reloadFunction", "contentManager.reloadFrame('contentLeft');"));

		$gui->options(true, false, false);

		#$gui->displayMode(BPS::getProperty("FileManagerGUI", "displayMode", "CRMSubframeContainer"));
		#$gui->displayMode("popup");

		$html .= $gui->getBrowserHTML(-1);
		
		echo Util::getBasicHTML($html, "");
	}*/

	public function setPath(){
		$bps = $this->getMyBPSData();
		#Aspect::registerOnetimePointCut("aboveList", "GUIFactory::getContainer", "FileManagerGUI::adviceAboveList");
		$rootDir = null;
		if($bps != -1 AND isset($bps["root"])) {
			$rootDir = preg_replace("/([a-z]*)%([0-9]*)/", "\\1:\\2", $bps["root"]);
			$rootDir = preg_replace("/^([A-Z])%/", "\\1:", $bps["root"]);
		}
		#echo $rootDir;
		if($rootDir != null){
			$T = new HTMLTable(1);

			#$rel = "specifics/$rootDir";
			#$root = Util::getRootPath().$rel;
			$root = FileStorage::getFilesDir().$rootDir;
			
			$root = preg_replace("/([a-z]*):([0-9]*)/", "\\1%\\2", $root);
			
			$_SESSION["BPS"]->setProperty("FileManagerGUI", "path", preg_replace("/^([A-Z]):/", "\\1%", $root));
			$_SESSION["BPS"]->setProperty("FileManagerGUI", "root", preg_replace("/^([A-Z]):/", "\\1%", $root));

			$F = new File($root);
			$F->loadMe();

			if($F->getA() == null){
				if(is_writable(FileStorage::getFilesDir()))
					mkdir($root);
				
				else {
					$B = new Button("", "stop");
					$B->type("icon");
					$B->style("float:left;margin-right:10px;");

					$T->addRow($B."Das Verzeichnis <code>$rel</code> existiert nicht und kann nicht automatisch angelegt werden, da keine Schreibberechtigung für <code>specifics</code> vorliegt.");
					return $T;
				}
			}
		}

		$bps = $this->getMyBPSData(); //go again

		#print_r($bps);

		if(isset($bps["path"]) AND strpos($bps["path"], $bps["root"]) === false)
			$bps["path"] = preg_replace("/^([A-Z])%/", "\\1:", $bps["root"]);#$bps["root"];
		

		if($bps != -1 AND isset($bps["path"]))
			$this->setDir(preg_replace("/^([A-Z])%/", "\\1:", $bps["path"]));
		
		return true;
	}
	
	public function getHTML($id, $page){
		#$bps = $this->getMyBPSData();
		Aspect::registerOnetimePointCut("aboveList", "GUIFactory::getContainer", "FileManagerGUI::adviceAboveList");
		/*$rootDir = null;
		if($bps != -1 AND isset($bps["root"])) $rootDir = $bps["root"];
	
		if($rootDir != null){
			$T = new HTMLTable(1);

			$rel = "specifics/$rootDir";
			$root = Util::getRootPath().$rel;

			$_SESSION["BPS"]->setProperty("FileManagerGUI", "path", $root);
			$_SESSION["BPS"]->setProperty("FileManagerGUI", "root", $root);

			$F = new File($root);
			$F->loadMe();

			if($F->getA() == null){
				if(is_writable(Util::getRootPath()."specifics"))
					mkdir($root);
				
				else{
					$B = new Button("", "stop");
					$B->type("icon");
					$B->style("float:left;margin-right:10px;");

					$T->addRow($B."Das Verzeichnis <code>$rel</code> existiert nicht und kann nicht automatisch angelegt werden, da keine Schreibberechtigung für <code>specifics</code> vorliegt.");
					return $T;
				}
			}
		}

		$bps = $this->getMyBPSData(); //go again

		#print_r($bps);

		if(isset($bps["path"]) AND strpos($bps["path"], $bps["root"]) === false)
			$bps["path"] = $bps["root"];
		
		

		if($bps != -1 AND isset($bps["path"]))
			$this->setDir($bps["path"]);*/
		
		$pathing = $this->setPath();
		if($pathing !== true)
			return $pathing;
		
		$this->hideDirs(true);

		$gui = new HTMLGUIX();

		$gui->object($this);
		$gui->name("Datei");
		#$gui->colWidth("FileIsDir", 20);
		$gui->caption("Dateien");
		$gui->attributes(array("FileName"));

		#$gui->parser("FileIsDir","mFileGUI::popupIsDirParser2");
		$gui->parser("FileName","mFileGUI::nameParser2");

		$gui->addToEvent("onDelete", BPS::getProperty("FileManagerGUI", "reloadFunction", "contentManager.reloadFrame('contentLeft');"));

		$gui->options(true, false, false);

		$gui->displayMode(BPS::getProperty("FileManagerGUI", "displayMode", "CRMSubframeContainer"));
		#$gui->displayMode("popup");

		return $gui->getBrowserHTML(-1);
	}

	public static function adviceAboveList(){
		$bps = BPS::getAllProperties("FileManagerGUI");
		#
		$bps["path"] = str_replace('\\', "/", preg_replace("/^([A-Z]):/", "\\1%", $bps["path"]));
		#print_r($bps);
		$mF = new mFileGUI();
		return $mF->getUploadForm($bps["path"], BPS::getProperty("FileManagerGUI", "reloadFunction", "contentManager.reloadFrame('contentLeft');")/*"contentManager.reloadFrame('contentLeft');"*/, false, "width:100%;max-width:100%;");
	}
	
	public static function getHistorieData($ownerClass, $ownerClassID, HistorieTable $Tab){
		$FM = new FileManagerGUI();
		$FM->setOwner("WAdresse", $ownerClassID);
		
		$pathing = $FM->setPath();
		if($pathing !== true)
			return true;
		
		$FM->hideDirs(true);
		$files = array();
		while($D = $FM->getNextEntry()){
			$files[$D->A("FileCreationDate")] = $D;
		}
		
		arsort($files);
		
		$i = 0;
		foreach($files As $F){
			$Tab->addHistorie("Datei", "computer", $F->A("FileCreationDate"), $F->A("FileName")."");
			
			$i++;
			if($i == 9)
				break;
		}
		
		return true;
	}
}
?>