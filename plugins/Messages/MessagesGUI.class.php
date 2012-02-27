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
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class MessagesGUI extends UnpersistentClass implements iGUIHTML2 {
	public function getHTML($id){
		if($_SESSION["BPS"]->isPropertySet("SysMessages","displayCategory"))
			$bps = $_SESSION["BPS"]->getProperty("SysMessages","displayCategory");
		
		echo "
<input type=\"button\" style=\"width:50px;\" onclick=\"showBox();\" value=\"show\" /> 
<input type=\"button\" style=\"width:50px;\" onclick=\"hideBox();\" value=\"hide\" /> 
<input type=\"button\" style=\"width:50px;\" onclick=\"new Ajax.Updater('messageLayer', './interface/rme.php?class=Messages&constructor=&method=clearMessages&parameters=');\" value=\"clear\" /> 
<input type=\"button\" style=\"width:100px;\" onclick=\"new Ajax.Updater('messageLayer', './interface/loadFrame.php?p=Messages&bps=SysMessages;displayCategory:'+$('messagesCategory').value);\" value=\"Refresh\" />
<select style=\"width:100px;\" id=\"messagesCategory\">
	<option ".((isset($bps) AND $bps == "all") ? "selected=\"selected\"" : "").">all</option>
	<option ".((isset($bps) AND $bps == "SQL") ? "selected=\"selected\"" : "").">SQL</option>
	<option ".((isset($bps) AND $bps == "BPS") ? "selected=\"selected\"" : "").">BPS</option>
</select>
<br />
<br />
<pre>";
		$_SESSION["messages"]->echoMessagesReverse();
		echo "</pre>";
	}

	function clearMessages(){
		$_SESSION["messages"]->clearMessages();
		$this->getHTML();
	}

	public static function doSomethingElse(){
		$_SESSION["messages"]->startLogging();
	}
}
?>
