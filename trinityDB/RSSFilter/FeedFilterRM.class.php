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

class FeedFilterRM implements iFeedFilter {

	public function getLabel() {
		return "rapidmoviez.com";
	}

	public function filterFilename(Serie $Series, SimpleXMLElement $item) {
		$FE = $this->parseItem($item);

		return $FE->filename;#[array_search(SerieGUI::getQualities($Series->A("quality")), $FE->quality)];
	}


	public function parseItem(SimpleXMLElement $item) {
		#print_r($item);
		$FE = new FeedEntry();
		$title = $item->title."";

		$FE->title = $item->title."";
		
		$FE->filename = $FE->title;

		#$qs = array();
		#foreach($FE->filename AS $file)
		#	$qs[] = Serie::determineQuality($file);
		$FE->quality = Serie::determineQuality($FE->filename);

		$FE->language = "en";


		preg_match("/s([0-9]+)e([0-9]+)/i", $title, $matches);

		$FE->name = trim(str_replace(".", " ", substr($title, 0, strpos($title, $matches[0]))));

		$FE->season = $matches[1];
		$FE->episode = $matches[2];

		$FE->link = $item->link."";


		$FE->published = strtotime($item->pubDate."");

		return $FE;
	}

	public function getAvailableHosts(RSSFilter $RSF){
		$available = $RSF->getAvailableHosts();

		$hosts = array();
		if($RSF->A("RSSFilterProviderRapidshare") == "1" AND in_array("Rapidshare.com", $available)) $hosts["Rapidshare.com"] = "Rapidshare";
		#if($RSF->A("RSSFilterProviderNetload") == "1" AND in_array("Netload", $available)) $hosts["Netload.com"] = "nl";

		return $hosts;
	}

	public function download(RSSFilter $RSF, $filename, $page){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		#curl_setopt($ch, CURLOPT_ENCODING , "gzip");
        $content = curl_exec($ch);
        curl_close($ch);
		
		$content = file_get_contents($page);
		
		preg_match("/\<script type\=\"text\/javascript\" src\=\"http:\/\/rapidmoviez.com\/j\/([0-9]*)\/script-58.js\"\>\<\/script\>/", $content, $matches);
		
		$script = file_get_contents("http://rapidmoviez.com/j/$matches[1]/script-58.js");
		$script = str_replace("eval(String.fromCharCode(", "", $script);
		$script = preg_replace("/\)\)$/", "", $script);
		
		$script = explode(",", $script);
		
		$script = array_map("chr", $script);
		$text = implode("", $script);
		
		preg_match_all("/txt = '';(.*)\(/ismU", $text, $matches);
		
		
		foreach($matches[1] AS $k => $s){
			$s = str_replace("';txt += '", "", $s);
			$s = str_replace("txt += '", "", $s);
			$matches[1][$k] = str_replace("';txt += \"\\n\";$", "", $s);
		}
		
		$RSLink = null;
		
		foreach($matches[1] AS $link){
			if(stripos($link, "rapidshare") === false)
				continue;
			
			$RSLink = explode("';txt += \"\\n\";", $link);
		}
		
		

		#if(count($matches) != 3 OR !isset($matches[1][0]))
		#	return array("I could not find any suitable links, please try again", "");

		$usableHosts = $this->getAvailableHosts($RSF);
		
		if(count($usableHosts) == 0)
			return array("You did not select any hosts to download from", "");

		$JD = new JD($RSF->A("RSSFilterJDID"));
		
		if($RSLink != null AND isset($usableHosts["Rapidshare.com"]))
			foreach($RSLink AS $l)
			$JD->download($l, $filename);
		
		return true;
	}
}
?>