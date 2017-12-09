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

class FileGUI extends File implements iGUIHTML2 {
	public $showPreviewOnly = false;
	function __construct($ID) {
		parent::__construct($ID);
		
		$this->customize();
	}
	
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
			
			#$B = $gui->addSideButton("Umbenennen", "./plugins/Files/rename.png");
			#$B->popup("", "Datei umbenennen", "File", $this->getID(), "popupRename");
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
/*
	public function popupRename(){
		$F = new HTMLForm("newFileName", array("dateiName"));
		$F->setLabel("dateiName", "Neuer Name");
		$F->setSaveRMEPCR("Datei umbenennen", "./images/i2/save.gif", "File", $this->getID(), "rename", "function(transport) { contentManager.loadFrame('contentLeft', 'File', transport.responseText, 0); Popup.close('File', 'edit'); contentManager.reloadFrame('contentRight'); }");

		echo $F;
	}*/

	public function rename($newName){
		try {
			$result = parent::rename($newName);
		} catch(Exception $e){
			Red::errorD($e->getMessage());
		}

		if($result === false)
			$r = basename($this->getID());
		else
			$r = basename($result);
		
		$ext = Util::ext($r);
		
		echo str_replace(".$ext", "<span style=\"color:grey;\">.$ext</span>", $r);
	}
	
	public function previewWindow(){
		#echo $this->getID();
		#$relPath = $this->getRelPath();
		#echo $this->A("FileMimetype");
		$BD = new Button("Datei\nherunterladen", "save");
		$BD->style("margin:10px;float:right;");
		$BD->windowRme("File", $this->getID(), "download");
		$BD->onclick("".OnEvent::closePopup("File"));
		
		if($this->A("FileIsDir") == "0")
			switch($this->A("FileMimetype")){
				case "image/jpeg":
				case "image/png":
				case "image/gif":
				case "image/svg":
				case "image/svg+xml":
					$display .= "$BD<div style=\"clear:both;width:600px;max-height:450px;overflow:auto;\"><img style=\"margin:10px;max-width:560px;\" src=\"data:".$this->A("FileMimetype").";base64,".base64_encode(file_get_contents($this->getID()))."\" /></div>";
				break;
				case "text/plain":
				case "text/x-php":
				case "text/x-c++":
					$display = "$BD<div style=\"clear:both;width:600px;max-height:450px;overflow:auto;\"><p>".highlight_file($this->ID,true)."</p></div>";
				break;
				case "text/html":
					$tinyMCEID = "tinyMCEEditor".rand(100, 9000000);
					$IBody = new HTMLInput("tinyMCEEditor", "textarea");
					$IBody->style("height: 450px; width: 100%; min-height: 450px;  max-height: 450px;");
					$IBody->id($tinyMCEID);
					$IBody->setValue(file_get_contents($this->getID()));
					
					tinyMCEGUI::editorMail($tinyMCEID);
					
					$display = "$IBody".OnEvent::script("
						\$j(window).oneTime(40, function() {
							".tinyMCEGUI::editorMail($tinyMCEID)."
						});;");
				break;
				case "application/pdf":
					$display = "<iframe id=\"filePreview\" style=\"width:100%;border:0;height:500px;\"></iframe>
						".OnEvent::script(OnEvent::iframe($this, "previewPDF", "", "filePreview"));
				break;
				default:
					$display = "<div  class=\"highlight\">$BD<p style=\"padding-top:10px;\">Es steht leider keine Vorschau<br />für diesen Dateityp zur Verfügung.</p><div style=\"clear:both;\"></div>";
			}
		else
			$display = "Verzeichnis";
		
		echo $display;
	}

	public static function sizeParser($w){
		return Util::formatByte($w);
	}
	
	public static function dateParser($w){
		return Util::CLDateParser($w);
	}

	public function previewPDF(){
		if(strpos($this->getID(), realpath(FileStorage::getFilesDir())) === false)
			return;
		
		header('Content-Type: application/pdf');
		header('Content-Length: ' . filesize($this->getID()));
		header('Content-disposition: inline; filename="' . basename($this->getID()) . '"');
		readfile($this->getID());
	}
}
?>