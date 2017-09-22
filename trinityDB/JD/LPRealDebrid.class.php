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
class LPRealDebrid implements iLinkParser {
	function getLabel(){
		return "real-debrid.fr";
	}

	public function parse($link, $username, $password) {
		$ch = curl_init("https://api.real-debrid.com/rest/1.0/unrestrict/link");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.urlencode($username)));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "link=".urlencode($link));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = json_decode(curl_exec($ch));
		
		if(isset($output->error))
			throw new Exception ("RealDebrid: ".$output->error);
		
		return $output->download;
	}
}
?>