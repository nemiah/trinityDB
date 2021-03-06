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
class RSSFilter extends PersistentObject {
	public static $feeds = array();

	function  __construct($ID) {
		parent::__construct($ID);
		
	}
	/**
	 *
	 * @return iFeedFilter
	 */
	public function getFeedAdapter(){
		$c = $this->A("RSSFilterAdapter");

		return new $c();
	}

	public function filterNew(){
		$this->loadFeed();

		$xml = RSSFilter::$feeds[$this->A("RSSFilterFeed")];
		$Adapter = $this->getFeedAdapter();

		$ac = new anyC();
		$ac->setCollectionOf("Serie");

		$Serien = array();
		while($S = $ac->getNextEntry()){
			$Serien[strtolower($S->A("name"))] = $S;
			$Serien[strtolower($S->A("altFeedName1"))] = $S;
		}

		$new = array();

		foreach($xml->channel->item AS $v){

			$Epis = $Adapter->parseItem($v);
			if(!isset($Serien[strtolower($Epis->name)]) AND $Epis->name != "")
				$new[strtolower($Epis->name)] = $Epis;
		}

		return $new;
	}

	public function loadFeed(){
		if(!isset(RSSFilter::$feeds[$this->A("RSSFilterFeed")])){
			RSSFilter::$feeds[$this->A("RSSFilterFeed")] = "<phynx></phynx>";
			$content = @file_get_contents($this->A("RSSFilterFeed"));
			if($content === false)
				throw new Exception($this->A("RSSFilterFeed")." could not be loaded!");
			
			$content = str_replace("", "", $content);
			
			try  {
				libxml_use_internal_errors(true);
				RSSFilter::$feeds[$this->A("RSSFilterFeed")] = new SimpleXMLElement($content);
			} catch (Exception $e){
				try {
					$config = array(
						'indent' => true,
						'clean' => true,
						'input-xml'  => true,
						'output-xml' => true,
						'wrap'       => false
						);
					
					$tidy = new Tidy();
					$xml = $tidy->repairString($content, $config);
					
					RSSFilter::$feeds[$this->A("RSSFilterFeed")] = new SimpleXMLElement($xml);
				} catch(ClassNotFoundException $e){
					throw new Exception($this->A("RSSFilterFeed")." contains errors, but Tidy not found!");
				} catch(Exception $e){
					
					#$errors = "";
					#foreach(libxml_get_errors() as $error) 
					#	print_r($error->message);
					
					throw new Exception($this->A("RSSFilterFeed")." contained errors even Tidy could not fix!");
				}
				#$errors = "";
				#foreach(libxml_get_errors() as $error) 
				#	print_r($error->message);
				
				#throw new Exception($this->A("RSSFilterFeed"));
			}
			
		}
	}

	/**
	 * @param Serie $Series
	 * @param Folge[] $Episodes 
	 */
	public function filterFor(Serie $Series, $Episodes){

		$filtered = array();
		$foundEpisodes = array();

		$this->loadFeed();

		$Adapter = $this->getFeedAdapter();

		$xml = RSSFilter::$feeds[$this->A("RSSFilterFeed")];

		if($xml === "null") return $filtered;

		foreach($xml->channel->item AS $v){
			#print_r($v);
			foreach($Episodes AS $E){
				$title = $v->title."";

				if(strpos($title, "[ENGLISCH]") !== false OR strpos($title, "[DEUTSCH]") !== false){
					if($Series->A("sprache") == "en" AND strpos($title, "[ENGLISCH]") === false) continue;
					if($Series->A("sprache") == "de" AND strpos($title, "[DEUTSCH]") === false) continue;
				}
				
				$ts01e01 = stripos($title, "S".($E->A("season") < 10 ? "0" : "").$E->A("season")."E".($E->A("episode") < 10 ? "0" : "").$E->A("episode"));
				$t01x01 = stripos($title, $E->A("season")."×".($E->A("episode") < 10 ? "0" : "").$E->A("episode"));
				$t01ix01 = stripos($title, $E->A("season")."x".($E->A("episode") < 10 ? "0" : "").$E->A("episode"));
				
				if($ts01e01 === false AND $t01x01 === false AND $t01ix01 === false)
					continue;

				if(
					strpos(strtolower($title), strtolower(str_replace(" ", ".", $Series->A("name")))) === false 
					AND strpos(strtolower($title), strtolower($Series->A("name"))) === false 
					AND ($Series->A("altFeedName1") == "" OR strpos(strtolower($title), strtolower(str_replace(" ", ".", $Series->A("altFeedName1")))) === false )) continue;

				if($Series->A("quality") > 1 AND strpos(strtolower($title), strtolower(SerieGUI::getQualities($Series->A("quality")))) === false) continue;

				$continue = false;
				if($Series->A("quality") == "1"){
					foreach(SerieGUI::getQualities() AS $k => $q){
						if($k < 2) continue;
						
						if(strpos(strtolower($title), strtolower($q)) !== false)
							$continue = true;
					}
				}
				if($continue) continue;

				if(isset($foundEpisodes[$E->getID()])) continue;
				$foundEpisodes[$E->getID()] = true;
				
				$filtered[] = array("title" => $title, "link" => $v->link."", "pubDate" => $v->pubDate."", "description" => $v->description."", "fileName" => $Adapter->filterFilename($Series, $v), "season" => ($E->A("season") < 10 ? "0" : "").$E->A("season"), "episode" => ($E->A("episode") < 10 ? "0" : "").$E->A("episode"));
				#print_r($filtered);
				#echo $title."<br />";
			}
		}

		return $filtered;
	}

	public function getAvailableHosts(){
		return array("Rapidshare.com", "Netload", "Uploaded");
	}

	public function download($link){
		$JD = new JD($this->A("RSSFilterJDID"));
		try {
			$JD->download($link, $link);
		} catch(Exception $e){}
		
		return true;
	}

	public function autoDownload($filename, $page, $targetFileName, Serie $Serie = null){
		if($this->A("RSSFilterAutoDL") == "0")
			return false;
		
		#$JD = new JD($this->A("RSSFilterJDID"));
		#if(!$JD->supportsAutoDownload())
		#	return false;

		$Adapter = $this->A("RSSFilterAdapter");
		$Adapter = new $Adapter();

		$Adapter->download($this, $filename, $page, $targetFileName, $Serie);

		return true;
	}

	public static function getStyle(){
		return "
				<style type=\"text/css\">
					* {
						padding:0px;
						margin:0px;
					}

					body {
						font-size:0.8em;
						font-family:sans-serif;
						background-color:#d8d8d8;
						color:black;
					}

					p {
						padding:5px;
					}

					div {
						padding:10px;
					}

					li {
						font-size:0.8em;
					}

					ol {
						margin-left:10px;
					}

					h2 {
						margin-top:20px;
						clear:both;
					}

					pre {
						font-size:10px;
					}

					.backgroundColor0 {
						background-color:white;
					}
				</style>";
	}
}
?>