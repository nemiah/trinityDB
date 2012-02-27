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