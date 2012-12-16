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
class thetvdbcomAdapter extends EpguideAdapter implements iEpguideAdapter {
	private $apiKey = "606C9D5059F782B2";

	public static $mirror;
	public static $serverTime;

	private function getMirror(){
		if(self::$mirror != null) return self::$mirror;
		try {
			$info = @file_get_contents("http://www.thetvdb.com/api/$this->apiKey/mirrors.xml");
			if($info === false)
				throw new Exception();
			
			$mirrorList = new SimpleXMLElement($info);
		} catch(Exception $e){
			throw new Exception("Please test with your browser if the page is available: http://www.thetvdb.com/api/$this->apiKey/mirrors.xml");
		}
		self::$mirror = $mirrorList->Mirror[0]->mirrorpath."";

		return self::$mirror;
	}

	private function getServerTime(){
		if(self::$serverTime != null) return self::$serverTime;

		$content = file_get_contents(self::$mirror."/api/Updates.php?type=none");
		$serverTime = new SimpleXMLElement($content);
		self::$serverTime = $serverTime->Time."";

		return self::$serverTime;
	}

	public function getInfo($name, $language = "en"){
		try {
			$mirrorPath = $this->getMirror();
		} catch(Exception $e){
			Red::errorD($e->getMessage());
		}
		$content = file_get_contents("$mirrorPath/api/GetSeries.php?seriesname=".urlencode($name)."&language=$language");
		$seriesInfo = new SimpleXMLElement($content);

		if(!isset($seriesInfo->Series)) return null;

		$c = new stdClass();

		$c->Banner = $mirrorPath."/banners/".$seriesInfo->Series->banner."";
		$c->Overview = $seriesInfo->Series->Overview."";
		$c->SeriesID = $seriesInfo->Series->seriesid."";
		$c->Name = $seriesInfo->Series->SeriesName."";

		return $c;
	}

