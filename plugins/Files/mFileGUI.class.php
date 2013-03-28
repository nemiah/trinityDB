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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class mFileGUI extends mFile implements iGUIHTMLMP2 {
	
	public static function getManagerButton($targetClass, $targetID, $usePool = false, $fieldDefaultFile = ""){
		
		$BF = new Button("Dateien","computer");
		$BF->popup("", "Datei-Manager", "mFile", "", "getPopupManager", array("'".$targetClass."ID".str_pad($targetID."", 4, "0", STR_PAD_LEFT)."'", "'$targetClass'", "'$targetID'", $usePool ? "1" : "0", "'$fieldDefaultFile'"));
		return $BF;
	}
	
	public static function addFile($className, $classID, $fileID){
		$F = new File($fileID);
		$F->loadMe();
		
		$D = new Datei(-1);
		$A = $D->newAttributes();
		
		$A->DateiClass = $className;
		$A->DateiClassID = $classID;
		$A->DateiPath = $fileID;
		$A->DateiName = basename($fileID);
		$A->DateiSize = $F->getA()->FileSize;
		$A->DateiIsDir = $F->getA()->FileIsDir;
		
		$D->setA($A);
		return $D->newMe();
	}
	
	public function getHTML($id, $page){

		$bps = $this->getMyBPSData();
		
		$p = FileStorage::getFilesDir();
		
		
		if($bps != -1 AND isset($bps["path"])){
			$bps["path"] = preg_replace("/^([A-Z])%/", "\\1:", $bps["path"]);
			$this->setDir($bps["path"]);
			$p = realpath($bps["path"]);
		}
		#print_r($bps);
		
		$t = new HTMLTable(1);
		$t2 = "";
		
		if(strpos($p,"specifics") === false)
			$p = FileStorage::getFilesDir();

		if(!is_readable($p)){
			$t->addRow("Verzeichnis <code>$p</code> nicht lesbar.<br /><br /><input type=\"button\" class=\"bigButton backgroundColor3\" value=\"zurück\" style=\"background-image:url(./images/navi/back.png);\" onclick=\"contentManager.reloadFrameRight('mFileGUI;path:".realpath($p."/../")."');\" />");
			return $t->getHTML();
		}
		
		if(is_writable($p)) {
			$t = new HTMLTable(1, "Verzeichnis erstellen:");
			$t->addRow("
				<input type=\"button\" value=\"anlegen\" style=\"width:40%;float:right;\" onclick=\"saveClass('File','-1', function() {contentManager.reloadFrameRight();}, 'newDirForm');\" />
				<input type=\"text\" onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\" name=\"FileName\" style=\"width:40%;\" />
				<input type=\"hidden\" name=\"FileDir\" value=\"$p\" />
				<input type=\"hidden\" name=\"FileIsDir\" value=\"1\" />");

			$t2 = $this->getUploadForm(str_replace('\\', "/", preg_replace("/^([A-Z]):/", "\\1%", $p)));
			
		}
		else $t->addRow("Verzeichnis $p nicht beschreibbar. Es können keine Unterverzeichnisse angelegt oder Dateien hochgeladen werden.<br /><br />Bitte machen Sie das Verzeichnis mit <code>chmod 777 ".basename($p)."</code> durch den Webserver beschreibbar.");
		

		$gesamt = $this->loadMultiPageMode($id, $page, 0);
		$gui = new HTMLGUIX($this);
		$gui->name("Datei");
		#$gui->setMultiPageMode($gesamt, $page, 0, "contentRight", str_replace("GUI","",get_class($this)));

		$gui->blacklists(array($p."/.", $p."/.."));

		#$gui->setObject();
		#$gui->setAttributes($this->collector);
		#$gui->setCollectionOf($this->collectionOf,"Datei");

		#$gui->setIsDisplayMode(true);
		#$gui->setDeleteInDisplayMode(true);
		#$gui->setEditInDisplayMode(true);

		$gui->attributes(array("FileIsDir","FileName"));
		#$gui->setDisplaySide("right");
		
		$gui->colWidth("FileIsDir","18px;");
		$gui->options(true, true, false, false);
		
		$gui->parser("FileIsDir","mFileGUI::isDirParser");#,array("\$aid",($bps != -1 AND isset($bps["selectionMode"])) ? "true" : "false"));
		$gui->parser("FileName","mFileGUI::nameParser");#,array("\$aid","\$FileIsDir"));
		
		#$gui->autoCheckSelectionMode(get_class($this));

		return "<form id=\"newDirForm\">".$t->getHTML()."</form>".$t2."<div style=\"margin-top:30px;\">".$gui->getBrowserHTML($id)."</div>";

	}

	public function getUploadForm($path, $onSuccess = "contentManager.reloadFrame('contentRight');", $label = true, $tableStyle = null){
		
		$F = new HTMLForm("fileUpload", array("datei"), $label ? "Datei-Upload:" : null);

		$F->setType("datei", "file", null, array("path" => $path, "class" => "File"));
		$F->addJSEvent("datei", "onChange", $onSuccess);
		$F->getTable()->setColWidth(1, 120);
		if($tableStyle != null) $F->getTable()->setTableStyle($tableStyle);

		return $F;
	}
	
	private static $fieldDefaultFile;
	public function getPopupManager($rootDir = null, $class = null, $classID = null, $usePool = false, $fieldDefaultFile = ""){
		if($rootDir != null){
			$T = new HTMLTable(1);

			#$rel = "$rootDir";
			$root = FileStorage::getFilesDir().$rootDir;

			$_SESSION["BPS"]->setProperty("mFileGUI", "path", $root);
			$_SESSION["BPS"]->setProperty("mFileGUI", "root", $root);

			$F = new File($root);
			$F->loadMe();

			if($F->getA() == null){
				if(is_writable(FileStorage::getFilesDir())){
					mkdir($root);
				}
				else{
					$B = new Button("", "stop");
					$B->type("icon");
					$B->style("float:left;margin-right:10px;");

					$T->addRow($B."Das Verzeichnis <code>$rootDir</code> existiert nicht und kann nicht automatisch angelegt werden, da keine Schreibberechtigung für <code>specifics</code> vorliegt.");
					die($T);
				}
			}
		}

		$bps = $this->getMyBPSData();

		if(strpos($bps["path"], $bps["root"]) === false)
			$bps["path"] = $bps["root"];
		

		if($bps != -1 AND isset($bps["path"]))
			$this->setDir($bps["path"]);
		
		$this->hideDirs(true);
		
		
		$gui = new HTMLGUIX();

		$gui->object($this);
		$cols = array();
		
		if($fieldDefaultFile != ""){
			$C = new $class($classID);
			self::$fieldDefaultFile = array($C, $C->A($fieldDefaultFile), $fieldDefaultFile);
			
			$cols[] = "isDefault";
			$gui->colWidth("isDefault", 20);
			$gui->parser("isDefault","mFileGUI::parserDefault");
		}
		$cols[] = "FileName";
		$gui->attributes($cols);

		$gui->parser("FileName","mFileGUI::nameParser2");

		$gui->options(true, false, false);

		$gui->name("Datei");
		
		$gui->addToEvent("onDelete", OnEvent::reloadPopup("mFile"));
		
		
		$oldFiles = "";
		if($class != null AND $classID != null){
			$AC = anyC::get("Datei", "DateiClassID", $classID);
			$AC->addAssocV3("DateiClass", "=", $class);
			
			$oldFiles = new HTMLTable(2, "Verknüpfte Dateien");
			
			while($F = $AC->getNextEntry()){
				$BDL = new Button("Datei herunterladen", "./plugins/Files/download.png", "icon");
				$BDL->onclick("windowWithRme('File','".$F->A("DateiPath")."','download','');");
				$BDL->style("float:right;");
		
				$oldFiles->addRow(array($BDL.$F->A("DateiName")));
			}
		}
		
		$BPool = new Button("Pool\nanzeigen", "./lightCRM/Mail/images/attach.png");
		$BPool->style("float:right;margin:10px;");
		$BPool->onclick(OnEvent::popupSidePanel("mFile", -1, "sidePanelPool", array("'{$class}Pool'", "'$rootDir'")));
		
		if($usePool AND $rootDir){
			echo $BPool."<div style=\"clear:both;\"></div>";
			if(file_exists(FileStorage::getFilesDir()."{$class}Pool"))
				echo OnEvent::script("window.setTimeout(function(){ if(\$j('#editDetailsmFileSidePanel').length > 0) return; ".OnEvent::popupSidePanel("mFile", -1, "sidePanelPool", array("'{$class}Pool'", "'$rootDir'"))."}, 100);");
		}
		
		echo $this->getUploadForm($bps["path"], OnEvent::reloadPopup("mFile"), false).$gui->getBrowserHTML(-1).$oldFiles;
	}
	
	public function copyFile($poolDir, $specificDir, $fileName){
		copy(FileStorage::getFilesDir()."$poolDir/$fileName", FileStorage::getFilesDir()."$specificDir/$fileName");
	}

	public function sidePanelPool($dir, $copyTo){
		$I = new HTMLInput("filesPool", "file");
		$I->onchange(OnEvent::rme($this, "processPoolUpload", array("'$dir'", "fileName"), " ".OnEvent::reloadSidePanel("mFile")));
		echo "<div style=\"padding:5px;height:50px;\">".$I."</div></div>";
		
		if(!file_exists(FileStorage::getFilesDir()."$dir"))
			return;
		
		$T = new HTMLTable(1, "Pool");
		$Idir = new DirectoryIterator(FileStorage::getFilesDir()."$dir");
		foreach ($Idir as $file) {
			if($file->isDot()) continue;
			if($file->isDir()) continue;
			
			$BD = new Button("Datei löschen", "./images/i2/delete.gif", "icon");
			$BD->style("float:right;margin-left:5px;");
			$BD->rmePCR("mFile", -1, "deletePoolFile", array("'$dir'", "'".$file->getFilename()."'"), OnEvent::reloadSidePanel("mFile"));
			
			$BU = new Button("Datei verwenden", "./images/i2/insert.png", "icon");
			$BU->style("float:left;margin-right:5px;");
			$BU->rmePCR("mFile", -1, "copyFile", array("'$dir'", "'$copyTo'", "'".$file->getFilename()."'"), OnEvent::reloadPopup("mFile"));
			
			$T->addRow(array("$BU$BD<small style=\"color:grey;float:right;margin-top:4px;\">".Util::formatByte($file->getSize())." </small>".(strlen($file->getFilename()) > 15 ? substr($file->getFilename(), 0, 15)."..." : $file->getFilename())));
		}
		
		echo $T;
	}
	
	public function processPoolUpload($dir, $fileName){
		$uloadedFile = Util::getTempDir().$fileName.".tmp";
		
		if(!file_exists(FileStorage::getFilesDir()."$dir"))
			mkdir(FileStorage::getFilesDir()."$dir");
		
		
		if(copy($uloadedFile, FileStorage::getFilesDir()."$dir/$fileName")){
			unlink($uloadedFile);
		} else
			Red::errorD("Fehler beim Upload der Datei!");
	}
	
	public function deletePoolFile($dir, $fileName){
		unlink(FileStorage::getFilesDir()."$dir/".$fileName);
	}
	
	public static function popupIsDirParser2($w, $e){
		if($w == "0") return "<img class=\"mouseoverFade\" onclick=\"contentManager.loadFrame('contentLeft','File','".$e->getID()."');\" src=\"./images/i2/details.png\" />";
		if($w == "1") return "<img class=\"mouseoverFade\" onclick=\"rmeP('mFile', '', 'getPopupManager', '', 'if(checkResponse(transport)) $(\'mDateiDetailsContent\').update(transport.responseText);', '_mFileGUI;path:".realpath($e->getID())."');\" src=\"./images/i2/folder.png\" />";
	}

	public static function nameParser2($w, $e){
		$dl = "<img src=\"./plugins/Files/download.png\" class=\"mouseoverFade\" title=\"Datei herunterladen\" onclick=\"windowWithRme('File','".$e->getID()."','download','');\" style=\"float:right;margin-left:5px;\" /><span style=\"float:right;color:grey;\">".Util::formatByte(filesize($e->getID()))."</span>";

		if($e->A("FileIsDir") == "0") return $dl.$w;
		else return $w;
	}
/*
	public static function popupIsDirParser($w, $l, $p){
		$p = HTMLGUI::getArrayFromParametersString($p);
		if($w == "0") return "<img class=\"mouseoverFade\" onclick=\"contentManager.loadFrame('contentLeft','File','$p[0]');\" src=\"./images/i2/details.png\" />";
		if($w == "1") return "<img class=\"mouseoverFade\" onclick=\"rmeP('mFile', '', 'getPopupManager', '', 'if(checkResponse(transport)) $(\'mDateiDetailsContent\').update(transport.responseText);', '_mFileGUI;path:".realpath($p[0])."');\" src=\"./images/i2/folder.png\" />";
	}*/

	public static function nameParser($w, $E){
		$dl = "<img src=\"./plugins/Files/download.png\" class=\"mouseoverFade\" title=\"Datei herunterladen\" onclick=\"windowWithRme('File','".$E->getID()."','download','');\" style=\"float:right;\" />";

		if($w == ".")
			return "Dieses Verzeichnis<br /><small style=\"color:grey;\">".basename(realpath($E->getID()))."</small>";
		
		if($w == "..")
			return "Zum übergeordneten Verzeichnis<br /><small style=\"color:grey;\">".basename(realpath($E->getID()))."</small>";

		if($E->A("FileIsDir") == "0") return $dl.$w;
		else return $w;
	}
	
	public static function parserDefault($w, $E){
		$I = new HTMLInput("isDefault", "checkbox", $E->getID() == self::$fieldDefaultFile[1] ? "1" : "0");
		$I->onchange(OnEvent::rme(self::$fieldDefaultFile[0] , "saveMultiEditField", array("'".self::$fieldDefaultFile[2]."'", "this.checked ? '".$E->getID()."' : ''"), OnEvent::reloadPopup("mFile"), "", OnEvent::reloadPopup("mFile")));
		
		return $I;
	}
	
	public static function isDirParser($w, $E){

		#$p = HTMLGUI::getArrayFromParametersString($p);
		if($w == "0") return "";#$p[1] != "true" ? "<img class=\"mouseoverFade\" onclick=\"contentManager.loadFrame('contentLeft','File','$p[0]');\" src=\"./images/i2/details.png\" />" : "";
		else {
			$symb = "./images/i2/folder.png";

			if($E->A("FileName") == ".")
				$symb = "./plugins/Files/thisDir.png";

			if($E->A("FileName") == "..")
				$symb = "./plugins/Files/parentDir.png";


			$B = new Button("Verzeichnis öffnen", $symb, "icon");
			#if(realpath($E->getID()) != realpath(Util::getRootPath()."specifics"))
			#$B->onclick("contentManager.reloadFrame('contentRight', '_mFileGUI;path:".preg_replace("/^([A-Z]):/", "\\1%", str_replace('\\', "/", realpath($E->getID())))."');");
			$B->loadFrame("contentRight", "mFile", "-1", "0", "_mFileGUI;path:".preg_replace("/^([A-Z]):/", "\\1%", str_replace('\\', "/", realpath($E->getID())))."");
			return $B;
			#return "<img class=\"mouseoverFade\" onclick=\"contentManager.reloadFrameRight();\" src=\"\" />";
		}
	}
}
?>
