<?php
/**
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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class PluginGUI extends Plugin implements iGUIHTML2 {
	function getHTML($id){
		
		$this->loadMeOrEmpty();
		
		$gui = new HTMLGUI();
		$gui->setObject($this);
		$gui->setName("Plugin");

		$gui->setLabel("PluginTables", "Tabellen");
		$gui->setLabel("PluginMenuName", "Menü-Name");
		$gui->setLabel("PluginApplication", "Anwendung");
		$gui->setLabel("PluginFolder", "Verzeichnis");
		$gui->setLabel("PluginIcon", "Symbol");
		$gui->setLabel("PluginHasJSFile", "JS-Datei?");
		$gui->setLabel("PluginAdmin", "Admin?");
		$gui->setLabel("PluginVersion", "Version");
		
		$gui->setLabel("PluginDescriptionTemplateID", "Beschreibung");
		$gui->setLabel("PluginObjectTemplateID", "Object");
		$gui->setLabel("PluginObjectGUITemplateID", "ObjectGUI");
		$gui->setLabel("PluginCollectionGUITemplateID", "CollectionGUI");
		$gui->setLabel("PluginDoSomethingElse", "doSomethingElse");
		$gui->setLabel("PluginSearch", "Suche?");
		$gui->setLabel("PluginSearchFields", "Durchs. Felder");
		$gui->setLabel("PluginSearchDisplayFields", "Angez. Felder");
		$gui->setLabel("PluginCollectionDisplayFields", "Angez. Felder");
		$gui->setLabel("PluginDescriptionInFolder","im Ordner?");
		$gui->setLabel("PluginCreateSQLTables","SQL-Tabellen erstellen?");

		$gui->setFieldDescription("PluginTables", "Mehrere Tabellen mit Leerzeichen trennen");

		$gui->insertSpaceAbove("PluginCreateSQLTables","Templates");
		$gui->insertSpaceAbove("PluginCollectionDisplayFields","Collection-Optionen für Hauptklasse");

		$acD = new mPluginTemplateGUI();
		$acD->addAssocV3("PluginTemplateType", "=", "0");

		$gui->selectWithCollection("PluginDescriptionTemplateID", $acD, "PluginTemplateName", "keine Datei erzeugen");

		$acO = new mPluginTemplateGUI();
		$acO->addAssocV3("PluginTemplateType", "=", "1");

		$gui->selectWithCollection("PluginObjectTemplateID", $acO, "PluginTemplateName", "keine Datei erzeugen");

		$acOG = new mPluginTemplateGUI();
		$acOG->addAssocV3("PluginTemplateType", "=", "2");

		$gui->selectWithCollection("PluginObjectGUITemplateID", $acOG, "PluginTemplateName", "keine Datei erzeugen");

		$acC = new mPluginTemplateGUI();
		$acC->addAssocV3("PluginTemplateType", "=", "3");

		$gui->selectWithCollection("PluginCollectionGUITemplateID", $acC, "PluginTemplateName", "keine Datei erzeugen");

		$gui->setType("PluginHasJSFile", "checkbox");
		$gui->setType("PluginAdmin", "checkbox");
		$gui->setType("PluginDoSomethingElse", "checkbox");
		$gui->setType("PluginDescriptionInFolder", "checkbox");
		$gui->setType("PluginCreateSQLTables", "checkbox");

		$gui->setType("PluginSearch", "checkbox");

		$B = new Button("Plugin\nerzeugen","save");
		$B->rme("Plugin", $this->ID, "createPlugin", "", "Popup.display(\'Log\',transport);");
		
		$tab = new HTMLTable(1);
		$tab->addRow($B);

		$gui->setStandardSaveButton($this);
	
		return $tab.$gui->getEditHTML();
	}

	public function createPlugin(){
		$tab = new HTMLTable(2);
		$tab->setColWidth(1, "120px");

		$tables = explode(" ", $this->A("PluginTables"));

		$mainTable = $tables[0];

		if($this->A("PluginDescriptionInFolder") == "1" AND $this->A("PluginFolder") == "")
			die("error:'Die Beschreibungs-Datei kann nicht im Plugin-Ordner angelegt werden, da kein Plugin-Ordner angegeben ist.'");

		$startDir = str_replace("".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."Plugins", "", dirname(__FILE__));
		$pluginDir = $startDir."/".$this->A("PluginApplication")."/".$this->A("PluginFolder");

		$pluginFile = $startDir."/".$this->A("PluginApplication")."/m".$mainTable."Plugin.xml";
		if($this->A("PluginDescriptionInFolder") == "1")
			$pluginFile = $startDir."/".$this->A("PluginApplication")."/".$this->A("PluginFolder")."/plugin.xml";


		if($this->A("PluginFolder") != ""){

			$F = new File($pluginDir);
			$F->loadMe();

			$dirExists = $F->getA() != null;
			$dirStatus = "Verzeichnis existiert";
			if(!$dirExists){
				mkdir($pluginDir);
				chmod($pluginDir, 0777);
				$dirStatus = "Verzeichnis erstellt";
				$F->forceReload();
			}
			$dirStatus .= "<br />Beschreibbar: ".($F->A("FileIsWritable") ? "ja" : "nein");
			$dirStatus .= "<br />Lesbar: ".($F->A("FileIsReadable") ? "ja" : "nein");
			$tab->addLV("Verzeichnis:", $dirStatus);

			foreach($tables AS $T){
				$objectFile = $pluginDir."/$T.class.php";
				$objectGUIFile = $pluginDir."/".$T."GUI.class.php";
				$collectionGUIFile = $pluginDir."/m".$T."GUI.class.php";

				if($this->A("PluginObjectTemplateID") != 0)
					$tab->addLV("Object-Datei:", $T.":<br />".$this->makeFile($objectFile, $T, new PluginTemplate($this->A("PluginObjectTemplateID"))));

				if($this->A("PluginObjectGUITemplateID") != 0)
					$tab->addLV("ObjectGUI-Datei:", $T.":<br />".$this->makeFile($objectGUIFile, $T, new PluginTemplate($this->A("PluginObjectGUITemplateID"))));

				if($this->A("PluginCollectionGUITemplateID") != 0)
					$tab->addLV("CollectionGUI-Datei:", $T.":<br />".$this->makeFile($collectionGUIFile, $T, new PluginTemplate($this->A("PluginCollectionGUITemplateID"))));
			}
		}

		if($this->A("PluginDescriptionTemplateID") != 0)
			$tab->addLV("Plugin-Datei:", $this->makeFile($pluginFile, $mainTable, new PluginTemplate($this->A("PluginDescriptionTemplateID"))));

		if($this->A("PluginCreateSQLTables") == "1"){
			$DB = new DBStorage();
			$C = $DB->getConnection();

			foreach($tables AS $T){
				$C->query("CREATE TABLE `$T` (
`{$T}ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`{$T}Name` VARCHAR( 100 ) NOT NULL
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci ");

				$value = "erstellt";
				if($C->errno) $value = addslashes($C->error);

				$tab->addLV("SQL-Tablle:", $T.":<br />".$value);
			}
		}

		echo $tab;
	}

	private function quoteFieldValues($field){
		$v = $this->A($field);
		if($v != ""){
			if($v{0} == "\"") return;
			$ex = split(",", $this->A($field));
			foreach($ex AS $key => $value){
				$ex[$key] = "\"".trim($value)."\"";
			}

			$this->changeA($field, implode(", ", $ex));
		}
	}

	function makeFile($filename, $table, $template){

		$F = new File($filename);
		$F->loadMe();

		$fileExists = $F->getA() != null;
		$fileStatus = "Datei existiert";
		if($fileExists)
			return $fileStatus;
	
		if (!$handle = fopen($filename, 'w+'))
			return "Kann Datei $filename nicht öffnen/anlegen";

		$content = $template->A("PluginTemplateText");

		$A = PMReflector::getAttributesArray($this->A);

		$this->quoteFieldValues("PluginSearchFields");
		$this->quoteFieldValues("PluginSearchDisplayFields");
		$this->quoteFieldValues("PluginCollectionDisplayFields");

		foreach($A AS $k => $v){
			$w = $this->A($v);
			if($w === "0") $w = "false";
			if($w === "1") $w = "true";

			$content = str_replace("{".$v."}", $w, $content);
		}

		$tables = explode(" ", $this->A("PluginTables"));
		$tablesXML = "";
		if(count($tables) > 0) foreach($tables AS $T)
			$tablesXML .= "\n			<table name=\"$T\" />";

		$content = str_replace("{PluginTablesXML}", $tablesXML, $content);
		$content = str_replace("{PluginTable}", $table, $content);

		if($table != $tables[0]) {
			$this->changeA("PluginSearch", "0");
			$this->changeA("PluginCollectionDisplayFields", "");
		}

		$newContent = "";
		$addLine = true;
		$removeNextFI = false;
		$c = split("\n", $content);
		foreach($c AS $v){
			if((trim($v) == "FI-->" OR trim($v) == "#FI") AND $addLine == false) {
				$addLine = true;
				continue;
			}

			if(trim($v) == "<!--IF false" OR trim($v) == "#IF false" OR trim($v) == "#IF !true") {
				$addLine = false;
				continue;
			}

			if(trim($v) == "<!--IF true" OR trim($v) == "#IF true" OR trim($v) == "#IF !false") {
				$removeNextFI = true;
				continue;
			}

			if((trim($v) == "FI-->" OR trim($v) == "#FI") AND $removeNextFI){
				$removeNextFI = false;
				continue;
			}

			if(!$addLine) continue;
		
			$newContent .= $v."\n";
		}


		if (fwrite($handle, trim($newContent)) === false)
			return "Kann in Datei nicht schreiben";

		chmod($filename, 0666);

		fclose($handle);

		return "Datei erstellt";
	}
}
?>