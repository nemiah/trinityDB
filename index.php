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

$dir = new DirectoryIterator(dirname(__FILE__));
$notExecutable = array();
foreach ($dir as $file) {
	if($file->isDot()) continue;
	if(!$file->isDir()) continue;

	if($file->isExecutable()) continue;
	$notExecutable[] = $file->getFilename();
}

if(count($notExecutable) > 0 AND !is_executable("./system") AND stripos(getenv("OS"), "Windows") === false)
	die("The directory <i>system</i> is not marked executable.<br />Please resolve this issue by running the following command inside the installation directory:<br /><code>chmod u=rwX,g=rX,o=rX system</code>");

if(count($notExecutable) > 0 AND is_executable("./system")){
	require "./system/basics.php";

	emoFatalError(
		"I'm sorry, but I'm unable to access some directories",
		"Please make sure that the webserver is able to access these directories and its subdirectories:<br /><br />".implode("<br />", $notExecutable)."<br /><br />Usually a good plan to achieve this, is to run the following<br />commands in the installation directory:<br /><code>chmod -R u=rw,g=r,o=r *<br />chmod -R u=rwX,g=rX,o=rX *</code>",
		"phynx");
}

$texts = array();
$texts["de_DE"] = array();
$texts["de_DE"]["username"] = "Benutzername";
$texts["de_DE"]["password"] = "Passwort";
$texts["de_DE"]["application"] = "Anwendung";
$texts["de_DE"]["login"] = "anmelden";
$texts["de_DE"]["save"] = "Zugangsdaten speichern";
$texts["de_DE"]["sprache"] = "Sprache";
$texts["de_DE"]["optionsImage"] = "Optionen anzeigen";
$texts["de_DE"]["lostPassword"] = "Passwort vergessen?";

$texts["en_US"] = array();
$texts["en_US"]["username"] = "Username";
$texts["en_US"]["password"] = "Password";
$texts["en_US"]["application"] = "Application";
$texts["en_US"]["login"] = "login";
$texts["en_US"]["save"] = "save login data";
$texts["en_US"]["sprache"] = "Language";
$texts["en_US"]["optionsImage"] = "show options";
$texts["en_US"]["lostPassword"] = "Lost password?";

$texts["it_IT"] = array();
$texts["it_IT"]["username"] = "Username";
$texts["it_IT"]["password"] = "Password";
$texts["it_IT"]["application"] = "Applicazione";
$texts["it_IT"]["login"] = "accesso";
$texts["it_IT"]["save"] = "memorizzare i dati";
$texts["it_IT"]["sprache"] = "Lingua";
$texts["it_IT"]["optionsImage"] = "Visualizzare le opzioni";
$texts["it_IT"]["lostPassword"] = "Password persa?";

require "./system/connect.php";
$browserLang = Session::getLanguage();
/*
$E = new Environment();
*/
$cssColorsDir = (isset($_COOKIE["phynx_color"]) ? $_COOKIE["phynx_color"] : "standard");
/*
if(file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php")){
	require_once Util::getRootPath()."plugins/Cloud/Cloud.class.php";
	require_once Util::getRootPath()."plugins/Cloud/mCloud.class.php";

	$E = mCloud::getEnvironment();
}*/

