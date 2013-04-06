<?php
/*
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
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */

if(isset($argv[1]))
	$_GET["cloud"] = $argv[1];

if(isset($argv[2]))
	$_SERVER["HTTP_HOST"] = $argv[2];

session_name("ExtConnJD");

require_once realpath(dirname(__FILE__)."/../../system/connect.php");


$absolutePathToPhynx = realpath(dirname(__FILE__)."/../../")."/";

$e = new ExtConn($absolutePathToPhynx);

$e->addClassPath($absolutePathToPhynx."trinityDB/JD");
$e->addClassPath($absolutePathToPhynx."ubiquitous/prettifyDB");
$e->addClassPath(FileStorage::getFilesDir());

$e->useDefaultMySQLData();

$e->useUser();

$F = new RSSFeed("trinityDB downloads", "", "", "en-GB", "");

$AC = anyC::get("JDownload");
$AC->addAssocV3("JDownloadDate", ">", time() - 3600 * 24 * 7);
$AC->addOrderV3("JDownloadDate", "DESC");

while($D = $AC->getNextEntry())
	$F->addEntry($newName = prettifyDB::apply("seriesEpisodeNameDownloaded", $D->A("JDownloadURL")), "", "", "", $D->getID(), date("r", $D->A("JDownloadDate")));


echo $F;

$e->cleanUp();
?>