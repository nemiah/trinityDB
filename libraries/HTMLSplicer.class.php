<?php
/*
 *  This file is part of phynx.

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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class HTMLSplicer {
	
	private $url;
	private $vals;
	private $index;
	
	public function __construct(){
	}
	
	public function setUrl($url){
		$this->url = $url;
	}
	
	public function setHTML($html){
		$this->html = $html;
	}
	
	public function setVals($vals){
		$this->vals = $vals;
	}
	
	public function setIndex($index){
		$this->index = $index;
	}
	
	public function parseIt(){
		$xmlP = xml_parser_create();
		xml_parser_set_option($xmlP, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($xmlP, XML_OPTION_SKIP_WHITE, 1);
		
		if($this->url != ""){
			$page = implode("",file($this->url));
			$page = str_replace("&nbsp;","",$page);
			$page = str_replace("&","",$page);
		} else $page = $this->html;
		
		xml_parse_into_struct($xmlP, $page, $this->vals, $this->index);
		
		#echo xml_error_string(xml_get_error_code($xmlP))." on line ".xml_get_current_line_number($xmlP)." column ".xml_get_current_column_number($xmlP);
		
		#if(xml_get_error_code($xmlP)) echo $page;
		#echo "<pre>";
		#echo $page;
		#print_r($this->vals);
		#echo "</pre>";
	}
	
	public function getAsHTML(){
		foreach($this->vals AS $key => $value){
			if($value["tag"] == "img") continue;
			
			$attributes = "";
			if($value["tag"] == "a" AND isset($value["attributes"]))
				foreach($value["attributes"] AS $name => $wert) $attributes .= " $name=\"$wert\"";
			
			
			if($value["type"] == "open") echo "<".$value["tag"].">".(isset($value["value"]) ? $value["value"] : "");
			if($value["type"] == "close") echo "</".$value["tag"].">";
			if($value["type"] == "complete") echo "<".$value["tag"]." $attributes>".(isset($value["value"]) ? $value["value"] : "")."</".$value["tag"].">";
		}
	}
	
	public function getColNumber($ColNumber){
		$content = array();
		$col = 0;
		$line = 0;
		foreach($this->vals AS $key => $value){
			if($col == $ColNumber){
				if(!isset($content[$line])) $content[$line] = "";
				$content[$line] .= (isset($value["value"]) ? trim($value["value"]) : "");
			}
			if($value["tag"] == "td" AND $value["type"] == "close") $col++;
			if($value["tag"] == "td" AND $value["type"] == "complete") $col++;
			if($value["tag"] == "tr" AND ($value["type"] == "close" OR $value["type"] == "complete")) {
				$col = 0;
				$line++;
			}
		}
		
		return $content;
	}
	
	public function getTable($tableNumber){
		
		$t = 0;
		for($i=0;$i<count($this->index["table"]);$i+=2){
			if($t++ < $tableNumber) continue;
			
			$v = $this->index["table"][$i];
			$w = $this->index["table"][$i+1] + 1;
			
			$newVals = array();
			for($j = $v;$j < $w; $j++)
				$newVals[$j] = $this->vals[$j];
			
			$newIndex = array();
			foreach($this->index AS $key => $value)
				foreach($value AS $subKey => $subValue)
					if($subValue >= $v AND $subValue < $w) $newIndex[$key][$subKey] = $subValue;
			
				
			$HP = new HTMLSplicer();
			$HP->setVals($newVals);
			$HP->setIndex($newIndex);
			return $HP;
		}
	}
	
}
?>