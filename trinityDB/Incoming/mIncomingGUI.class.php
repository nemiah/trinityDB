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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class mIncomingGUI extends mIncoming implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		$bps = $this->getMyBPSData();

		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);
		$gui->version("mIncoming");

		#$gui->options(true, true, true, true);
		$gui->name("Incoming");

		$gui->attributes(array("IncomingDir"));

		try {
			if($bps != -1 AND isset($bps["edit"])) return $gui->getBrowserHTML($id);
		} catch (Exception $e){ }

		$T = new HTMLTable(3, "new episodes");

		$found = $this->findNewEpisodes();


		foreach($found AS $S)
			foreach($S AS $E)
				$T->addRow(array($E["name"], $E["season"], $E["episode"]));


		$Tab = new HTMLSideTable("left");

		$B = new Button("Change\ndirectories", "./trinityDB/Incoming/SetFolder.png");
		$B->onclick("contentManager.loadFrame('contentRight', 'mIncoming', -1, 0, 'mIncomingGUI;edit:true');");
		$Tab->addRow($B);

		$B = $Tab->addButton("Rename\ndownloaded", "redo");
		$B->popup("", "Rename downloaded", "mIncoming", "-1", "renameDownloaded", array("'0'"));

		$B = $Tab->addButton("Create move-\nscript", "redo");
		$B->windowRme("mIncoming", "-1", "createMoveScript", array("'0'"));

		$B = $Tab->addButton("Run move-\nscript", "redo");
		$B->windowRme("mIncoming", "-1", "createMoveScript", array("'1'"));

		$B = $Tab->addButton("Prettifyer", "./trinityDB/Incoming/prettify.png");
		$B->loadFrame("contentLeft", "mIncomingPrettify");
		return ($id == -1 ? $Tab : "").$T;
	}
	
	public function renameDownloaded(){
		$AC = anyC::get("JDownload");
		$AC->addAssocV3("JDownloadFilename", "!=", "");
		$AC->addAssocV3("JDownloadRenamed", "=", "0");
		
		$dirs = array();
		$ACI = anyC::get("Incoming", "IncomingUseForDownloads", "1");
		while($I = $ACI->getNextEntry())
			$dirs[] = $I->A("IncomingDir");
		
		while($D = $AC->getNextEntry()){
			$filename = preg_replace("/\.htm$/", "", basename($D->A("JDownloadFilename")));
			$ext = Util::ext($filename);
			
			$found = false;
			foreach($dirs AS $dir){
				if(file_exists($dir."/$filename")){
					$found = true;
					if(rename($dir."/$filename", $dir."/".Util::makeFilename($D->A("JDownloadRenameto").".$ext"))){
						echo "<p>renamed $dir/$filename to ".Util::makeFilename(str_replace(" ", ".", $D->A("JDownloadRenameto")).".$ext")."</p>";
						$D->changeA("JDownloadRenamed", time());
						$D->saveMe();
					} else {
						echo "<p>$filename: ERROR RENAMING!</p>";
					}
				}
				#	rename($filename, Util::makeFilename($D->A("JDownloadRenameto").".$ext"));
			}
			
			if(!$found)
				echo "<p>$filename: NOT FOUND!</p>";
			
		}
	}

	public function createMoveScript($run = false){

		if(!$run){
			header("Content-Type: application/x-shellscript; charset=UTF-8");
			if(!Util::isWindowsHost())
				header("Content-Disposition: attachment; filename=\"move.sh\"");
			else
				header("Content-Disposition: attachment; filename=\"move.bat\"");
		}
		
		$new = $this->findNewEpisodes();
		$maxlength = 0;
		foreach($new AS $series)
			foreach($series AS $episode)
				if(strlen($episode["path"]) > $maxlength) $maxlength = strlen($episode["path"]);


		$code = "";
		foreach($new AS $series){
			if(count($series) == 0) continue;
			if($series[0]["pointer"]->A("dir") == "") continue;
			
			if(!Util::isWindowsHost())
				$code .= "##".$series[0]["name"]."\n";
			else
				$code .= "echo \"".$series[0]["name"]."\"\r\n";

			#$code .= print_r($series, true);
			foreach($series AS $episode){
				if($episode["pointer"]->A("dir") == "") continue;

				$AC = new anyC();
				$AC->setCollectionOf("Folge");
				$AC->addAssocV3("SerieID", "=", $episode["pointer"]->getID());
				$AC->addAssocV3("season", "=", $episode["season"]);
				$AC->addAssocV3("episode", "=", $episode["episode"]);
				$AC->addAssocV3("wanted", "=", "1");

				$F = $AC->getNextEntry();
				if($F == null) continue;

				if(!Util::isWindowsHost())
					$code .= "mv \"".str_pad($episode["path"]."\"", $maxlength + 3)." \"".$episode["pointer"]->A("dir")."/".$F->getNewFileName($episode["pointer"], $F->getSuffix(basename($episode["path"])))."\";\n";
					#$code .= "mv -n '".str_pad($episode["path"]."\"", $maxlength + 3)." \"".$episode["pointer"]->A("dir")."/".$F->getNewFileName($episode["pointer"], $F->getSuffix(basename($episode["path"])))."';\n";
				else
					$code .= "move /-Y '".str_pad(str_replace ("/", "\\", $episode["path"])."\"", $maxlength + 3)." \"".str_replace("/", "\\", $episode["pointer"]->A("dir"))."\\".$F->getNewFileName($episode["pointer"], $F->getSuffix(basename($episode["path"])))."';\r\n";

			}
			$code .= "\n";
		}
		
		if(!$run)
			echo $code;
		else {
			$SC = new SystemCommand();
			$SC->setCommand($code);
			$SC->execute();
		}
		#print_r();
		#echo Util::getBasicHTMLText(trim($code)."\n\n", "move script");
	}
}
?>