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

class mMerlinGUI extends anyC implements iGUIHTMLMP2 {

	public function getHTML($id, $page){
		#$this->addOrderV3("MerlinHasChildren");
		$this->addOrderV3("MerlinDirName");
		$this->loadMultiPageMode($id, $page, 0);

		$gui = new HTMLGUIX($this);

		$gui->name("Merlin");
		
		$gui->attributes(array("MerlinDirName"));

		$gui->parser("MerlinDirName", "mMerlinGUI::dirParser");

		return $gui->getBrowserHTML($id);
	}

	public static function dirParser($w, $E){
		$B = new Button("jetzt veröffentlichen", "./plugins/Merlin/deployNow.png");
		$B->onclick("if(!confirm('Jetzt deployen?')) return; $('contentLeft').update('<p>Bitte warten...</p>'); ");
		$B->rmePCR("Merlin", $E->getID(), "deploy", "true", "$('contentLeft').update(transport.responseText);");
		$B->type("icon");
		$B->style("float:left;margin-right:5px;");

		$BC = "";
		if($E->A("MerlinHasChildren") == "1"){
			$BC = new Button("hat Kinder", "./plugins/Merlin/hasChildren.png");
			$BC->type("icon");
			$BC->style("float:right;margin-left:5px;");
		}

		$dir = str_replace("/home/nemiah/NetBeansProjects/deploy/","", $w);
		if($dir[strlen($dir) - 1] == "/"){
			$dir[strlen($dir) - 1] = " ";
			$dir = trim($dir);
		}

		return $BC.$B.$dir;
	}
}
?>