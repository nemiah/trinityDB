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
class mFileGUI extends mFile implements iGUIHTMLMP2 {
	
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
		
		$p = realpath("../specifics/");
		
		if($bps != -1 AND isset($bps["path"])){
			$this->setDir($bps["path"]);
			$p = realpath($bps["path"]);
		}
		
		$t = new HTMLTable(1);
		$t2 = "";
		
		if(strpos($p,"specifics") === false) {
			#$t->addRow("Höchstes Verzeichnis erreicht!");
			$p = realpath("../specifics")."/";
		}

		if(!is_readable($p)){
			$t->addRow("Verzeichnis <code>$p</code> nicht lesbar.<br /><br /><input type=\"button\" class=\"bigButton backgroundColor3\" value=\"zurück\" style=\"background-image:url(./images/navi/back.png);\" onclick=\"reloadRightFrame('mFileGUI;path:".realpath($p."/../")."');\" />");
			return $t->getHTML();
		}
		
		if(is_writable($p)) {
			$t = new HTMLTable(1, "Verzeichnis erstellen:");
			$t->addRow("
				<input type=\"button\" value=\"anlegen\" style=\"width:40%;float:right;\" onclick=\"saveClass('File','-1', function() {reloadRightFrame();}, 'newDirForm');\" />
				<input type=\"text\" onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\" name=\"FileName\" style=\"width:40%;\" />
				<input type=\"hidden\" name=\"FileDir\" value=\"$p\" />
				<input type=\"hidden\" name=\"FileIsDir\" value=\"1\" />");

			$t2 = $this->getUploadForm($p);
			
		}
		else $t->addRow("Verzeichnis $p nicht beschreibbar. Es können keine Unterverzeichnisse angelegt oder Dateien hochgeladen werden.<br /><br />Bitte machen Sie das Verzeichnis mit <code>chmod 777 ".basename($p)."</code> durch den Webserver beschreibbar.");
		

		$gui = new HTMLGUI2();
		$gesamt = $this->loadMultiPageMode($id, $page, 0);
		$gui->setMultiPageMode($gesamt, $page, 0, "contentRight", str_replace("GUI","",get_class($this)));
		

		$gui->setName("Datei");
		$gui->setObject($this);
		#$gui->setAttributes($this->collector);
		$gui->setCollectionOf($this->collectionOf,"Datei");

		$gui->setIsDisplayMode(true);
		$gui->setDeleteInDisplayMode(true);
		
		$gui->setShowAttributes(array("FileIsDir","FileName"));
		$gui->setDisplaySide("right");
		
		$gui->setColWidth("FileIsDir","18px;");
		
		$gui->setParser("FileIsDir","mFileGUI::isDirParser",array("\$aid",($bps != -1 AND isset($bps["selectionMode"])) ? "true" : "false"));
		$gui->setParser("FileName","mFileGUI::nameParser",array("\$aid","\$FileIsDir"));
		
