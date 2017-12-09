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
class mFileGUI extends mFile implements iGUIHTMLMP2 {
		
	/*public function getFolderSelection($targetFrame = "", $path = "/"){
		echo $path;
		$useDir = FileStorage::getFilesDir().$path;
		
		
		$this->setDir($useDir);
		
		$html = "<div style=\"max-height:350px;overflow:auto;\">";
		while($F = $this->getNextEntry()){
			if($F->A("FileName") == "." OR $F->A("FileName") == "..")
				continue;
						
			if(!$F->A("FileIsDir"))
				continue;
			
			$B = new Button("Verzeichnis öffnen", "./plugins/Files/icons/folder.png", "icon");
			$B->style("margin-right:5px;margin-top:2px;");

			$html .= "<div style=\"padding:5px;\">$B <a href=\"#\" onclick=\"".OnEvent::rme($this, "getFolderSelection", array("'$targetFrame'", "'$path".$F->A("FileName")."/'"), "function(t){ \$j('#$targetFrame').html(t.responseText) }")." return false;\">".$F->A("FileName")."</a></div>";
		}
		
		$html .= "</div>";
		
		echo $html;
	}*/
	
	public static function getManagerButton($targetClass, $targetID, $usePool = false, $defaultFileField = "", $defaultFileOnChange = null, $useDirectories = false, $popupOptions = "{}"){
		$dir = $targetClass."ID".str_pad($targetID."", 4, "0", STR_PAD_LEFT);
		
		$BF = new Button("Dateien","computer");
		$BF->popup("", "Datei-Manager", "mFile", "", "getPopupManager", array("'".$dir."'", "'$targetClass'", "'$targetID'", $usePool ? "1" : "0", "'$defaultFileField'", "''", "'".addslashes($defaultFileOnChange)."'", $useDirectories ? "1" : "0"), "", $popupOptions);
		
		if(file_exists(FileStorage::getFilesDir().$dir) AND !Util::isDirEmpty(FileStorage::getFilesDir().$dir))
			$BF->className("confirm");
		
		return $BF;
	}
	
	public static function getManagerButtonCustomDir($targetClass, $targetID, $subDir, $usePool = false, $fieldDefaultFile = "", $targetFilename = null, $linkTo = null){
		$args = array("$subDir", "'$targetClass'", "'$targetID'", $usePool ? "1" : "0", "'$fieldDefaultFile'");
		if($targetFilename != null)
			$args[] = "'$targetFilename'";
		
		
		$BF = new Button("Dateien","computer");
		if($linkTo)
			$BF->link($linkTo);
		$BF->popup("", "Datei-Manager", "mFile", "", "getPopupManager", $args);
		
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

		/*$bps = $this->getMyBPSData();
		
		$p = FileStorage::getFilesDir();
		
		
		if($bps != -1 AND isset($bps["path"])){
			$bps["path"] = preg_replace("/^([A-Z])%/", "\\1:", $bps["path"]);
			$this->setDir($bps["path"]);
			$p = realpath($bps["path"]);
		}
		#print_r($bps);
		
		
		if(strpos($p,"specifics") === false)
			$p = FileStorage::getFilesDir();

		$t = new HTMLTable(1);
		$t2 = "";
		
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
		

		*/
		
		$gui = new HTMLColGUI($this);
		$gui->cols(1);
		
		$gui->content("right", $this->rightCol(true));
		
		$gui->resize("var height = 0; \$j('#filesFrame').prevAll().each(function(k, v){ height += \$j(v).outerHeight(); }); \$j('#filesFrame').css('height', contentManager.maxHeight() - height);");
		
		return $gui;
		
		/*$gui = new HTMLGUIX($this);
		$gui->name("Datei");
		#$gui->setMultiPageMode($gesamt, $page, 0, "contentRight", str_replace("GUI","",get_class($this)));

		$gui->blacklists(array($p."/.", $p."/.."));

		$gui->attributes(array("FileIsDir","FileName"));
		#$gui->setDisplaySide("right");
		
		$gui->colWidth("FileIsDir","18px;");
		$gui->options(true, true, false, false);
		
		$gui->parser("FileIsDir","mFileGUI::isDirParser");#,array("\$aid",($bps != -1 AND isset($bps["selectionMode"])) ? "true" : "false"));
		$gui->parser("FileName","mFileGUI::nameParser");#,array("\$aid","\$FileIsDir"));
		
		#$gui->autoCheckSelectionMode(get_class($this));

		return "<form id=\"newDirForm\">".$t->getHTML()."</form>".$t2."<div style=\"margin-top:30px;\">".$gui->getBrowserHTML($id)."</div>";*/
	}
	
