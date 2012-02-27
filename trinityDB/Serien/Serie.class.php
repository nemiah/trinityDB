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
class Serie extends PersistentObject {
	public function checkAllEpisodes($returnValues = false, $newFiles = null){
		$ac = new anyC();
		$ac->setCollectionOf("Folge");
		$ac->addAssocV3("SerieID", "=", $this->getID());
		$ac->addAssocV3("wanted", "=", "1");
		$ac->addOrderV3("season");
		$ac->addOrderV3("episode");

		if(!$returnValues) $T = new HTMLTable(5);
		$R = array();

		if($newFiles != null) $newFound = $this->findNewEpisodes($newFiles);

		while($E = $ac->getNextEntry()){
			$c = $E->check($this);

			if($c[0] === true)
				continue;

			$continue = false;
			if($newFiles != null)
				foreach($newFound AS $NE)
					if($NE["season"] * 1 == $E->A("season") * 1 AND $NE["episode"] * 1 == $E->A("episode") * 1)
						$continue = true;

			if($continue)
				continue;

			$R[] = $E;

			if(!$returnValues) $T->addRow(array("S".$E->A("season")."E".$E->A("episode"), $E->A("name"), $c[0] ? "ok" : "", Util::formatByte($c[1]), $c[2]));

		}

		if($returnValues) return $R;

		echo "<div style=\"overflow:auto;max-height:400px;\">".$T."</div>";
	}
	
	public function downloadEpisodes($echo = true){
		$adapter = new thetvdbcomAdapter();
		$adapter->download($this, $echo);
	}

	public function checkRSS($newFiles = null){
		$Episodes = $this->checkAllEpisodes(true, $newFiles);

		$RF = new RSSFilter($this->A("RSSFilterID"));

		return $RF->filterFor($this, $Episodes);
	}

	public function findNewEpisodes(array $newFiles){
		$found = array();

		foreach($newFiles AS $E){
			if(stripos($E, str_replace(" ", ".", $this->A("name"))) !== false OR ($this->A("altFileName1") != "" AND stripos($E, $this->A("altFileName1")) !== false)){
				preg_match("/s([0-9]+)e([0-9]+)/i", $E, $matches);

				if(count($matches) == 0)
					preg_match("/([0-9]+)x([0-9]+)/i", $E, $matches);

				if(count($matches) > 0) $found[] = array("name" => $this->A("name"), "file" => basename($E), "season" => $matches[1], "episode" => $matches[2], "matches" => $matches, "path" => $E, "pointer" => $this);
			}
		}

		return $found;
	}

	public function newSeriesFromID($name, $language, $seriesID){
		$this->loadMeOrEmpty();

		$this->changeA("name", $name);
		$this->changeA("sprache", $language);
		$this->changeA("siteID", $seriesID);
		
		echo $this->newMe(true, false);

		$this->downloadEpisodes(false);
	}

	public function deleteMe() {
		$AC = new anyC();
		$AC->setCollectionOf("Folge");
		$AC->addAssocV3("SerieID", "=", $this->getID());

		while($F = $AC->getNextEntry())
			$F->deleteMe();

		parent::deleteMe();
	}

	public static function determineQuality($fileName){
		$QS = SerieGUI::getQualities();

		for($i = 2; $i < count($QS); $i++)
			if(strpos($fileName, $QS[$i]) !== false)
				return $QS[$i];

		return $QS[1];
	}
}
?>