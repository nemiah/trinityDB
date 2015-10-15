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

class FeedFilterDDLV implements iFeedFilter {
	private static $links = array();
	public function getLabel() {
		return "ddlvalley.cool";
	}

	public function filterFilename(Serie $Series, SimpleXMLElement $item) {
		$FE = $this->parseItem($item);

		return $FE->filename[array_search(SerieGUI::getQualities($Series->A("quality")), $FE->quality)];
	}


	public function parseItem(SimpleXMLElement $item) {
		#print_r($item);
		$FE = new FeedEntry();
		$title = $item->title."";

		$FE->title = $item->title."";
		
		$FE->filename = explode(" & ", $FE->title);

		$qs = array();
		foreach($FE->filename AS $file)
			$qs[] = Serie::determineQuality($file);
		$FE->quality = $qs;

		$FE->language = "en";

		self::$links[$item->link.""] = array();
		foreach($item->enclosure AS $link)
			self::$links[$item->link.""][] = $link->attributes()->url."";
		#debug_print_backtrace();
		#print_r($FE->links);
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
		#if($RSF->A("RSSFilterProviderRapidshare") == "1" AND in_array("Rapidshare.com", $available)) $hosts["Rapidshare.com"] = "Rapidshare";
		#if($RSF->A("RSSFilterProviderNetload") == "1" AND in_array("Netload", $available)) $hosts["Netload.com"] = "netload";
		if($RSF->A("RSSFilterProviderUploaded") == "1" AND in_array("Uploaded", $available)) $hosts["Uploaded.to"] = "uploaded";

		return $hosts;
	}

	public function download(RSSFilter $RSF, $filename, $page, $targetFileName, Serie $Serie){
		#s$args = func_get_args();
		#print_r($args);
		#print_r(self::$links[$page]);
		#die();
		#return false;
	
        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_ENCODING , "gzip");
        $content = curl_exec($ch);
        curl_close($ch);
		#echo $page;
		#$content = file_get_contents($page);
		#die($content);
		$namepos = strpos($content, "<p><onelink /></p>");
		$lastpos = strpos($content, "<p><download /></p>", $namepos);

		if($namepos === false){
			$namepos = strpos($content, "<p><onelink/></p>");
			$lastpos = strpos($content, "<p><download/></p>", $namepos);
		}
		
		if($namepos === false)
			return array("I could not find any suitable links, please try downloading the file manually", "");


		$subcontent = substr($content, $namepos, $lastpos - $namepos);
		$middle = strpos($subcontent, $filename);
		if($middle !== false)
			$subcontent = substr($content, $namepos + $middle, $lastpos - ($namepos + $middle));
		else {
			$lastpos2 = strpos($content, "<p><download /></p>", $lastpos + 20);
			if($lastpos2 === false)
				$lastpos2 = $lastpos;
			
			$subcontent = substr($content, $namepos, $lastpos2 - $namepos);
			$middle = strpos($subcontent, $filename);
			$subcontent = substr($content, $namepos + $middle, $lastpos2 - ($namepos + $middle));
		}
		
		preg_match_all("/<strong>([a-zA-Z0-9\(\)]*)[a-zA-Z0-9: \(\)]*<\/strong><br\s*\/>\s*\<a\s*href\=\"([a-zA-Z0-9 \-_\/:\.]*)\"[ a-zA-Z=\"_]*>/", $subcontent, $matches);

		#print_r($matches);
		
		if(count($matches) != 3 OR !isset($matches[1][0]))
			return array("I could not find any suitable links, please try again", "");
*/
		
		$usableHosts = $this->getAvailableHosts($RSF);

		if(count($usableHosts) == 0)
			return array("You did not select any hosts to download from", "");

		

		$JD = new JD($RSF->A("RSSFilterJDID"));
		foreach(self::$links[$page] AS $link){
			$url = parse_url($link);
			#print_r($url);
			#echo $link."\n";
			$found = false;
			foreach($usableHosts AS $host){
				if(stripos($url["host"], $host) === false)
					continue;
				#echo Serie::determineQuality($link);
				#echo $Serie->A("quality");
				if(Serie::determineQuality($link) != SerieGUI::getQualities($Serie->A("quality")))
					continue;
				
				$found = $link;
			}
			
			if(!$found)
				continue;
			
			$JD->download($link, $filename, $targetFileName, $Serie);
			break;
		}

		return true;
	}
}
?>