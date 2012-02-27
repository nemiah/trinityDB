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
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setLabelCaption("Dateieigenschaften");

		$gui->setLabel("FileName","Dateiname");
		$gui->setLabel("FileDir","Verzeichnis");
		$gui->setLabel("FileSize","Größe");
		$gui->setLabel("FileMimetype","Typ");
		$gui->setLabel("FileIsWritable","beschreibbar?");
		$gui->setLabel("FileIsReadable","lesbar?");
		$gui->insertSpaceAbove("FileIsWritable");
		$gui->setType("FileIsDir","hidden");
		
		$gui->setParser("FileSize","Util::formatByte");
		
		$gui->setIsDisplayMode(true);
		
		$gui->setStandardSaveButton($this);
	
		$gui->setParser("FileIsWritable","Util::catchParser");
		$gui->setParser("FileIsReadable","Util::catchParser");
		
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
		
		return (!$this->showPreviewOnly ? $gui->getEditHTML() : "").$display;
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
	
	public function previewPDF(){
		$this->loadMe();
		echo "<html><script>document.location='.".$this->getRelPath()."';</script></html>";
	}
}
?>