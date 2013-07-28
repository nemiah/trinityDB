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
session_name("ExtConnFF");
error_reporting(E_ALL);

$absolutePathToPhynx = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..").DIRECTORY_SEPARATOR;

require_once $absolutePathToPhynx."system/connect.php";

#if(function_exists('date_default_timezone_set'))
#	date_default_timezone_set('Europe/Berlin');



require_once $absolutePathToPhynx."classes".DIRECTORY_SEPARATOR."frontend".DIRECTORY_SEPARATOR."ExtConn.class.php";

$e = new ExtConn($absolutePathToPhynx);
$e->useDefaultMySQLData();
$e->useUser();

/*require_once $absolutePathToPhynx."trinityDB/Serien/Serie.class.php";
require_once $absolutePathToPhynx."trinityDB/Serien/SerieGUI.class.php";
require_once $absolutePathToPhynx."trinityDB/Serien/Folge.class.php";
require_once $absolutePathToPhynx."trinityDB/Serien/iEpguideAdapter.class.php";
require_once $absolutePathToPhynx."trinityDB/Serien/EpguideAdapter.class.php";
require_once $absolutePathToPhynx."trinityDB/Serien/thetvdbcomAdapter.class.php";
require_once $absolutePathToPhynx."trinityDB/Incoming/Incoming.class.php";
require_once $absolutePathToPhynx."trinityDB/Incoming/mIncoming.class.php";*/
require_once $absolutePathToPhynx."classes/backend/File.class.php";
require_once $absolutePathToPhynx."plugins/Files/mFile.class.php";
require_once $absolutePathToPhynx."classes/backend/FileStorage.class.php";

require_once $absolutePathToPhynx."classes/frontend/HTMLInput.class.php";

/*require_once $absolutePathToPhynx."trinityDB/RSSFilter/RSSFilter.class.php";
require_once $absolutePathToPhynx."trinityDB/RSSFilter/iFeedFilter.class.php";
require_once $absolutePathToPhynx."trinityDB/RSSFilter/FeedEntry.class.php";
require_once $absolutePathToPhynx."trinityDB/RSSFilter/FeedFilterSJorg.class.php";
require_once $absolutePathToPhynx."trinityDB/RSSFilter/FeedFilter1DDL.class.php";
require_once $absolutePathToPhynx."trinityDB/RSSFilter/FeedFilterRM.class.php";
require_once $absolutePathToPhynx."trinityDB/JD/JD.class.php";
require_once $absolutePathToPhynx."trinityDB/JD/JDownload.class.php";*/


addClassPath($absolutePathToPhynx."trinityDB/Serien/");
addClassPath($absolutePathToPhynx."trinityDB/RSSFilter/");
addClassPath($absolutePathToPhynx."trinityDB/Incoming/");
addClassPath($absolutePathToPhynx."trinityDB/JD/");

$RSF = new RSSFilter($_GET["RSSFilterID"]);
$Adapter = $RSF->A("RSSFilterAdapter");
$Adapter = new $Adapter();

if(isset($_GET["manualMultiDL"])){
	$added = "";
	
	$ILinks = array();
	$ILinks[] = new HTMLInput("manualMultiDL", "hidden", "true");
	$ILinks[] = new HTMLInput("RSSFilterID", "hidden", $_GET["RSSFilterID"]);
	
	for($i = 1; $i < 2; $i++){
		$ILink = new HTMLInput("manualMultiDL$i", "textarea");
		
		$ILink->id("manualMultiDL$i");
		$ILink->style("border:1px solid grey;width:400px;padding:2px;margin-bottom:5px;height:200px;");

		$ILinks[] = $ILink;

		if(isset($_GET["manualMultiDL$i"]) AND $_GET["manualMultiDL$i"] != ""){
			$exLinks = explode("\n", $_GET["manualMultiDL$i"]);
			#print_r($links);
			foreach($exLinks AS $l){
				$result = $RSF->download(trim($l));
				$added .= $l." added<br />";
			}
		}
	}
	
	
	
	$IGo = new HTMLInput("go", "submit", "Go");
	$IGo->style("border:1px solid grey;margin-left:10px;padding:2px;");
	#$IGo->onclick("document.location.href='?RSSFilterID=$_GET[RSSFilterID]&manualMultiDL=true&manualMultiDL1='+document.getElementById('manualMultiDL1').value;");#+'&manualMultiDL2='+document.getElementById('manualMultiDL2').value+'&manualMultiDL3='+document.getElementById('manualMultiDL3').value+'&manualMultiDL4='+document.getElementById('manualMultiDL4').value+'&manualMultiDL5='+document.getElementById('manualMultiDL5').value

	$html = emoFatalError("Manual Download</h1><p>Please input the links here one per line:</p><form action=\"./FilteredFeed.php\" method=\"get\">".implode(" ", $ILinks).$IGo."</form>"."<h1>", $added, "trinityDB automatic downloader", false, false);

	die($html);
}