	public function download(Serie $S, $echo = false){
		if($echo) $tab = new HTMLTable(1);

		$mirrorPath = $this->getMirror();
		if($echo) $tab->addRow("Retrieving mirror list... using $mirrorPath");

		$serverTime = $this->getServerTime();
		if($echo) $tab->addRow("Retrieving server time... $serverTime");
		$S->changeA("lastupdate", $serverTime);
		
		if($S->A("siteID") == 0){
			if($echo) $tab->addRow("Retrieving series information...");
			
			$data = file_get_contents("$mirrorPath/api/GetSeries.php?seriesname=".urlencode($S->A("name"))."&language=".$S->A("sprache"));
			if($data === false)
				throw new Exception("No data from $mirrorPath/api/GetSeries.php?seriesname=".urlencode($S->A("name"))."&language=".$S->A("sprache"));
			#die("DATA: $data");
			$seriesInfo = new SimpleXMLElement($data, null, false);
			$seriesID = $seriesInfo->Series->seriesid;
			$S->changeA("siteID", $seriesID);
			#$S->changeA("description", $seriesInfo->Series->Overview);
		}
		else
			$seriesID = $S->A("siteID");

		$tempFile = Util::getTempFilename("SerieID".$S->getID(), "zip");

		if($echo) $tab->addRow("Downloading episodes information...");
		$SZip = "$mirrorPath/api/$this->apiKey/series/$seriesID/all/".$S->A("sprache").".zip";
		if(!copy($SZip, $tempFile))
			Red::errorD("The download of $SZip failed!");

		try {
			$zip = new ZipArchive;
			if ($zip->open($tempFile) === TRUE) {
				$zip->extractTo(dirname($tempFile)."/SerieID".$S->getID());
				$zip->close();
			} else
				throw new ClassNotFoundException("");
		} catch (ClassNotFoundException $e){
			if(!Util::isWindowsHost()) $commandUnzip = "unzip -o $tempFile -d SerieID".$S->getID();
			else $commandUnzip = Util::getRootPath ()."trinityDB/Serien/unzip.exe -o $tempFile -d SerieID".$S->getID();
			if($echo) $tab->addRow("Extracting data...<br />$commandUnzip");
			$sc = new SystemCommand();
			$sc->setCommand("cd ".dirname($tempFile)." && $commandUnzip");
			$sc->execute();
		}

		$e = 0;
		$u = 0;
		$file = dirname($tempFile)."/SerieID".$S->getID()."/".$S->A("sprache").".xml";
		if(!file_exists($file))
			Red::errorD("Could not find the expected file $file. Please check if it was properly extracted from $tempFile.");

		$episodesList = new SimpleXMLElement(file_get_contents($file));
		$status = $episodesList->Series->Status;
		$S->changeA("description", $episodesList->Series->Overview);
		$S->changeA("status", $status);
		$S->changeA("genre", $episodesList->Series->Genre);

		$S->saveMe(true, false);
		
		foreach($episodesList->Episode AS $k => $v){
			$F = new Factory("Folge");

			$F->sA("episodeID", $v->id);

			if(($E = $F->exists(true))) {
				if($v->lastupdated == $E->A("lastupdate")) continue;

				$E->changeA("name", $v->EpisodeName);
				$E->changeA("airDate", $v->FirstAired);
				$E->changeA("lastupdate", $v->lastupdated);
				$E->changeA("description", $v->Overview);

				$E->saveMe(true, false);
				
				$u++;

				continue;
			}

			$F->sA("SerieID", $S->getID());
			$F->sA("season", $v->SeasonNumber);
			$F->sA("episode", $v->EpisodeNumber);
			$F->sA("name", $v->EpisodeName);
			$F->sA("airDate", $v->FirstAired);
			$F->sA("lastupdate", $v->lastupdated);
			$F->sA("description", $v->Overview);
			$F->sA("wanted", "1");

			$F->store(true, false);
			$e++;
		}


		if($echo) $tab->addRow("Loaded $e episodes");
		if($echo) $tab->addRow("Updated $u episodes");

		if(mUserdata::getGlobalSettingValue("trinityDBdlCover", "0") == "1"){
			$bannerList = new SimpleXMLElement(dirname($tempFile)."/SerieID".$S->getID()."/banners.xml", null, true);

			foreach($bannerList AS $banner){
				if($banner->BannerType."" == "poster"){
					#echo $banner->BannerPath."";
					copy("http://www.thetvdb.com/banners/".$banner->BannerPath, $S->A("dir")."/Folder.jpg");
					if($echo) $tab->addRow("Downloaded cover");
					break;
				}
			}
		}

		if($echo) echo $tab;
	}

	public function update($echo = false){
		$oldest = new anyC();
		$oldest->setCollectionOf("Serie");
		$oldest->addOrderV3("lastupdate", "ASC");
		$oldest->addAssocV3("status", "=", "Continuing");
		$oldest->addAssocV3("lastupdate", "<", time() - 3600 * 24 * 3);
		$oldest->setLimitV3("2");

		while($S = $oldest->getNextEntry())
			$this->download($S, $echo);
		

		$oldest = new anyC();
		$oldest->setCollectionOf("Serie");
		$oldest->addOrderV3("lastupdate", "ASC");
		$oldest->addAssocV3("status", "=", "Ended");
		$oldest->addAssocV3("lastupdate", "<", time() - 3600 * 24 * 21);
		$oldest->setLimitV3("1");

		while($S = $oldest->getNextEntry())
			$this->download($S, $echo);


		$oldest = new anyC();
		$oldest->setCollectionOf("Serie");
		$oldest->addOrderV3("lastupdate", "ASC");
		$oldest->addAssocV3("lastupdate", "<", time() - 3600 * 24 * 3, "AND", "1");
		$oldest->addAssocV3("status", "=", "Continuing", "AND", "1");

		$oldest->addAssocV3("lastupdate", "<", time() - 3600 * 24 * 21, "OR", "2");
		$oldest->addAssocV3("status", "=", "Ended", "AND", "2");
		$oldest->lCV3();
		
		return $oldest->numLoaded();

	}
}
?>