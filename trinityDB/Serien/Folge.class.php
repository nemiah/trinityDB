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
 *  2010, trinityDB - https://sourceforge.net/p/opentrinitydb/
 */
class Folge extends PersistentObject {
	public function check(Serie $S){
		$FExists = $this->fileExists($S);
		
		$FSize = 0;
		#if($FExists) filesize($S->A("dir")."/".$this->getNewFileName($S, $this->getExistingSuffix($S)));
		
		return array($FExists, $FSize, $this->getExistingSuffix($S));
	}

	public function fileExists($S = null){
		if($S == null)
			$S = new Serie($this->A("SerieID"));

		return file_exists($S->A("dir")."/".$this->getNewFileName($S, $this->getExistingSuffix($S)));
		#return (file_exists($S->A("dir")."/".$this->getNewFileName($S, "avi")) OR file_exists($S->A("dir")."/".$this->getNewFileName($S, "mkv")) OR file_exists($S->A("dir")."/".$this->getNewFileName($S, "divx")));
	}

	public function getExistingSuffix($S){
		if(file_exists($S->A("dir")."/".$this->getNewFileName($S, "avi"))) return "avi";
		if(file_exists($S->A("dir")."/".$this->getNewFileName($S, "mkv"))) return "mkv";
		if(file_exists($S->A("dir")."/".$this->getNewFileName($S, "divx"))) return "divx";

		return "none";
	}

	public function getSuffix($originalFile){
		$ex = explode(".", $originalFile);

		return $ex[count($ex) - 1];
	}

	public function getNewFileName($S, $suffix){
		$replaceWhat = array("?", "\"", "*", "|", "<", ">", ":", "’", "/");
		$replaceWith = array("", "", "", "", "", "", "", "", " ");
		$fixedName = $this->A("name");
		$fixedName = str_replace($replaceWhat, $replaceWith, $fixedName);

		if(Util::isWindowsHost())
			$fixedName = utf8_decode($fixedName);

		return str_replace($replaceWhat, $replaceWith, $S->A("name"))." - S".($this->A("season") < 10 ? "0" : "").$this->A("season")."E".($this->A("episode") < 10 ? "0" : "").$this->A("episode")." - ".$fixedName.".".$suffix;
	}

	public function toggleWanted(){
		if($this->A("wanted") == "1")
			$this->changeA("wanted", "0");
		else
			$this->changeA("wanted", "1");

		$this->saveMe(true, false);
	}
}
?>