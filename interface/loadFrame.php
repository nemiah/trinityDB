<?php
/*
 *  This file is part of phynx.

 *  phynx is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  phynx is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */


require "../system/connect.php";
#die("error:GlobalMessages.E007('$_GET[p]')");
#$output = new Output('auto', true, false);

if($_SESSION["S"]->checkIfUserLoggedIn() == true) {
	#setcookie(session_name(),"",time()-1000);
	#echo session_id();
	die("-1");
}
if(!$_SESSION["S"]->checkIfUserIsAllowed($_GET["p"])) die("error:GlobalMessages.E006");

if(!$_SESSION["S"]->isUserAdmin()) $userHiddenPlugins = mUserdata::getHiddenPlugins();
if(isset($userHiddenPlugins[$_GET["p"]])) die("error:GlobalMessages.E005");

if(isset($_GET["bps"]))
	$_SESSION["BPS"]->setByString($_GET["bps"]);

$_GET["p"] = str_replace("GUI","",$_GET["p"]);
$n = $_GET["p"]."GUI";
try {
	if(!$_SESSION["CurrentAppPlugins"]->isPluginGeneric($_GET["p"])){
		$b = new $n((isset($_GET["id"]) ? $_GET["id"] : "-1"));
	} else {
		$n = ($_GET["p"][0] == "m" ? "m" : "")."GenericGUI";
		$b = new $n((isset($_GET["id"]) ? $_GET["id"] : "-1"), $_GET["p"]);
	}
} catch (ClassNotFoundException $e){
	die("error:GlobalMessages.E009('$n')");
}

if(!PMReflector::implementsInterface($n,"iGUIHTMLMP2")
	AND !PMReflector::implementsInterface($n,"iGUIHTML2"))
		die("error:GlobalMessages.E007('$_GET[p]')");
try {
	#ob_start(array(&$output, 'gz'));
	#if(PMReflector::implementsInterface($n,"iGUIHTMLMP2"))
	echo $b->getHTML((isset($_GET["id"]) ? $_GET["id"] : "-1"), isset($_GET["page"]) ? $_GET["page"] : 0);
	#else
	#	echo $b->getHTML((isset($_GET["id"]) ? $_GET["id"] : "-1"));
} catch (TableDoesNotExistException $e) {
	echo "error:GlobalMessages.E001";
} catch (DatabaseNotSelectedException $e) {
	echo "error:GlobalMessages.E002";
} catch (NoDBUserDataException $e) {
	echo "error:GlobalMessages.E003";
} catch (FieldDoesNotExistException $e) {
	echo "error:GlobalMessages.E004";
} catch (DatabaseNotFoundException  $e) {
	echo "error:GlobalMessages.E002";
}

?>