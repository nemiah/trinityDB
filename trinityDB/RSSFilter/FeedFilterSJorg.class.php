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

class FeedFilterSJorg implements iFeedFilter {

	public function getLabel() {
		return "serienjunkies.org";
	}

	public function filterFilename(Serie $Series, SimpleXMLElement $item) {
		return trim(str_replace(array("[ENGLISCH]", "[DEUTSCH]"), "", $item->title.""));
	}
	
	public function parseItem(SimpleXMLElement $item) {
		$FE = new FeedEntry();
		$title = $item->title."";

		$FE->quality = "360p";

		if(strpos($title, "[DEUTSCH]") !== false) {
			$title = trim(str_replace("[DEUTSCH]", "", $title));
			$FE->language = "de";
		}
		if(strpos($title, "[ENGLISCH]") !== false) {
			$title = trim(str_replace("[ENGLISCH]", "", $title));
			$FE->language = "en";
		}

		if(strpos($title, "720p") !== false) $FE->quality = "720p";
		if(strpos($title, "1080p") !== false) $FE->quality = "1080p";
		if(strpos($title, "1080i") !== false) $FE->quality = "1080i";

		preg_match("/s([0-9]+)e([0-9]+)/i", $title, $matches);

		$FE->name = trim(str_replace(".", " ", substr($title, 0, strpos($title, $matches[0]))));

		$FE->season = $matches[1];
		$FE->episode = $matches[2];

		$FE->link = $item->link."";

		$FE->title = $item->title."";

		$FE->published = strtotime($item->pubDate."");

		return $FE;
	}

	public function getAvailableHosts(RSSFilter $RSF){
		$available = $RSF->getAvailableHosts();

		$hosts = array();
		if($RSF->A("RSSFilterProviderRapidshare") == "1" AND in_array("Rapidshare.com", $available)) $hosts["Rapidshare.com"] = "rc";
		if($RSF->A("RSSFilterProviderNetload") == "1" AND in_array("Netload", $available)) $hosts["Netload.com"] = "nl";

		return $hosts;
	}

	public function download(RSSFilter $RSF, $filename, $page, $targetFileName){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING , "gzip");
        $content = curl_exec($ch);
        curl_close($ch);

		#$content = file_get_contents($page);
		#die($content);
		$namepos = strpos($content, $filename);
		$lastpos = strpos($content, "</p>", $namepos);

		if($namepos === false)
			return array("I could not find any suitable links, please try downloading the file manually", "");


		$subcontent = substr($content, $namepos, $lastpos - $namepos);
		
		preg_match_all("/<a href=\"([a-zA-Z0-9 \-_\/:\.]*)\"[ a-zA-Z=\"_]*>/", $subcontent, $matches);

		if(count($matches) != 2 OR !isset($matches[1][0]))
			return array("I could not find any suitable links, please try again", $subcontent);

		$usableHosts = $this->getAvailableHosts($RSF);

		if(count($usableHosts) == 0)
			return array("You did not select any hosts to download from", "");

		$JD = new JD($RSF->A("RSSFilterJDID"));

		foreach($matches[1] AS $k => $url){
			if(!in_array(substr(basename($url), 0, 2), $usableHosts))
				continue;

			$JD->download($url);
		}

		return true;
	}
}
?>
