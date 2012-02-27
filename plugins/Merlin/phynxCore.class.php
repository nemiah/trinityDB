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
class phynxCore {
	protected $deployTo;
	protected $deployFrom;
	protected $deployToBase;

	protected $directories = array();

	protected $coreDirectoriesCopy;
	protected $coreDirectoriesEmpty;
	protected $coreFiles;

	protected $applications;
	protected $plugins;

	protected $debug = false;
	protected $debugTable;

	protected $selections;
	protected $zip = false;
	protected $demoBox = false;
	protected $parents;

	protected $zipLoc;
	protected $specifics;

	protected $toSubDir = false;

	public function __construct(){
		$this->setCoreStructure();
		$this->debugTable = new HTMLTable(2, "Deploy-Log");
		$this->debugTable->setTableStyle("width:700px;margin-left:10px;");

		$this->deployFrom = Util::getRootPath();
	}

	public function toSubdir($dir){
		$this->toSubDir = $dir;
	}

	public function applications(array $apps){
		$this->applications = $apps;
	}

	public function plugins(array $plugins){
		$this->plugins = $plugins;
	}

	public function parents(array $parents){
		$this->parents = $parents;
	}

	public function selections(array $selections){
		$this->selections = $selections;
	}

	public function zip($b){
		$this->zip = $b;
	}

	public function demoBox($b){
		$this->demoBox = $b;
	}

	public function specifics($files){
		$this->specifics = $files;
	}