if($_SESSION["S"]->checkIfUserLoggedIn() == false) $_SESSION["CurrentAppPlugins"]->scanPlugins();
header('Content-type: application/xml; charset="utf-8"',true);
echo '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="./styles/layout.xsl" ?>
<phynx>
	<HTMLGUI>
		<overlay>
			<options>
				<label for="username">'.$texts[$browserLang]["username"].'</label>
				<label for="password">'.$texts[$browserLang]["password"].'</label>
				<label for="application">'.$texts[$browserLang]["application"].'</label>
				<label for="login">'.$texts[$browserLang]["login"].'</label>
				<label for="save">'.$texts[$browserLang]["save"].'</label>
				<label for="optionsImage">'.$texts[$browserLang]["optionsImage"].'</label>
				<label for="lostPassword">'.$texts[$browserLang]["lostPassword"].'</label>
				
				<label for="isDemo">Für den Demo-Zugang verwenden Sie bitte Max//Max oder Admin//Admin</label>
				<label for="extDemo">Dies ist die erweiterte Demoversion. Sie können sich auch als Mitarbeiter//Mitarbeiter einloggen, um eine für Mitarbeiter angepasste Version von <b>open3A</b> zu sehen.</label>

				<isDemo value="'.(strstr($_SERVER["SCRIPT_FILENAME"],"demo") ? "true" : "false").'" />
				<isExtendedDemo value="'.(strstr($_SERVER["SCRIPT_FILENAME"],"demo_all") ? "true" : "false").'" />

				<showApplicationsList value="'.Environment::getS("showApplicationsList", "1").'" defaultApplicationIfFalse="'.Environment::getS("defaultApplication", "").'" />
			</options>
			
			<languages>
				<lang value="default">'.$texts[$browserLang]["sprache"].'</lang>
				<lang value="de_DE">deutsch</lang>
				<lang value="en_US">english</lang>
				<lang value="it_IT">italiano</lang>
			</languages>
		</overlay>
		
		<applications>'.$_SESSION["applications"]->getGDL().'
		</applications>
		
		<options>
			<label for="title">'.Environment::getS("renameFramework", "phynx by Furtmeier Hard- und Software").'</label>
			<isDesktop value="'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop") ? "true" : "false").'" />
		</options>
		
		<stylesheets>
			<css>./styles/standard/overlayBox.css</css>
			<css>./styles/standard/frames.css</css>
			<css>./styles/standard/general.css</css>
			<css>./styles/standard/navigation.css</css>
			<css>./styles/standard/autoCompletion.css</css>
			<css>./styles/standard/contextMenu.css</css>
			<css>./styles/standard/TextEditor.css</css>
			<css>./styles/standard/calendar.css</css>
			<css>./styles/'.Environment::getS("cssColorsDir", $cssColorsDir).'/colors.css</css>
			'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "vertical") ? '<css>./styles/standard/vertical.css</css>' : "").'
			'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "desktop") ? '<css>./styles/standard/desktop.css</css>' : "").'
			'.((isset($_COOKIE["phynx_layout"]) AND $_COOKIE["phynx_layout"] == "fixed") ? '<css>./styles/standard/fixed.css</css>' : "").'
		</stylesheets>
		
		<javascripts>
			<js>./libraries/scriptaculous/prototype.js</js>
			<js>./libraries/scriptaculous/effects.js</js>
			<js>./libraries/scriptaculous/dragdrop.js</js>
			<js>./libraries/scriptaculous/slider.js</js>
			<js>./libraries/scriptaculous/builder.js</js>
			
			<js>./javascript/Observer.js</js>
			<js>./javascript/overlayBox.js</js>
			<js>./javascript/menu.js</js>
			<js>./javascript/autoComplete.js</js>
			<js>./javascript/contextMenu.js</js>
			<js>./javascript/userControl.js</js>
			<js>./javascript/Interface.js</js>
			<js>./javascript/Popup.js</js>
			<js>./javascript/contentManager.js</js>
			<js>./javascript/DesktopLink.js</js>
			<js>./javascript/handler.js</js>
			<js>./javascript/Util.js</js>
			<js>./javascript/Aspect.js</js>
			<js>./javascript/DynamicJS.php</js>
			
			<js>./libraries/calendar.js</js>
			<js>./libraries/TextEditor.js</js>
			<js>./libraries/webtoolkit.base64.js</js>
			<js>./libraries/webtoolkit.sha1.js</js>
			<js>./libraries/fileuploader.js</js>
		</javascripts>
		
		<contentLeft>
			<p>Sie haben JavaScript nicht aktiviert.<br />
			Bitte aktivieren Sie JavaScript, damit diese Anwendung funktioniert.</p>
		</contentLeft>
		
		<footer>
			<options>
				<showHelpButton value="'.Environment::getS("showHelpButton", "1").'" />
				<showLayoutButton value="'.Environment::getS("showLayoutButton", "1").'" />
				<showCopyright value="'.Environment::getS("showCopyright", "1").'" />
			</options>

			<iconLayout>./images/navi/office.png</iconLayout>
			<iconLogout>./images/i2/logout.png</iconLogout>
			<iconHelp>./images/navi/hilfe.png</iconHelp>
			
			<copyright>
				Copyright (C) 2007, 2008, 2009, 2010, 2011 by <a href="http://www.Furtmeier.IT">Furtmeier Hard- und Software</a>. This program comes with ABSOLUTELY NO WARRANTY; this is free software, and you are welcome to redistribute it under certain conditions; see <a href="gpl.txt">gpl.txt</a> for details.<br />Thanks to the authors of the libraries and icons used by this program. <a href="javascript:contentManager.loadFrame(\'contentRight\',\'Credits\');">View credits.</a>
			</copyright>
		</footer>
	</HTMLGUI>
</phynx>';
?>