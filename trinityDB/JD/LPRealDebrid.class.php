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
		$ckfile = Util::getTempFilename("RDCookies", "txt");
		
		$ch = curl_init("http://real-debrid.fr/ajax/login.php?user=".urlencode($username)."&pass=".urlencode($password)."&captcha_challenge=&captcha_answer=&time=".time());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
		
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
		
		#print_r($response);
		
		$ch = curl_init("http://real-debrid.fr/ajax/unrestrict.php?link=".urlencode($link)."&password=&remote=0&time=".time());
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:25.0) Gecko/20100101 Firefox/25.0'));
		curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = json_decode(curl_exec($ch));
		
		unlink($ckfile);
		return $output->main_link;
		
	}
}

?>