if((isset($_GET["getLink"]) AND isset($_GET["fromPage"])) OR isset($_GET["manualDL"])){
	if(isset($_GET["manualDL"])){
		$result = $RSF->download($_GET["manualDL"]);
		$dled = $_GET["manualDL"];
	} else {
		$result = $Adapter->download($RSF, $_GET["getLink"], $_GET["fromPage"]);
		$dled = $_GET["getLink"];
	}

	if($result !== true){
		$ILink = new HTMLInput("manualDL");
		$ILink->id("manualDL");
		$ILink->style("border:1px solid grey;width:400px;padding:2px;");
		
		$IGo = new HTMLInput("go", "button", "Go");
		$IGo->style("border:1px solid grey;margin-left:10px;padding:2px;");
		$IGo->onclick("if(document.getElementById('manualDL').value != '') document.location.href='?RSSFilterID=$_GET[RSSFilterID]&manualDL='+document.getElementById('manualDL').value; else alert('Please input a link!');");

		$html = emoFatalError($result[0]."</h1><p>Look for <b>".basename($dled)."</b></p><p>Please input the link here: ".$ILink.$IGo."</p><h1>", $result[1], "trinityDB automatic downloader", false, false);

		$html = str_replace("<!-- MORE SPACE -->", "<iframe src=\"$_GET[fromPage]\" style=\"width:100%;border:0px;height:550px;margin-top:5px;\"></iframe>", $html);


		die($html);
	} else {
		echo "<!DOCTYPE html>
		<html lang=\"en\">
			<head>
				<meta charset=\"utf-8\" />
				<title>trinityDB automatic downloader</title>".RSSFilter::getStyle()."
			</head>
			<body>
				<div class=\"backgroundColor0\">
					<h1>Download OK</h1>
					<p>".basename($dled)."</p>
					<p>From <a href=\"$_GET[fromPage]\">$_GET[fromPage]</a></p>
				</div>
			</body>
		</html>";
	}
	exit;
}

$tvdbUnavailable = false;
$tvdb = new thetvdbcomAdapter();
$nonupdated = 0;
try {
	$nonupdated = $tvdb->update();
} catch (Exception $ex){
	$tvdbUnavailable = $ex->getMessage();
}
#print_r($_SERVER);
$link = "http".(strpos($_SERVER["SERVER_PROTOCOL"], "https") ? "s" : "")."://".$_SERVER["HTTP_HOST"].str_replace("/trinityDB/RSSFilter/FilteredFeed.php", "", $_SERVER["SCRIPT_NAME"]);


echo('<?xml version="1.0" encoding="UTF-8"?>'); ?>

<rss version="2.0">
	<channel>
		<title>trinityDB filtered feed</title>
		<link><?php echo $link; ?></link>
		<language>en-gb</language>
		<description></description>
		<pubDate><?php echo(date("r")); ?></pubDate>

		<image>
			<url><?php echo $link."/trinityDB/Serien/trinityDB.png"; ?></url>
			<title>trinityDB filtered feed</title>
			<link><?php echo $link; ?></link>
		</image>

 
<?php
if($tvdbUnavailable !== false)
	echo "
		<item>
			<title>The TV DB is currently unavailable</title>
			<description><![CDATA[$tvdbUnavailable]]></description>
			<link>$link</link>
		</item>";

$Incoming = new mIncoming();
$newFiles = $Incoming->getNewFiles();

$ac = new anyC();
$ac->setCollectionOf("Serie");
$ac->addAssocV3("RSSFilterID", "=", $_GET["RSSFilterID"]);
$ac->addAssocV3("status", "=", "Continuing");
$series = "";

while($S = $ac->getNextEntry()){
	try {
		$C = $S->checkRSS($newFiles);
		foreach($C AS $En){
			if($series != $S->A("name") AND $series != ""){
				echo "
			<item>
				<title>-----------------------------------</title>
				<link>$link/trinityDB/RSSFilter/FilteredFeed.php?RSSFilterID=$_GET[RSSFilterID]</link>
			</item>";
			}
			$series = $S->A("name");

			$DLLink = "$link/trinityDB/RSSFilter/FilteredFeed.php?RSSFilterID=$_GET[RSSFilterID]&amp;fromPage=".urlencode($En["link"])."&amp;getLink=".urlencode($En["fileName"]);
			if($RSF->A("RSSFilterJDID") == "0")
				$DLLink = $En["link"];

			$usedAutoDL = false;
			$alreadyDLed = JDownload::testDownloaded($En["fileName"]);
			if(!$alreadyDLed)
				$usedAutoDL = $RSF->autoDownload($En["fileName"], $En["link"], $S->A("name")." S".$En["season"]."E".$En["episode"]);


			echo "
			<item>
				<title>".$S->A("name")." S".$En["season"]."E".$En["episode"]."".(($alreadyDLed OR $usedAutoDL) ? " OK" : "")."</title>
				<description><![CDATA[".$En["description"]."]]></description>
				<pubDate>$En[pubDate]</pubDate>
				<link>$DLLink</link>
			</item>";
		}
	} catch(Exception $ex){
		echo "
		<item>
			<title>Error: ".$ex->getMessage()."</title>
		</item>";
	}
}
	$DLLink = "$link/trinityDB/RSSFilter/FilteredFeed.php?RSSFilterID=$_GET[RSSFilterID]&amp;manualMultiDL=true";
	echo "
		<item>
			<title>Manual download</title>
			<description><![CDATA[]]></description>
			<pubDate></pubDate>
			<link>$DLLink</link>
		</item>
		<item>
			<title>Not yet updated series: $nonupdated</title>
			<description><![CDATA[]]></description>
			<pubDate></pubDate>
			<link>$link</link>
		</item>";
	?>
	</channel>
</rss>
<?php 
#$e->cleanUp();
?>