	public function rightCol($return = false){
		$useDir = realpath(FileStorage::getFilesDir());
		
		$path = BPS::getProperty("mFileGUI", "path", false);
		if($path){
			$path = preg_replace("/^([A-Z])%/", "\\1:", $path);
			
			if(strpos($path, realpath(FileStorage::getFilesDir())) === 0)
				$useDir = $path;
		}
		
		$writable = is_writable($useDir);
		$readable = is_readable($useDir);
		
		$this->setDir($useDir);
		
		$this->loadMultiPageMode(-1, 0, 10000);
		
		$showPath = str_replace(realpath(FileStorage::getFilesDir()), "", $useDir);
		
		
		$BH = new Button("Root", "home", "iconicL");
		$BH->onclick($this->pather(FileStorage::getFilesDir()));
		$BH->style("float:left;");
				
		$way = realpath(FileStorage::getFilesDir());
		$bread = "<div style=\"vertical-align:top;display:inline-block;border-right:1px solid #aaa;\" class=\"isFolder\" data-path=\"$way\">$BH&nbsp;</div>";
		foreach(explode(DIRECTORY_SEPARATOR, $showPath) AS $k => $v){
			if($k == 0)
				continue;
			
			$way .= DIRECTORY_SEPARATOR."$v";
			
			$bread .= "<div class=\"selectionRow isFolder\" data-path=\"$way\" onclick=\"".$this->pather($way)."\" style=\"padding:8px;cursor:pointer;border-right:1px solid #aaa;color:#555;vertical-align:top;display:inline-block;padding-left:20px;padding-right:20px;border-bottom:0px;\">$v</div>";
		}
		
		$I = new HTMLInput("upload", "file", null, array("path" => str_replace('\\', "/", realpath($way)), "class" => "File"));
		$I->style("width:250px;");
		$I->onchange($this->pather($way));
		
		$IN = new HTMLInput("newDir", "text");
		$IN->placeholder("Neues Verzeichnis");
		$IN->style("width:250px;margin-right:20px;");
		$IN->onEnter(OnEvent::rme($this, "makeDir", array("'".str_replace('\\', "/", realpath($way))."'", "\$j(this).val()"), "function(){ ".$this->pather($way)." }"));
		
		$ISA = new HTMLInput("selectAll", "checkbox");
		$ISA->onchange("console.log(\$j(this).prop('checked')); \$j('.selectFile').prop('checked', \$j(this).prop('checked'));");
		
		if(!$writable OR !$readable){
			$I = "";
			$IN = "";
		}
		
		$html = "
			<div style=\"background-color:#F7F7F7;border-bottom:1px solid #ddd;\">
				$bread
				<div style=\"display:inline-block;margin-top:2px;float:right;\">$IN</div>
				<div style=\"display:inline-block;margin-top:1px;float:right;\">$I</div>
			</div>
			
			<div id=\"filesFrame\" style=\"overflow:auto;\">
			<form id=\"dlFiles\">
			<div style=\"border-bottom:1px solid #ddd;color:#777;\">
				<div style=\"display:inline-block;float:right;padding:5px;\">
					Größe
					<div style=\"display:inline-block;width:64px;\">&nbsp;</div>
				</div>
				<!--<div style=\"display:inline-block;width:57px;float:left;padding-left:5px;\">$ISA</div>-->
				<div style=\"display:inline-block;width:26px;float:left;padding-left:5px;\">&nbsp;</div>
				<div style=\"padding:5px;\">Dateiname</div>
			</div>";
		
		if(!$readable){
			$html .= "<div style=\"padding:5px;\" class=\"highlight\">Das Verzeichnis <code>$useDir</code> ist nicht lesbar.</div>";
			return $html;
		}
		
		if(!$writable)
			$html .= "<div style=\"padding:5px;\" class=\"highlight\">Das Verzeichnis $useDir ist nicht beschreibbar.<br />
				Es können keine Unterverzeichnisse angelegt oder Dateien hochgeladen werden.<br />
				Bitte machen Sie das Verzeichnis mit <code>chmod 777 ".basename($useDir)."</code> durch den Webserver beschreibbar.</div>";
		$i = 0;
		while($F = $this->getNextEntry()){
			if($F->A("FileName") == "." OR $F->A("FileName") == "..")
				continue;
			
			$ext = Util::ext($F->A("FileName"));
			
			$B = "";
			if($F->A("FileIsDir")){
				$B = new Button("Verzeichnis öffnen", "./plugins/Files/icons/folder.png", "icon");
				$B->style("margin-right:5px;margin-top:2px;");
			} elseif(!$F->A("FileIsDir") AND file_exists(dirname(__FILE__)."/icons/file_extension_$ext.png")) {
				$B = new Button("", "./plugins/Files/icons/file_extension_$ext.png", "icon");
				$B->style("margin-right:5px;margin-top:2px;");
			} elseif(!$F->A("FileIsDir") AND !file_exists(dirname(__FILE__)."/icons/file_extension_$ext.png")) {
				$B = new Button("", "./plugins/Files/icons/file_extension_unknown.png", "icon");
				$B->style("margin-right:5px;margin-top:2px;");
			}
			
			$BDL = new Button("Datei herunterladen", "download", "iconic");
			$BDL->style("margin-right:10px;");
			$BDL->windowRme("File", $F->getID(), "download");
			$size = "<div style=\"display:inline-block;margin-right:20px;color:grey;width:60px;text-align:right;\"></div>";
			
			if($F->A("FileIsDir")){
				$onclick = $this->pather($F->getID());
				$BDL = "";
			} else {
				$onclick = OnEvent::popup("Vorschau", "File", $F->getID(), "previewWindow", "", "", "{width:600, hPosition: 'center'}");
				$size = "<div style=\"display:inline-block;margin-right:20px;color:grey;width:60px;text-align:right;\">".Util::formatByte($F->A("FileSize"))."</div>";
			}
			$onRename = OnEvent::rme($F, "rename", array("\$j('input[name=renameFile$i]').val()"), "function(t){ \$j('.fileName$i').html(t.responseText); \$j('#default$i').show(); \$j('#rename$i').hide(); }");
			
			$BD = new Button("Element löschen", "trash_stroke", "iconic");
			#$BD->style("float:right;");
			$BD->onclick("deleteClass('File','".$F->getID()."', function() { ".$this->pather(realpath(dirname($F->getID())))." },'Element wirklich löschen?');");
			
			$BR = new Button("Element umbenennen", "pen_alt2", "iconic");
			$BR->style("margin-right:10px;");
			$BR->onclick("\$j('.fileDefault').show(); \$j('.fileRename, #default$i').hide(); \$j('#rename$i').show();");
			
			$IR = new HTMLInput("renameFile$i", "text", $F->A("FileName"));
			$IR->style("width:350px;");
			$IR->onEnter($onRename);
			
			$BC = new Button("Umbenennen abbrechen", "x", "iconic");
			$BC->onclick("\$j('#default$i').show(); \$j('#rename$i').hide();");
			$BC->style("margin-left:10px;");
			
			$BO = new Button("Umbenennen abschließen", "check", "iconic");
			$BO->onclick($onRename);
			$BO->style("margin-left:10px;");
			
			$IC = new HTMLInput("select$i", "checkbox");
			$IC->setClass("selectFile");
			$IC->style("margin-top:2px;margin-right:10px;display:none;");
			
			
			$html .= "
			<div data-path=\"".$F->getID()."\" class=\"selectionRow ".($F->A("FileIsDir") ? "isFolder" : "")."\" style=\"\">
				<div class=\"selectionRowHeightSetter\" style=\"display:inline-block;float:left;\">$IC$B</div>
				<div class=\"selectionRowHeightSetter\" style=\"display:inline-block;float:right;\">$BDL$BR$size$BD</div>
					
				<div id=\"rename$i\" class=\"fileRename selectionRowHeightSetter\" style=\"display:none;margin-bottom:-3px;\">$IR$BC$BO</div>
				<div id=\"default$i\" class=\"selectionRowHeightSetter fileDefault\" onclick=\"".$onclick."\" style=\"padding:5px;padding-top:9px;cursor:pointer;\">
					<span class=\"fileName$i\">".str_replace(".$ext", "<span style=\"color:grey;\">.$ext</span>", $F->A("FileName"))."</span>
					
				</div>
				
				
				<div style=\"clear:both;\"></div>
			</div>";
			
			$i++;
		}
		
		$html .= "</form></div>".OnEvent::script("
\$j('.selectionRow').draggable({
	revert: true,
	helper: function(v){
		var newE = \$j(v.target).closest('.selectionRow').clone();
		newE.css('width', '400px').css('opacity', '0.7').addClass('selectionRow');
		newE.find('.iconic').remove();
		return newE;
	}/*,
	start: function (event, ui) {
		 \$j(ui.helper).css('margin-left', event.clientX - \$j(event.target).offset().left + 10);
		 \$j(ui.helper).css('margin-top', event.clientY - \$j(event.target).offset().top + 10);
	 }*/
});

\$j('.isFolder').droppable({
	hoverClass: 'highlight',
	accept: '.selectionRow',
	tolerance: 'pointer',
	drop: function(event, ui) {
		var newE = ui.helper.clone();
		ui.helper.remove();
		".OnEvent::rme($this, "moveFile", array("\$j(ui.draggable).data('path')", "\$j(this).data('path')"), "function(){ 
			\$j(ui.draggable).fadeOut(400, function(){ \$j(this).remove(); }); 
			\$j('body').append(newE);
			\$j(newE).fadeOut(400, function(){ \$j(this).remove(); }); 
		}")."
	}
});
");
		
		if(!$return)
			echo $html;
		else
			return $html;
	}
	
	#public function preview(){
	#	echo "<iframe src=\"\"></iframe>";
	#}
	
	public function makeDir($path, $dirName){
		$F = new File(-1);
		$F->loadMeOrEmpty();
		
		$F->changeA("FileDir", $path);
		$F->changeA("FileIsDir", true);
		$F->changeA("FileName", $dirName);
		
		$F->newMe();
	}
	
	public function moveFile($fileName, $dirName){
		if(strpos($fileName, realpath(FileStorage::getFilesDir())) !== 0)
			Red::errorD ("$fileName nicht im erlaubten Pfad!");
		
		if(strpos($dirName, realpath(FileStorage::getFilesDir())) !== 0)
			Red::errorD ("$dirName ist kein erlaubter Pfad Pfad!");
		
		$F = new File($fileName);
		$F->moveToDir($dirName);
	}
	
	private function pather($path){
		return OnEvent::rme($this, "rightCol", "", "function(t){ \$j('#contentScreenRight').html(t.responseText); fitFrames(); }", "mFileGUI;path:".preg_replace("/^([A-Z]):/", "\\1%", str_replace('\\', "/", realpath($path))));
	}

	public function getUploadForm($path, $onSuccess = "contentManager.reloadFrame('contentRight');", $label = true, $tableStyle = null){
		
		$F = new HTMLForm("fileUpload", array("datei"), $label ? "Datei-Upload:" : null);

		$F->setType("datei", "file", null, array("path" => $path, "class" => "File"));
		$F->addJSEvent("datei", "onChange", $onSuccess);
		$F->getTable()->setColWidth(1, 120);
		if($tableStyle != null) $F->getTable()->setTableStyle($tableStyle);

		return $F;
	}
	
	private static $defaultFileField;
	private static $args;
	public function getPopupManager($rootDir = null, $class = null, $classID = null, $usePool = false, $defaultFileField = "", $uploadTargetFilename = null, $defaultFileOnChange = null, $useDirectories = false){
		self::$args = func_get_args();

		if($rootDir != null){
			$root = FileStorage::getFilesDir().$rootDir;

			$_SESSION["BPS"]->setProperty("mFileGUI", "path", preg_replace("/^([A-Z]):/", "\\1%", $root));
			$_SESSION["BPS"]->setProperty("mFileGUI", "root", preg_replace("/^([A-Z]):/", "\\1%", $root));
			
			$F = new File($root);
			$F->loadMe();

			if($F->getA() == null){
				if(is_writable(FileStorage::getFilesDir())){
					mkdir($root, 0777, true);
				} else {
					$B = new Button("", "stop");
					$B->type("icon");
					$B->style("float:left;margin-right:10px;");
					
					$T = new HTMLTable(1);
					$T->addRow($B."Das Verzeichnis <code>$rootDir</code> existiert nicht und kann nicht automatisch angelegt werden, da keine Schreibberechtigung für <code>specifics</code> vorliegt.");
					
					die($T);
				}
			}
		}

		$bps = $this->getMyBPSData();
		
		$bps["path"] = preg_replace("/^([A-Z])%/", "\\1:", $bps["path"]);
		$bps["root"] = preg_replace("/^([A-Z])%/", "\\1:", $bps["root"]);
			
		if(strpos($bps["path"], $bps["root"]) === false)
			$bps["path"] = $bps["root"];
		
		
		if($bps != -1 AND isset($bps["path"]))
			$this->setDir($bps["path"]);
		
		$this->hideDirs(!$useDirectories);
		
		
		$gui = new HTMLGUIX();

		$gui->object($this);
		$cols = array();
		
		if($defaultFileField != ""){
			$C = new $class($classID);
			self::$defaultFileField = array($C, $C->A($defaultFileField), $defaultFileField, $defaultFileOnChange);
			
			$cols[] = "isDefault";
			$gui->colWidth("isDefault", 20);
			$gui->parser("isDefault","mFileGUI::parserDefault");
		}
		$cols[] = "FileName";
		$gui->attributes($cols);

		$gui->parser("FileName","mFileGUI::nameParser2");

		$gui->options(true, false, false);

		$gui->name("Datei");

		$gui->caption("Dateien");
		
		$gui->addToEvent("onDelete", OnEvent::reloadPopup("mFile"));
		
		if($defaultFileField){
			$TC = new HTMLTable(2);
			$TC->setColWidth(1, 32);
			
			$BC = new Button("Standard", "./plugins/Files/thisRow.png", "icon");
			$BC->style("margin-left:5px;");
			
			$TC->addRow(array($BC, "Als Standard verwenden"));
			$TC->addRowClass("backgroundColor0");
			
			$gui->append($TC);
		}
				
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
		
		$BPool = new Button("Pool\nanzeigen", "./plugins/Files/pool.png");
		$BPool->style("float:right;margin:10px;");
		$BPool->onclick(OnEvent::popupSidePanel("mFile", -1, "sidePanelPool", array("'{$class}Pool'", "'$rootDir'")));
		
		if($usePool AND $rootDir){
			echo $BPool."<div style=\"clear:both;\"></div>";
			if(file_exists(FileStorage::getFilesDir()."{$class}Pool"))
				echo OnEvent::script("window.setTimeout(function(){ if(\$j('#editDetailsmFileSidePanel').length > 0) return; ".OnEvent::popupSidePanel("mFile", -1, "sidePanelPool", array("'{$class}Pool'", "'$rootDir'"))."}, 100);");
		}
		
		$fields = array("datei");
		if($useDirectories)
			$fields[] = "verzeichnis";
		
		$F = new HTMLForm("fileUpload", $fields);
		
		$s = array("path" => str_replace('\\', "/", realpath($bps["path"])), "class" => "File");
		if($uploadTargetFilename != null)
			$s["targetFilename"] = $uploadTargetFilename;
		
		$F->setType("datei", "file", null, $s);
		
		$B = new Button("Erstellen", "./plugins/Files/go-next.png", "icon");
		$B->style("float:right;");
		$B->rmePCR("mFile", "-1", "makeDir", array("'".$bps["path"]."'", "\$j('[name=verzeichnis]').val()"), OnEvent::reloadPopup("mFile"));
		
		$F->addFieldButton("verzeichnis", $B);
		
		$F->addJSEvent("datei", "onChange", OnEvent::reloadPopup("mFile"));
		$F->addJSEvent("verzeichnis", "onEnter", OnEvent::rme($this, "makeDir", array("'".$bps["path"]."'", "\$j(this).val()"), OnEvent::reloadPopup("mFile")));
		
		
		$F->getTable()->setColWidth(1, 120);
		
		$B = new Button("Nach oben", "./plugins/Files/go-up.png", "icon");
		$B->style("float:left;margin-right:5px;");
		
		$TP = new HTMLTable(2);
		$TP->setColWidth(2, 20);
		$TP->addRow(array(
			"<a href=\"#\" onclick=\"".OnEvent::popup("Datei-Manager", "mFile", "", "getPopupManager", array("''", "'".self::$args[1]."'", self::$args[2], self::$args[3], "'".self::$args[4]."'", "'".self::$args[5]."'", "'".self::$args[6]."'", self::$args[7]), "_mFileGUI;path:".realpath($bps["path"]."/../"))." return false;\">$B Nach oben</a>",
			"<div style=\"width:22px;height:20px;\"></div>"
		));
		
		if($bps["root"] != $bps["path"])
			$gui->prepend ($TP);
		
		echo "<p class=\"prettyTitle\">/".str_replace(dirname($bps["root"])."/", "", $bps["path"])."</p><p style=\"color:grey;margin-top:-15px;margin-bottom:10px;\"><small>".dirname(realpath($bps["root"]))."</small></p>".$F.$gui->getBrowserHTML(-1).$oldFiles;
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
		$BP = new Button("Datei anzeigen", "./images/i2/details.png", "icon");
		$BP->popup("", "Vorschau", "File", $e->getID(), "previewWindow", "", "", "{width:600, hPosition: 'center'}");
		$BP->style("float:right;margin-left:5px;");
				
		$dl = "$BP<img src=\"./plugins/Files/download.png\" class=\"mouseoverFade\" title=\"Datei herunterladen\" onclick=\"windowWithRme('File','".$e->getID()."','download','');\" style=\"float:right;margin-left:5px;\" /><span style=\"float:right;color:grey;\">".Util::formatByte(filesize($e->getID()))."</span>";

		
		if($e->A("FileIsDir") == "0") 
			return $dl.$w;
		 
		$B = new Button("Verzeichnis öffnen", "./images/i2/folder.png", "icon");
		$B->style("float:left;margin-right:5px;");
		
		return "<a href=\"#\" onclick=\"".OnEvent::popup("Datei-Manager", "mFile", "", "getPopupManager", array("''", "'".self::$args[1]."'", self::$args[2], self::$args[3], "'".self::$args[4]."'", "'".self::$args[5]."'", "'".self::$args[6]."'", self::$args[7]), "_mFileGUI;path:".realpath($e->getID()))." return false;\">".$B.$w."</a>";
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
		$I = new HTMLInput("isDefault", "checkbox", $E->getID() == self::$defaultFileField[1] ? "1" : "0");
		$I->onchange("var checked = this.checked;".OnEvent::rme(self::$defaultFileField[0] , "saveMultiEditField", array("'".self::$defaultFileField[2]."'", "this.checked ? '".$E->getID()."' : ''"), OnEvent::reloadPopup("mFile").(self::$defaultFileField[2] != null ? "\$j('[name=".self::$defaultFileField[2]."]').val(checked ? '".$E->getID()."' : '');" : "").(self::$defaultFileField[3] != null ? stripslashes(self::$defaultFileField[3]) : ""), "", OnEvent::reloadPopup("mFile")));
		
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