		$gui->autoCheckSelectionMode(get_class($this));
		try {
			return "<form id=\"newDirForm\">".$t->getHTML()."</form>".$t2."<div style=\"margin-top:30px;\">".$gui->getBrowserHTML($id)."</div>";
		} catch (Exception $e){ }
	}

	public function getUploadForm($path, $onSuccess = "contentManager.reloadFrame('contentRight');", $label = true, $tableStyle = null){
		
		$F = new HTMLForm("fileUpload", array("datei"), $label ? "Datei-Upload:" : null);

		$F->setType("datei", "file", null, array("path" => $path, "class" => "File"));
		$F->addJSEvent("datei", "onChange", $onSuccess);
		$F->getTable()->setColWidth(1, 120);
		if($tableStyle != null) $F->getTable()->setTableStyle($tableStyle);
		#$F->setSaveUpload("ImportDatanorm", "Datanorm-Datei importieren","function () { contentManager.loadFrame('contentLeft', 'DatanormImport'); }");

		return $F;
		/*
		if($label) $t2 = new HTMLTable(1,"Datei hochladen:");
		else $t2 = new HTMLTable(1);

		if($tableStyle != null) $t2->setTableStyle($tableStyle);

		$t2->addRow("
			<table style=\"width:100%;border:0px;\">
				<tr>
					<td style=\"padding:0px;\">
						<input type=\"file\" size=\"4\" style=\"width:200px;\" name=\"datei\" id=\"uploadFile\" />
					</td>
					<td style=\"padding:0px;width:40%;\">
						<input type=\"submit\" style=\"width:100%;\" value=\"hochladen\"/>
					</td>
				</tr>
			</table>
			<input type=\"hidden\" value=\"-1\" name=\"id\"/>
			<input type=\"hidden\" value=\"File\" name=\"class\" />
			<input type=\"hidden\" value=\"$path\" name=\"FileDir\"/>
			<input type=\"hidden\" value=\"FileContent\" name=\"saveToAttribute\"/>");

		return "<form onsubmit=\"return AIM.submit($('formImage'), {'onComplete' : function(){
						$onSuccess
						}});\" id=\"formImage\" enctype=\"multipart/form-data\" method=\"post\" action=\"./interface/set.php\">".$t2."</form>";*/
	}

	public function getPopupManager($rootDir = null){
		if($rootDir != null){
			$T = new HTMLTable(1);

			$rel = "specifics/$rootDir";
			$root = Util::getRootPath().$rel;

			$_SESSION["BPS"]->setProperty("mFileGUI", "path", $root);
			$_SESSION["BPS"]->setProperty("mFileGUI", "root", $root);

			$F = new File($root);
			$F->loadMe();

			if($F->getA() == null){
				if(is_writable(Util::getRootPath()."specifics")){
					mkdir($root);
				}
				else{
					$B = new Button("", "stop");
					$B->type("icon");
					$B->style("float:left;margin-right:10px;");

					$T->addRow($B."Das Verzeichnis <code>$rel</code> existiert nicht und kann nicht automatisch angelegt werden, da keine Schreibberechtigung für <code>specifics</code> vorliegt.");
					die($T);
				}
			}
		}

		$bps = $this->getMyBPSData();

		#print_r($bps);

		if(strpos($bps["path"], $bps["root"]) === false)
			$bps["path"] = $bps["root"];
		

		if($bps != -1 AND isset($bps["path"])){
			$this->setDir($bps["path"]);
			#$p = realpath($bps["path"]);
		}
		
		$gui = new HTMLGUIX();

		$gui->object($this);
		$gui->colWidth("FileIsDir", 20);

		$gui->attributes(array("FileIsDir","FileName"));

		$gui->parser("FileIsDir","mFileGUI::popupIsDirParser2");
		$gui->parser("FileName","mFileGUI::nameParser2");

		$gui->options(false, false, false);

		$gui->name("Datei");

		echo $this->getUploadForm($bps["path"], "rmeP('mFile', '', 'getPopupManager', '', 'if(checkResponse(transport)) $(\'mDateiDetailsContent\').update(transport.responseText);');", false).$gui->getBrowserHTML(-1);
	}

	public static function popupIsDirParser2($w, $e){
		if($w == "0") return "<img class=\"mouseoverFade\" onclick=\"contentManager.loadFrame('contentLeft','File','".$e->getID()."');\" src=\"./images/i2/details.png\" />";
		if($w == "1") return "<img class=\"mouseoverFade\" onclick=\"rmeP('mFile', '', 'getPopupManager', '', 'if(checkResponse(transport)) $(\'mDateiDetailsContent\').update(transport.responseText);', '_mFileGUI;path:".realpath($e->getID())."');\" src=\"./images/i2/folder.png\" />";
	}

	public static function nameParser2($w, $e){
		$dl = "<img src=\"./plugins/Files/download.png\" class=\"mouseoverFade\" title=\"Datei herunterladen\" onclick=\"windowWithRme('File','".$e->getID()."','download','');\" style=\"float:right;\" />";

		if($e->A("FileIsDir") == "0") return $dl.$w;
		else return $w;
	}

	public static function popupIsDirParser($w, $l, $p){
		$p = HTMLGUI::getArrayFromParametersString($p);
		if($w == "0") return "<img class=\"mouseoverFade\" onclick=\"contentManager.loadFrame('contentLeft','File','$p[0]');\" src=\"./images/i2/details.png\" />";
		if($w == "1") return "<img class=\"mouseoverFade\" onclick=\"rmeP('mFile', '', 'getPopupManager', '', 'if(checkResponse(transport)) $(\'mDateiDetailsContent\').update(transport.responseText);', '_mFileGUI;path:".realpath($p[0])."');\" src=\"./images/i2/folder.png\" />";
	}

	public static function nameParser($w, $l, $p){
		$p = HTMLGUI::getArrayFromParametersString($p);
		$dl = "<img src=\"./plugins/Files/download.png\" class=\"mouseoverFade\" title=\"Datei herunterladen\" onclick=\"windowWithRme('File','$p[0]','download','');\" style=\"float:right;\" />";

		if($p[1] == "0") return $dl.$w;
		else return $w;
	}
	
	public static function isDirParser($w, $l, $p){
		$p = HTMLGUI::getArrayFromParametersString($p);
		if($w == "0") return $p[1] != "true" ? "<img class=\"mouseoverFade\" onclick=\"contentManager.loadFrame('contentLeft','File','$p[0]');\" src=\"./images/i2/details.png\" />" : "";
		else return "<img class=\"mouseoverFade\" onclick=\"reloadRightFrame('_mFileGUI;path:".realpath($p[0])."');\" src=\"./images/i2/folder.png\" />";
	}
}
?>
