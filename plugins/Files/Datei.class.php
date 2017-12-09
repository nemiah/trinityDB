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
class Datei extends PersistentObject {
	public static function updatePath($oldPath, $newPath){
		$AC = anyC::get("Datei", "DateiPath", $oldPath);

		while($D = $AC->getNextEntry()){
			$D->changeA("DateiPath", $newPath);
			$D->changeA("DateiName", basename($newPath));

			$D->saveMe();
		}
	}
	
	public function newMe($checkUserData = true, $output = false) {
		$this->changeA("DateiAddedDate", time());
		
		return parent::newMe($checkUserData, $output);
	}
}
?>