	// <editor-fold defaultstate="collapsed" desc="debug">
	public function debug($bool){
		$this->debug = $bool;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="setCoreStructure">
	private function setCoreStructure(){
		$rm = array();
		$rm[] = "images";
		$rm[] = "javascript";
		$rm[] = "classes";
		$rm[] = "styles";
		$rm[] = "interface";
		$rm[] = "libraries";
		$rm[] = "styles";

		$mkEmpty = array();
		$mkEmpty[] = "specifics";
		$mkEmpty[] = "applications";
		$mkEmpty[] = "system";
		$mkEmpty[] = "system/IECache";
		$mkEmpty[] = "system/Backup";
		$mkEmpty[] = "system/DBData";

		$cpFiles = array();
		$cpFiles[] = "system/connect.php";
		$cpFiles[] = "system/basics.php";
		$cpFiles[] = "system/info.php";
		$cpFiles[] = "index.php";
		$cpFiles[] = "gpl.txt";

		$this->coreFiles = $cpFiles;
		$this->coreDirectoriesCopy = $rm;
		$this->coreDirectoriesEmpty = $mkEmpty;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="deployTo">
	public function deployTo($folder){
		$this->deployTo = $folder;
		if($this->deployTo[strlen($this->deployTo) - 1] != "/")
			$this->deployTo .= "/";

		$this->deployToBase = $this->deployTo;

		if($this->toSubDir !== false)
			$this->deployTo .= $this->toSubDir."/";
	}
	// </editor-fold>

	public function make(){
		if($this->deployTo == null)
			throw new Exception("No deployment folder given");

		
		$this->log("Verzeichnis", $this->deployTo);


		$this->makeEmptyDir();

		$this->makeCoreStructure();

		$this->makeAddParents();

		$this->makeAppStructures();

		$this->makeCopyPlugins();

		$this->makeCopySpecifics();

		$this->makeZip();

		$this->makeDemoBox();

		if($this->debug)
			echo "<div style=\"width:700px;\">".$this->debugTable."</div>";

		return $this->zipLoc;
	}

	public function makeAddParents(){
		if($this->parents == null) return;

		#echo "<pre>";
		foreach($this->parents AS $parentID){
			$M = new Merlin($parentID);

			$Settings = $M->getSettings();

			if(is_array($this->applications) AND $this->applications[0] == "")
				$this->applications = array();

			$this->applications = array_merge($this->applications, $Settings["applications"]);#explode(";:;", $M->A("MerlinApps"))

			$this->plugins = array_merge($this->plugins, $Settings["allPlugins"]);#explode(";:;", $M->A("MerlinPlugins"))

			if(is_array($this->selections) AND $this->selections[0] == "")
				$this->selections = array();

			$this->selections = array_merge($this->selections, explode("\n", trim($Settings["selections"])));#$M->A("MerlinSelections")

			if(is_array($this->specifics) AND $this->specifics[0] == "")
				$this->specifics = array();

			$this->specifics = array_merge($this->specifics, explode("\n", trim($Settings["specifics"])));#$M->A("MerlinSelections")

			#print_r(explode("\n", $Settings["selections"]));
		}

		$this->applications = array_unique($this->applications);
		$this->plugins = array_unique($this->plugins);
		$this->selections = array_unique($this->selections);

		#print_r($this->applications);
		#print_r($this->plugins);
		#print_r($this->selections);
		#echo "</pre>";

		foreach($this->selections AS $k => $v)
			$this->selections[$k] = explode(":", $v);
	}

	public function makeCopySpecifics(){
		if($this->specifics == null OR count($this->specifics) == 0) return;

		foreach($this->specifics AS $file){
			if($file == "") continue;
			
			copy($this->deployFrom."specifics/$file", $this->deployTo."specifics/$file");
		}

		$this->log("specifics", implode("<br />", $this->specifics));
	}

	public function makeCopyPlugins(){
		if($this->plugins == null) return;

		$apps = array();
		foreach($this->plugins AS $plugin){
			$ex = explode("_", $plugin);
			
			if(!in_array($ex[0], $apps))
				$apps[] = $ex[0];
		}

		foreach($apps AS $app){
			if($app == "") continue;
			$this->log($app, "");

			$AP = new AppPlugins();

			$AP->scanPlugins($app);

			$applicationFolderTo = $this->deployTo.$app."/";
			$applicationFolderFrom = $this->deployFrom.$app."/";

			if(!is_dir($applicationFolderTo))
				mkdir($applicationFolderTo);

			#echo("<pre>$app: $applicationFolderTo</pre>");

			foreach($this->plugins AS $plugin){
				if(strpos($plugin, $app."_") === false) continue;
				
				$plugin = str_replace($app."_", "", $plugin);

				$folder = $AP->getFolderOfPlugin($plugin);


				$pluginFolder = $this->deployTo.$app."/".$folder;
				if(!is_dir($pluginFolder)) !mkdir($pluginFolder, 0777, true);
					#die("<pre>$plugin: ".$pluginFolder."</pre>");

				$cp = new SystemCommand();

				if(strpos($folder, "../") !== false){ //IS LINK!!
					$copyCommand = "cp -R -t $this->deployTo$app/ $applicationFolderFrom{$plugin}LinkPlugin.xml 2>&1";
					$cp->setCommand($copyCommand);
					$cp->execute();
					$out = $cp->getOutput();

					$this->log($plugin, ($out == "" ? "<span style=\"color:green;\">Link kopiert: {$plugin}LinkPlugin.xml</span>" : "$copyCommand:<br /><br />".$out));
					continue;
				}

				$copyCommand = "cp -R -t $pluginFolder $applicationFolderFrom$folder/* 2>&1";
				$cp->setCommand($copyCommand);
				$cp->execute();
				$out = $cp->getOutput();


				if(file_exists($pluginFolder."/CI.pfdb.php"))
					unlink($pluginFolder."/CI.pfdb.php");

				$sql = $this->makePluginCIFile($pluginFolder);

				$this->log($plugin, ($out == "" ? "<span style=\"color:green;\">Verzeichnis kopiert: $folder</span>" : "$copyCommand:<br /><br />".$out)."<pre style=\"font-size:8px;\">$sql</pre>");

				#if(!file_exists($applicationFolderFrom.$plugin."Plugin.class.php")) {
				#	if(file_exists($applicationFolderFrom.$plugin."Plugin.xml"))
						#if($this->debug) echo "Datei $this->deployFrom$app/".$plugin."Plugin.class.php/.xml existiert nicht!\n";
					#else
				#		copy($applicationFolderFrom.$plugin."Plugin.xml",$applicationFolderTo.$plugin."Plugin.xml");

				#} #else
					#copy($applicationFolderFrom.$plugin."Plugin.class.php", $applicationFolderTo.$plugin."Plugin.class.php");
			}
		}
	}

	// <editor-fold defaultstate="collapsed" desc="log">
	protected function log($label, $message){
		if(!$this->debug) return;

		$this->debugTable->addRow(array("<span style=\"font-weight:bold;\">".$label.":</span>", $message));
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="zip">
	private function makeZip(){
		if(!$this->zip) return;

		$app = "phynx";
		$version = "";
		if(isset($_SESSION["applications"])){
			$app = $_SESSION["applications"]->getActiveApplication();
			$version = $_SESSION["applications"]->getRunningVersion()."_";
		}
		
		$zipLoc = "".$app."_".$version.date("d.m.Y").".zip";

		$sc = new SystemCommand();
		$sc->setCommand("cd $this->deployToBase && zip -9 -r $zipLoc .");
		$sc->execute();

		$this->zipLoc = $this->deployToBase."$zipLoc";
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="demoBox">
	private function makeDemoBox(){
		if(!$this->demoBox) return;

		if(!$this->zip) {
			$this->zip = true;
			$this->makeZip();
		}

		$user = "nemiah@open3a.de";
		$toDir = "/var/kunden/webs/web1/DemoBox/".basename($this->deployTo);
		$port = "22222";

		$sc = new SystemCommand();

		$ssh = "/usr/bin/ssh -p$port $user \"rm -R $toDir/*\" 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox clear", $out != "" ? nl2br($out) : "alles OK");


		$ssh = "/usr/bin/scp -P $port $this->zipLoc $user:$toDir 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox upload", $out != "" ? nl2br($out) : "alles OK");


		$ssh = "/usr/bin/ssh -p$port $user \"cd $toDir && unzip -q $toDir/".basename($this->zipLoc)."\" 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox unzip", $out != "" ? nl2br($out) : "alles OK");


		$ssh = "/usr/bin/scp -P $port ".Util::getRootPath()."plugins/Merlin/InstallationDemoBox.pfdb.php $user:$toDir/system/DBData/Installation.pfdb.php 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox DBData", $out != "" ? nl2br($out) : "alles OK");

		
		$ssh = "/usr/bin/ssh -p$port $user \"chmod -R u=rwX,o=rX,g=rX $toDir/*\" 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox permissions", $out != "" ? nl2br($out) : "alles OK");


