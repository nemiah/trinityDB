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
class mFile extends anyC {
	private $hideDirs = false;

	public function __construct(){
		$this->storage = "File";
		$this->setCollectionOf("File");
	}

	public function hideDirs($bool){
		$this->hideDirs = $bool;
	}

	public function lCV3($id = -1, $returnCollector = true){
		$num = parent::lCV3($id, $returnCollector);

		$c = array();

		while($E = $this->getNextEntry()){
			if($E->A("FileIsDir") != 1) $c[] = $E;
		}
		$this->resetPointer();
		if($this->hideDirs)
			$this->collector = $c;

		return $num;
	}
	
	public function setDir($dir, $forceDir = false){
		$this->loadAdapter();
		
		$this->Adapter->setDBFolder($dir, $forceDir);
	}
}
?>
