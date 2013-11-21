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

class mIncoming extends anyC {

	public function getNewFiles($forMoving = false){
		$episodes = array();

		if($forMoving)
			$this->addAssocV3("IncomingUseForMoving", "=", "1");

		while($I = $this->getNextEntry()){
			$E1 = $this->getAllFiles($I->A("IncomingDir"));

			$episodes = array_merge($episodes, $E1);
		}

		natcasesort($episodes);

		return $episodes;
	}
	
	public function renameDownloaded($output = false){
		$AC = anyC::get("JDownload");
		$AC->addAssocV3("JDownloadRenameto", "!=", "");
		$AC->addAssocV3("JDownloadFilename", "!=", "");
		$AC->addAssocV3("JDownloadRenamed", "=", "0");
		$AC->addAssocV3("JDownloadDate", ">=", time() - 3600 * 24 * 2);
		
		$dirs = array();
		$ACI = anyC::get("Incoming", "IncomingUseForDownloads", "1");
		while($I = $ACI->getNextEntry())
			$dirs[] = $I->A("IncomingDir");
		
		while($D = $AC->getNextEntry()){
			$filename = preg_replace("/\.htm$/", "", basename($D->A("JDownloadFilename")));
			$ext = Util::ext($filename);
			
			$found = false;
			foreach($dirs AS $dir){
				if(!file_exists($dir."/$filename") AND file_exists($dir."/".basename($D->A("JDownloadFilename"))))
					$filename = basename($D->A("JDownloadFilename"));
				
				if(file_exists($dir."/$filename")){
					$found = true;
					$newName = Util::makeFilename(str_replace(" ", ".", $D->A("JDownloadRenameto")).".$ext");
					if(file_exists($dir."/".$newName)){
						if($output)
							echo "<p>$filename: $newName ALREADY EXISTS!</p>";
						
						$D->changeA("JDownloadRenamed", "-1");
						$D->saveMe();
						continue;
					}
					
					if(filesize($dir."/$filename") != $D->A("JDownloadFilesize"))
						continue;
					
					if(rename($dir."/$filename", $dir."/".$newName)){
						if($output)
							echo "<p>renamed $dir/$filename to ".$newName."</p>";
						$D->changeA("JDownloadRenamed", time());
						$D->saveMe();
					} else {
						if($output)
							echo "<p>$filename: ERROR RENAMING!</p>";
					}
				}
				#	rename($filename, Util::makeFilename($D->A("JDownloadRenameto").".$ext"));
			}
			
			if(!$found AND $output)
				echo "<p>$filename: NOT FOUND!</p>";
			
		}
	}

	private function getAllFiles($dir){
		$D = new mFile();
		$D->setDir($dir, true);

		$episodes = array();

		while($F = $D->getNextEntry()){
			if($F->A("FileIsDir") == "1" AND strpos($F->A("Filename"), ".") === 1) continue;

			$episodes[] = $F->getID();
		}

		return $episodes;
	}

	public function findNewEpisodes(){
		$episodes = $this->getNewFiles(true);

		$ac = new anyC();
		$ac->setCollectionOf("Serie");
		#$ac->addAssocV3("status", "=", "Continuing");

		$found = array();

		while($S = $ac->getNextEntry()){
			$found[] = $S->findNewEpisodes($episodes);
		}

		return $found;
	}
}
?>