		$ssh = "/usr/bin/ssh -p$port $user \"chmod -R 777 $toDir/system/IECache\" 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox IECache", $out != "" ? nl2br($out) : "alles OK");


		$ssh = "/usr/bin/ssh -p$port $user \"chmod -R 777 $toDir/specifics\" 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox specifics", $out != "" ? nl2br($out) : "alles OK");


		$ssh = "/usr/bin/ssh -p$port $user \"rm $toDir/".basename($this->zipLoc)."\" 2>&1";
		$sc->setCommand($ssh);
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("DemoBox cleanUp", $out != "" ? nl2br($out) : "alles OK");


	}
	// </editor-fold>


	// <editor-fold defaultstate="collapsed" desc="makeCoreStructure">
	private function makeCoreStructure(){
		$cp = new SystemCommand();
		$cp->setCommand("cp -R -t $this->deployTo $this->deployFrom".implode(" $this->deployFrom",$this->coreDirectoriesCopy)."  2>&1");
		$cp->execute();
		$out = $cp->getOutput();
		$this->log("Kopiere Core-Verzeichnisse", $out == "" ? "kein Fehler" : $out );

		#$sc = new SystemCommand();
		$sc = new SystemCommand();
		$sc->setCommand("find $this->deployTo -name \".svn\" -exec rm -Rf {} \;");
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("Entferne .svn-Verzeichnisse", $out == "" ? "kein Fehler" : $out );

		$sc->setCommand("find $this->deployTo -name \".git\" -exec rm -Rf {} \;");
		$sc->execute();
		$out = $sc->getOutput();
		$this->log("Entferne .git-Verzeichnisse", $out == "" ? "kein Fehler" : $out );


		foreach($this->coreDirectoriesEmpty AS $dir)
			if(!mkdir($this->deployTo.$dir))
				throw new Exception("Could not create directory $this->deployTo$dir");
			
		foreach($this->coreFiles AS $file)
			if(!copy($this->deployFrom.$file, $this->deployTo.$file))
				throw new Exception("Could not copy file $file");
			
		#copy("$path/plugins/Deployment/NewInstallation.pfdb.php",$toFolder."system/DBData/Installation.pfdb.php");
		if(!copy($this->deployFrom."plugins/Merlin/NewInstallation.pfdb.php", $this->deployTo."system/DBData/Installation.pfdb.php"))
			throw new Exception("Could not copy file Installation.pfdb.php");


	}
	// </editor-fold>

	private function makePluginCIFile($pluginFolder){
		if(!file_exists($pluginFolder."/plugin.xml")) return;

		$xml = new SimpleXMLElement(file_get_contents($pluginFolder."/plugin.xml"));

		if(count($xml->plugin->deploy->table) == 0) return;

		#echo "<pre>";
		#foreach($xml->plugin->deploy->table AS $k => $v)
		#	print_r($v["name"]." ".$v["values"]);
		#echo "</pre>";

		$mf = new PhpFileDB();
		$mf->setFolder($pluginFolder."/");
		$mf->pfdbQuery("CREATE TABLE CI ('MySQL' varchar(5000), 'MSSQL' varchar(5000))");

		/*$T = explode(",",$DA->tabellen);
		$T = array_map("trim",$T);

		$D = explode(",",$DA->withData);
		$D = array_map("trim",$D);*/

		$messages = "";

		foreach($xml->plugin->deploy->table AS $Table){

			$where = "";
			foreach($this->selections AS $v)
				if($v[0] == $Table["name"])
					$where .= ($where == "" ? "" : " OR ").$v[1];

			if($Table["name"] == "User")
				$Table["name"] = "UserFakeTable";

			$where = trim($where);

			list($mysqlCreate, $mysqlData, $mysqlMessages) = $this->makeSQL("mysql", $Table, $where);
			list($mssqlCreate, $mssqlData, $mssqlMessages) = $this->makeSQL("mssql", $Table, $where);

			if($this->debug) $messages .= $mysqlMessages.$mysqlCreate."\n";
			if($this->debug) $messages .= $mssqlMessages.$mssqlCreate."\n";
			
			$mf->pfdbQuery("INSERT INTO CI (MySQL, MSSQL) VALUES ('".$mf->escapeString(trim($mysqlCreate))."', '".$mf->escapeString(trim($mssqlCreate))."') ".($where != "" ? ",('".$mf->escapeString(trim("INSERT INTO".$mysqlData))."', '".$mf->escapeString(trim("INSERT INTO".$mssqlData))."')" : "")."");
		}

		return $messages;
	}

	private function makeSQL($target, $Table, $where){
		$messages = "";

		$sc = new SystemCommand();
		$cmd = "mysqldump --compact -h".$_SESSION["DBData"]["host"]." -p".$_SESSION["DBData"]["password"]." -u".$_SESSION["DBData"]["user"]." ".$_SESSION["DBData"]["datab"]." ".$Table["name"]." ".($where != "" ? " --where=\"$where\"" : "")." ".($where != "" ? "" : "--no-data");

		$sc->setCommand($cmd);
		$sc->execute();
		$out = $sc->getOutput();
		$oldOut = $out;

		$out = str_replace("UserFakeTable","User",$out);
		
		if($Table["name"] == "UserFakeTable")
			$Table["name"] = "User";

		if(strpos($out, "CustomizerColumn") !== false AND $this->debug)
			$messages .= "<span style=\"color:red;font-weight:bold;\">Customizer-Spalte in Tabelle ".$Table["name"]." gefunden!</span>\n";



		#$out = ereg_replace("SET [@a-zA-Z0-9=_ ]*;\n","",$out);
		#$out = ereg_replace("--[@a-zA-Z0-9=_ \.]*\n","",$out);
		$out = preg_replace("/SET [@a-zA-Z0-9=_ ]*;\n/","",$out);
		$out = preg_replace("/--[@a-zA-Z0-9=_ \.]*\n/","",$out);

		if(strpos($out, "CREATE TABLE") !== false AND strpos($oldOut,"/*!50001 CREATE ALGORITHM=") === false){

			$outEx = explode("\n",$out);

			foreach($outEx AS $exk => $exv){
				if(strpos($outEx[$exk], "/*") == 0 AND strpos($outEx[$exk], "*/") !== false AND strpos($outEx[$exk], "/*!50001 VIEW") === false)
					unset($outEx[$exk]);
			}
			$out = implode("\n", $outEx);

			#$out = ereg_replace(" AUTO_INCREMENT=([0-9]+)","",$out);
			$out = preg_replace("/ AUTO_INCREMENT=([0-9]+)/","",$out);
			$out = explode("INSERT INTO",$out);
		}

		if(strpos($oldOut,"/*!50001 CREATE ALGORITHM") !== false AND (strpos($oldOut, "/*!50001 VIEW") !== false)){
			$out = split("/\*!50001 CREATE ALGORITHM=[A-Z]* \*/\n",$out);

			$out = $out[1];
			#$out = ereg_replace("/\*!50013 DEFINER=`[0-9a-zA-Z]*`@`[0-9a-zA-Z%\.]*` SQL SECURITY DEFINER \*/\n","",$out);
			$out = preg_replace("//\*!50013 DEFINER=`[0-9a-zA-Z]*`@`[0-9a-zA-Z%\.]*` SQL SECURITY DEFINER \*/\n/","",$out);
			$out = str_replace("/*!50001 VIEW `".$_SESSION["DBData"]["datab"]."`.", "CREATE VIEW ", $out);
			$out = str_replace(" */", "", $out);
			$out = array($out);
		}

		if($target == "mssql" AND isset($out[1])){
			$DB = new DBStorage();
			$C = $DB->getConnection();

			$result = $C->query("SHOW COLUMNS FROM $Table[name]");

			$a = "";
			while ($row = $result->fetch_assoc())
				$a .= ($a != "" ? ", " : "")."`".$row["Field"]."`";

			$out[1] = " `".$Table["name"]."` (".$a.")".str_replace("start`$Table[name]`", "","start".trim($out[1]));
		}

		return array($this->fixSQL($target, $out[0]), isset($out[1]) ? $this->fixSQL($target, $out[1]) : "", $messages);
	}

	private function fixSQL($target, $sql){
		if($target == "mysql") return $sql;

		if($target == "mssql") return $this->fixSQLMSSQL($sql);
	}

	private function fixSQLMSSQL($sql){
		$sql = str_replace("collate utf8_unicode_ci ", "", $sql);
		$sql = str_replace("ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci", "", $sql);
		$sql = str_replace("ENGINE=MyISAM DEFAULT CHARSET=utf8", "", $sql);
		$sql = str_replace("NOT NULL auto_increment", "IDENTITY(1,1) NOT NULL", $sql);
		$sql = str_replace("character set utf8 ", "", $sql);
		$sql = str_replace("character set latin1 ", "", $sql);
		$sql = str_replace("longblob", "varbinary(max)", $sql);

		$sql = preg_replace("/UNIQUE KEY `([a-zA-Z0-9]*)` \(`([a-zA-Z0-9]*)`\)/", "CONSTRAINT [\\1] UNIQUE NONCLUSTERED ([\\2])", $sql);

		$sql = preg_replace("/`([a-zA-Z0-9]*)`/", "[\\1]", $sql);
		$sql = preg_replace("/int\([0-9]*\)/", "int", $sql);
		$sql = preg_replace("/ COMMENT='[a-zA-Z0-9 ]*'/", "", $sql);
		$sql = preg_replace("/ COMMENT '[a-zA-Z0-9 ]*'/", "", $sql);
		return $sql;
	}

	// <editor-fold defaultstate="collapsed" desc="makeEmptyDir">
	private function makeEmptyDir(){
		$cp = new SystemCommand();
		$cp->setCommand("rm $this->deployToBase* -R 2>&1");
		$cp->execute();
		$out = $cp->getOutput();

		if($this->toSubDir !== false)
			mkdir($this->deployTo);

		$this->log("Leere Verzeichnis", $out == "" ? "kein Fehler" : $out );
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="makeAppStructures">
	public function makeAppStructures(){
		if($this->applications == null) return;
		if(count($this->applications) == 0) return;
		
		foreach($this->applications AS $app){
			if($app != "" AND !copy($this->deployFrom."applications/".$app."Application.class.php", $this->deployTo."applications/".$app."Application.class.php"))
				throw new Exception("Could not copy application-file {$app}Application.class.php");

			if($app == "multiCMS"){

				if($this->toSubDir === false){
					$multiCMSDir = $this->deployTo."public/";
					mkdir($multiCMSDir);
				}
				else
					$multiCMSDir = $this->deployToBase;

				copy($this->deployFrom."_dev/multiCMS/index.php", $multiCMSDir."index.php");
				copy($this->deployFrom."_dev/multiCMS/multiCMSDownload.php", $multiCMSDir."multiCMSDownload.php");

				copy($this->deployFrom."_dev/multiCMS/outsideConnect.php", $this->deployTo."interface/outsideConnect.php");

				$cp = new SystemCommand();
				$cp->setCommand("cp -R ".$this->deployFrom."_dev/multiCMS/multiCMSData $multiCMSDir");
				$cp->execute();
			}
		}
		#foreach($this->applications AS $app)
		#	if($app != "" AND !mkdir($this->deployTo.$app))
		#		throw new Exception("Could not create application-directory {$app}");
	}
	// </editor-fold>
}
?>
