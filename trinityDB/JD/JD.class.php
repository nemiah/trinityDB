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
class JD extends PersistentObject {
	public function filesize($link, $resolve = false){
		if($resolve AND $this->A("JDLinkParser") != ""){
			$C = $this->A("JDLinkParser");
			$C = new $C();
			$link = $C->parse($link, $this->A("JDLinkParserUser"), $this->A("JDLinkParserPassword"));
		}
		
		$info = get_headers($link, 1);
		if($info === false)
			return 0;
		
		return $info["Content-Length"];
	}
	
	public function download($link, $logLink = null, $logFilename = "", Serie $Serie = null){
		if(strpos($link, "linksafe.")){
			$newLocation = get_headers($link, 1);
			$links = $newLocation["Location"];
			$link = null;
			
			if(is_array($links)){
				foreach($links AS $k => $l)
					if(stripos($l, "https") === 0)
						$link = $links[$k];
					
				if($link == null)
					$link = $links[0];
			} else
				$link = $links;
		}
		
		if(strpos($link, "safeurl.")){
			
			$newLocation = get_headers($link, 1);
			if(isset($newLocation["Location"])){
				$link = $newLocation["Location"][0];
			
			} else {
				$contentWithLink = file_get_contents($link);
				preg_match_all("/(https:\/\/rapidshare[a-zA-Z0-9\.\-\/_#+\|!]*)/", $contentWithLink, $links);

				$links = array_unique($links[1]);
				$link = $links[0];
				$ex = explode("|", $link);

				$ex[0] = str_replace("/#!download", "/files/", $ex[0]);
				$link = $ex[0].$ex[2]."/".$ex[3];
			}
		}
		
		if(strpos($link, "canhaz.")){
			$newLocation = get_headers($link, 1);
			$link = $newLocation["Location"];

			$contentWithLink = file_get_contents($link);

			preg_match_all("/(http:\/\/rapidshare[a-zA-Z0-9\.\-\/_#+]*)/", $contentWithLink, $links);

			$links = array_unique($links[1]);
			$link = $links[0];
		}
		
		$linkOld = $link;
		if($this->A("JDLinkParser") != ""){
			$C = $this->A("JDLinkParser");
			$C = new $C();
			$link = $C->parse($link, $this->A("JDLinkParserUser"), $this->A("JDLinkParserPassword"));
		}
			
		
		if($this->A("JDDLType") == "4"){
			if($logFilename == ""){
				$info = get_headers($link, 1);
				if($info !== false){
					preg_match("/filename=\"(.*)\"/ismU", $info["Content-Disposition"], $matches);
					if(isset($matches[1]))
						$logFilename = $matches[1];
				}
			}
		
			$DL = anyC::getFirst("Incoming", "IncomingUseForDownloads", "1");
			
			$id = $this->logDownload($logLink, $linkOld, $logFilename, $this->filesize($link), $Serie);
			file_put_contents($this->A("JDWgetFilesDir")."/$id.temp", "-o wgetDL_".str_pad($id, 5, "0", STR_PAD_LEFT).".log -O ".rtrim($DL->A("IncomingDir"), "/")."/".basename($linkOld)." $link");
			rename($this->A("JDWgetFilesDir")."/$id.temp", $this->A("JDWgetFilesDir")."/$id.dl");
			chmod($this->A("JDWgetFilesDir")."/$id.dl", 0666);
			return true;
		}
		
		if($this->A("JDDLType") == "0")
			Util::PostToHost($this->A("JDHost"), $this->A("JDPort"), "/link_adder.tmpl", "none", "do=Add&addlinks=".urlencode($link), $this->A("JDUser"), $this->A("JDPassword"));

		if($this->A("JDDLType") == "1"){
			$xml = Util::PostToHost($this->A("JDHost"), $this->A("JDPort"), "/cgi-bin/Qdownload/DS_Login.cgi", "none", "user=".$this->A("JDUser")."&pwd=".urlencode(base64_encode($this->A("JDPassword")))."&admin=1");

			$xml = new SimpleXMLElement(substr($xml, strpos($xml, "<?xml ")));
			$data = Util::PostToHost($this->A("JDHost"), $this->A("JDPort"), "/cgi-bin/Qdownload/DS_Task_Option.cgi", "none", "url=".urlencode($link)."&todo=add_rs&type=http_ftp&acc_id=1&user=&pwd=&sid=".$xml->authSid."&ver=2.0");
		
			$xml = new SimpleXMLElement(substr($data, strpos($data, "<?xml ")));
			if($xml->Result."" == "success")
				$this->logDownload($logLink, $link, $logFilename, 0, $Serie);

		}

		if($this->A("JDDLType") == "2"){
			
			$content = file_get_contents("http://".$this->A("JDHost").":".$this->A("JDPort")."/action/add/links/grabber0/start1/$link");
			
			if(strpos($content, "Link(s) added. (\"$link\"") !== false AND $logLink != null)
				$this->logDownload($logLink, $link, $logFilename, 0, $Serie);
			
		}

		if($this->A("JDDLType") == "3"){
			$GLOBALS['THRIFT_ROOT'] = Util::getRootPath()."ubiquitous/Thrift";

			require_once $GLOBALS['THRIFT_ROOT'].'/Thrift.php';
			require_once $GLOBALS['THRIFT_ROOT'].'/transport/TTransport.php';
			require_once $GLOBALS['THRIFT_ROOT'].'/transport/TSocket.php';
			require_once $GLOBALS['THRIFT_ROOT'].'/protocol/TBinaryProtocol.php';
			require_once $GLOBALS['THRIFT_ROOT'].'/transport/TFramedTransport.php';
			require_once $GLOBALS['THRIFT_ROOT'].'/transport/TBufferedTransport.php';

			require_once $GLOBALS['THRIFT_ROOT'].'/packages/pyload/Pyload.php';
			require_once $GLOBALS['THRIFT_ROOT'].'/packages/pyload/pyload_types.php';

			$transport = new TSocket($this->A("JDHost"), $this->A("JDPort") * 1);
			$transport->open();

			$protocol = new TBinaryProtocol($transport);

			$client = new PyloadClient($protocol);
			$client->login($this->A("JDUser"), $this->A("JDPassword"));
			#echo $client->getServerVersion();
			#echo "<br />";
			$client->addPackage("trinityDB", array($link), 1);
			#Print 'result = ' . $result;

			$transport->close();
			
		}
	}

	private function logDownload($logLink, $link, $fileName = "", $fileSize = 0, Serie $Serie = null){
		$F = new Factory("JDownload");
		$F->sA("JDownloadURL", $logLink);
		$F->sA("JDownloadFilename", $link);
		$F->sA("JDownloadRenameto", $fileName);
		$F->sA("JDownloadJDID", $this->getID());
		$F->sA("JDownloadSerieID", $Serie != null ? $Serie->getID() : 0);
		
		$E = $F->exists(true);
		if($E === false){
			$F->sA("JDownloadDate", time());
			$F->sA("JDownloadFilesize", $fileSize);
			$id = $F->store();
		} else {
			$E->changeA("JDownloadDate", time());
			$E->changeA("JDownloadFilesize", $fileSize);
			$E->saveMe();
			$id = $E->getID();
		}
		
		return $id;
	}

	public function supportsAutoDownload(){
		if($this->A("JDDLType") == "4") return true;
		if($this->A("JDDLType") == "3") return true;
		if($this->A("JDDLType") == "2") return true;
		if($this->A("JDDLType") == "1") return true;

		return false;
	}

	public function newAttributes() {
		$A = parent::newAttributes();

		$A->JDPort = 8765;

		return $A;
	}
}
?>