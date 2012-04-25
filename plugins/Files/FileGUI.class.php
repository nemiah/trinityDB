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

class FileGUI extends File implements iGUIHTML2 {
	public $showPreviewOnly = false;
	
	function getHTML($id){
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUIX($this);
		$gui->name("Dateieigenschaften");
		#$gui->setObject($this);
		#$gui->setLabelCaption("Dateieigenschaften");

		$gui->label("FileName","Dateiname");
		$gui->label("FileDir","Verzeichnis");
		$gui->label("FileSize","Größe");
		$gui->label("FileMimetype","Typ");
		$gui->label("FileIsWritable","beschreibbar?");
		$gui->label("FileIsReadable","lesbar?");
		$gui->label("FileCreationDate","Erstellt am");
		$gui->space("FileIsWritable");
		$gui->type("FileIsDir","hidden");

		$gui->parser("FileSize","FileGUI::sizeParser");
		$gui->parser("FileIsWritable","Util::catchParser");
		$gui->parser("FileIsReadable","Util::catchParser");
		$gui->parser("FileCreationDate","FileGUI::dateParser");
		
		$display = "";
		
		$Path = $this->A->FileDir."/".$this->A->FileName;
		$relPath = $this->getRelPath();
		
		switch($this->A->FileMimetype){
			case "image/jpeg":
			case "image/png":
			case "image/gif":
				
				$display .= "<img src=\"".$relPath."\" />";
			break;
		
			case "text/plain":
			case "text/x-php":
				$display = highlight_file($this->A->FileDir."/".$this->A->FileName,true);
			break;

			case "application/pdf":
				$display = "<input onclick=\"windowWithRme('File', '$Path', 'previewPDF', '');\" type=\"button\" class=\"bigButton backgroundColor2\" value=\"PDF\nanzeigen\" style=\"margin:5px;background-image:url(./images/navi/pdf.png);\" />";
			break;
		}
		
		if($display != "") $display = "
		<div>
			<div class=\"backgroundColor1 Tab\"><p>Vorschau</p></div>
			<div class=\"backgroundColor3\" style=\"margin-left:10px;margin-right:10px;overflow:auto;max-height:400px;\">$display</div>
		</div>";

		if($this->A("FileIsDir") == "0"){
			if(stripos(PHP_OS, "WIN") === false){
				$B = $gui->addSideButton("Verschieben", "./plugins/Files/move.png");
				#$B->select(false, "mFile", "File", $this->getID(), "moveToDir");
				$B->customSelect("contentRight", $this->getID(), "mFile", "pFile.move");
			}
			
			$B = $gui->addSideButton("Umbenennen", "./plugins/Files/rename.png");
			$B->popup("", "Datei umbenennen", "File", $this->getID(), "popupRename");
		}
		
		$gui->optionsEdit(false, false);
		
		return (!$this->showPreviewOnly ? $gui->getEditHTML() : "").$display;
	}

	public function moveToDir($newDir){
		$FD = new File($newDir);
		if($FD->A("FileIsDir") != "1")
			Red::alertD("Bitte wählen Sie ein Verzeichnis!");

		try {
			$pr = parent::moveToDir($newDir);
		} catch(Exception $e){
			Red::errorD($e->getMessage());
		}
		
		if($pr === false)
			echo $this->getID();
		else
			echo $pr;
	}

	public function popupRename(){
		$F = new HTMLForm("newFileName", array("dateiName"));
		$F->setLabel("dateiName", "Neuer Name");
		$F->setSaveRMEPCR("Datei umbenennen", "./images/i2/save.gif", "File", $this->getID(), "rename", "function(transport) { contentManager.loadFrame('contentLeft', 'File', transport.responseText, 0); Popup.close('File', 'edit'); contentManager.reloadFrame('contentRight'); }");

		echo $F;
	}

	public function rename($newName){
		try {
			$result = parent::rename($newName);
		} catch(Exception $e){
			Red::errorD($e->getMessage());
		}

		if($result === false)
			echo $this->getID();
		else
			echo $result;
	}
	
	public function previewWindow(){
		$this->loadMe();
		$relPath = $this->getRelPath();
		
		$display = "";
		if($this->A->FileIsDir == "0")
			switch($this->A->FileMimetype){
				case "image/jpeg":
				case "image/png":
					
					$display .= "<img src=\".".$relPath."\" />";
				break;
				case "text/plain":
				case "text/x-php":
					$display = "<body><p>".highlight_file($this->ID,true)."</p></body>";
				break;
				case "application/pdf":
					$display = "<script>document.location='.".$relPath."';</script>";
				break;
			}
		else
			$display = "Verzeichnis";
		echo "<html>".$display."</html>";
	}

	public static function sizeParser($w){
		return Util::formatByte($w);
	}
	
	public static function dateParser($w){
		return Util::CLDateParser($w);
	}

	public function previewPDF(){
		$this->loadMe();
		echo "<html><script>document.location='.".$this->getRelPath()."';</script></html>";
	}
}
?>