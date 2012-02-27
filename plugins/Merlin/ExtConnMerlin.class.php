<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */


/**
 * Please load this class with the ExtConn class.
 * It will not work otherwise.
 */
class ExtConnMerlin {
	public function __construct($absolutePathToPhynx) {
		require_once($absolutePathToPhynx."plugins/Merlin/Merlin.class.php");
		require_once($absolutePathToPhynx."plugins/Merlin/MerlinGUI.class.php");
		require_once($absolutePathToPhynx."plugins/Merlin/phynxCore.class.php");
	}


	function doExport($customerID, array $userApps, array $userPlugins){
		$virtualM = new MerlinGUI(-1);

		$virtualAs = $virtualM->newAttributes();
		$virtualAs->MerlinApps = implode(";:;", $userApps);
		$virtualAs->MerlinPlugins = implode(";:;", $userPlugins);

		if (count($userApps) == 0)
			throw new Exception("Die gewählte Application-Menge ist leer");

		$tmpAnyC = new anyC();
		$tmpAnyC->setCollectionOf("Merlin");
		foreach ($userApps as $value){
			$tmpString = ucfirst($value);
			$parentGathering = "Free".$tmpString;
			$tmpAnyC->addAssocV3("MerlinDirName","LIKE","%$parentGathering%", "OR");
		}

		$ausgabe = array();
		while($m = $tmpAnyC->getNextEntry()){
			$ID = $m->getID();
			$ausgabe[]=$ID;
		}

		$virtualAs->MerlinsParents = implode(";:;", $ausgabe);
		$virtualAs->MerlinDirName = "/var/www/deploy/CustomerID".$customerID;
		$virtualAs->MerlinMakeZip = "1";

		if(!is_dir($virtualAs->MerlinDirName))
			mkdir($virtualAs->MerlinDirName);

		$virtualM->setA($virtualAs);
		$zipLoc = $virtualM->deploy("false");

		return $zipLoc;#$virtualM->getA();
	}
}
?>
