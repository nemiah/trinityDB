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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class LPPremiumize implements iLinkParser {
	function getLabel(){
		return "premiumize.me";
	}

	public function parse($link, $username, $password) {
		$ch = curl_init("https://www.premiumize.me/api/transfer/directdl?apikey=".urlencode($password));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "src=".urlencode($link));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
		
		return $response->location;
		
	}
	
	public static function findFolder($password, $seriesName){
		
		$ch = curl_init("https://www.premiumize.me/api/folder/list?includebreadcrumbs=false&apikey=".urlencode($password));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$data = json_decode(curl_exec($ch));
		curl_close($ch);
		
		foreach($data->content AS $dir)
			if($dir->name == $seriesName)
				return $dir->id;
		
		
		$ch = curl_init("https://www.premiumize.me/api/folder/create?apikey=".urlencode($password));
		curl_setopt($ch, CURLOPT_POSTFIELDS, "name=".urlencode($seriesName)."");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = json_decode(curl_exec($ch));
		curl_close($ch);
		
		return $data->id;
	}
}

?>