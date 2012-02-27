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
class CreditsGUI implements iGUIHTML2{
	public function getHTML($id){
		return "
		<div class=\"backgroundColor1 Tab\"><p>Credits</p></div>
		<table>
			<colgroup>
				<col class=\"backgroundColor2\" style=\"width:150px;\" />
				<col class=\"backgroundColor3\" />
			</colgroup>
			<tr>
				<td style=\"font-weight:bold;\" colspan=\"2\" class=\"backgroundColor0\">Icons</td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://tango.freedesktop.org\">Tango Desktop Project</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://www.fatcow.com/free-icons/\">1000 Free \"Farm-Fresh Web Icons\"</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://www.gnome.org/\">Gnome icons</a></td>
			</tr>
			<tr>
				<td colspan=\"2\"><a href=\"http://www.kde-look.org/content/show.php/Crystal+Clear?content=25668\">Crystal Clear by Everaldo Coelho</a></td>
			</tr>
			<tr>
				<td style=\"font-weight:bold;\" colspan=\"2\" class=\"backgroundColor0\">Javascripts</td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Kalender:</td>
				<td><a href=\"http://www.goweb.de/javascriptkalender.htm\">KjM</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Base64-Klasse:</td>
				<td><a href=\"http://www.webtoolkit.info/\">WebToolkit</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">AJAX-Bilderupload:</td>
				<td><a href=\"http://valums.com/ajax-upload/\">Andrew Valums</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">SHA1-Klasse:</td>
				<td><a href=\"http://www.webtoolkit.info/\">WebToolkit</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Effekte:</td>
				<td><a href=\"http://script.aculo.us/\">Script.aculo.us</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">AJAX-Framework:</td>
				<td><a href=\"http://www.prototypejs.org/\">Prototype</a></td>
			</tr>
			<!--<tr>
				<td style=\"text-align:right;\">Farbwähler:</td>
				<td><a href=\"mailto:bob@redivi.com\">Bob Ippolito</a></td>
			</tr>-->
			<tr>
				<td style=\"font-weight:bold;\" colspan=\"2\" class=\"backgroundColor0\">PHP</td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">MySQL-Backup:</td>
				<td><a href=\"http://www.phpmybackuppro.net\">phpMyBackupPro</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">PDF-Klasse:</td>
				<td><a href=\"http://www.fpdf.de\">FPDF</a></td>
			</tr>
			<tr>
				<td style=\"text-align:right;\">Mail-Klasse:</td>
				<td><a href=\"http://www.phpguru.org/\">Richard Heyes</a></td>
			</tr>
		</table>";
	}
}
?>
