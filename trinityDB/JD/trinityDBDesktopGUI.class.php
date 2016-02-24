<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  2007 - 2013, Rainer Furtmeier - Rainer@Furtmeier.IT
 */
class trinityDBDesktopGUI extends ADesktopGUI implements iGUIHTML2 {
	
	public function getHTML($id){
		// <editor-fold defaultstate="collapsed" desc="Aspect:jP">
		try {
			$MArgs = func_get_args();
			return Aspect::joinPoint("around", $this, __METHOD__, $MArgs);
		} catch (AOPNoAdviceException $e) {}
		Aspect::joinPoint("before", $this, __METHOD__, $MArgs);
		// </editor-fold>
		
		if($_SESSION["S"]->isUserAdmin()) return parent::getHTML($id);
		
		switch($id){
			case "1":
				$AC = anyC::get("JDownload");
				$AC->addOrderV3("JDownloadDate", "DESC");
				$AC->setLimitV3(19);
				
				$T = new HTMLTable(2);
				$T->weight("light");
				$T->addColStyle(1, "padding:5px;padding-left:10px;");
				$T->addColStyle(2, "color:grey;text-align:right;padding:5px;");
				while($D = $AC->getNextEntry()){
					$T->addRow(array($D->A("JDownloadRenameto"), Util::formatByte($D->A("JDownloadFilesize"), 2)));
					
					if($D->A("JDownloadRenamed") == 0)
						$T->addRowClass ("error");
						#$r .= OnEvent::script ("\$j('#BrowsermJDownload".$E->getID()."').css('background-color', '');");
					$T->addRowStyle("cursor:pointer;");
					
					$T->addRowEvent("click", "contentManager.loadPlugin('contentRight', 'mJDownload', '', ".$D->getID().");");
				}
				
				return "<p class=\"prettyTitle\">Downloads</p>".$T;
			break;
			
			case "2":
				
			break;
			
			case "3":
				return "";
			break;
		}

	}
}
?>
