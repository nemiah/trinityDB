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
if($_SESSION["S"]->checkIfUserLoggedIn() == true) die("-1");
$data = $_GET;
if(isset($_POST) AND count($_POST) > 0) $data = $_POST;

#if(!isset($data["class"])) die();

if(isset($data["class"]) AND !$_SESSION["S"]->checkIfUserIsAllowed($data["class"])) die("You are not allowed to see this Page!");

try {
	$className = $data["class"].(!strstr($data["class"],"GUI") ? "GUI" : "");

	if($data["id"] != -1){
		$oldClass = new $className($data["id"]);
		$oldClass->loadMe();
		$A = $oldClass->getA();

		if($A == null) die("error:GlobalMessages.E011");

		if(get_class($A) != "stdClass") $A->fillWithAssociativeArray($data);
		else $A = Util::fillStdClassWithAssocArray($A, $data);

	} else {
		$oldClass = new $className(-1);
		//$n = str_replace("GUI","",$data["class"]."Attributes");
		
		$A = $oldClass->newAttributes();
		if(get_class($A) != "stdClass") $A->newWithAssociativeArray($data);
		else $A = Util::fillStdClassWithAssocArray($A, $data);
	}
	/*
	if(isset($data["saveToAttribute"]) AND isset($_FILES['datei']) AND $_FILES['datei']['size'] > 0){
		$newFilename = Util::getTempFilename($_FILES['datei']['name'],"tmp");
		#echo $_FILES['datei']['tmp_name']
		if(!move_uploaded_file($_FILES['datei']['tmp_name'], $newFilename))
			die("<pre style=\"font-size:8px;\">Verschieben der Datei ".$_FILES['datei']['tmp_name']." fehlgeschlagen!</pre>");
		#echo "<br />Content:";

		$content = addslashes(file_get_contents($newFilename));
		#echo ";";
		$fi = $data["saveToAttribute"];
		
		if($className == "FileGUI") {
			if($_FILES['datei']['size'] > 0 AND $fi != "") $A->$fi = $content;
			$A->FileName = $_FILES['datei']['name'];
		}
		
		if($className == "TempFileGUI") {
			if($_FILES['datei']['size'] > 0 AND $fi != "") $A->$fi = $_FILES['datei']['type'].":::".$_FILES['datei']['size'].":::".base64_encode($content);
			$A->filename = $newFilename;
			$A->filesize = $_FILES['datei']['size'];
			$A->filetype = $_FILES['datei']['type'];
			$A->originalFilename = $_FILES['datei']['name'];
		}
	}*/
	
	if(isset($data["emptyAttribute"])) {
		$fi = $data["emptyAttribute"];
		$A->$fi = "";
	}
	
	$C = new $className($data["id"]);
	$C->setA($A);

	if($className == "FileGUI") {
		#if($_FILES['datei']['size'] > 0 AND $fi != "") $A->$fi = $content;
		#$A->FileName = $_FILES['datei']['name'];
		$C->makeUpload($A);
	}

	if($className == "TempFileGUI") {
		$C->makeUpload($A);
	}
	
	if($data["id"] != -1) $C->saveMe(true, true);
	else $C->newMe(true, true);
	
} catch (TableDoesNotExistException $e) {

} catch (DatabaseNotSelectedException $e) {
	#echo "Database does not exist<br />";
} catch (NoDBUserDataException $e) {
	#echo "Database authentication failed.<br />";
} catch (DatabaseNotFoundException $e) {
	#echo "Specified database not found.<br />";
} catch (DuplicateEntryException $e) {
	die("error:GlobalMessages.E012(".$e->getDuplicateFieldValue().")");
	/*echo "Der Wert ".$e->getDuplicateFieldValue()." wurde<br />
	bereits vergeben";
	exit();*/
} catch (ClassNotFoundException $e){
	die("Die Klasse '".$e->getClassName()."' wurde nicht gefunden!");
}
#echo "message:GlobalMessages.M002";
?>
