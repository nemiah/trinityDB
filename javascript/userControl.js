/*
 *
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
 
 
var userControl = {
	doLogin: function(){
		//"+$('loginUsername').value+","+$('loginPassword').value+","+$('anwendung').value+"
		if($('loginPassword').value != ";;cookieData;;") $('loginSHAPassword').value = SHA1($('loginPassword').value);
		$('loginPassword').value = "";
		new Ajax.Request("./interface/rme.php", {
		method: 'post',
		parameters: "class=Users&construct=&method=doLogin&parameters='"+joinFormFieldsToString('loginForm')+"'",
		onSuccess: function(transport) {
			if(transport.responseText == "") {
				alert("Fehler: Server antwortet nicht!");
				return;
			}
	    	if(transport.responseText == 0) {
	    		alert("Benutzername/Passwort falsch!\nBitte beachten Sie beim Passwort Groß-/Kleinschreibung.");
	    	} else {
	    		if(transport.responseText != 1 && transport.responseText != -2)
	    			alert(transport.responseText.replace(/<br \/>/ig,"\n").replace(/<b>/ig,"").replace(/<\/b>/ig,"").replace(/&gt;/ig,">"));
	    		
				var a = new Date();
				a = new Date(a.getTime() +1000*60*60*24*365);
				if($('saveLoginData').checked)
					document.cookie = 'userLoginData='+$('loginUsername').value+':'+$('loginSHAPassword').value+'; expires='+a.toGMTString()+';';
				else 
					document.cookie = 'userLoginData=--; expires=Thu, 01-Jan-70 00:00:01 GMT;';
	
	    		loadMenu();
	    		DesktopLink.loadContent();
	    		//$('loginPassword').value = "";
	    	}
		}});
	},
	
	doTestLogin: function(){
		new Ajax.Request("./interface/rme.php", {
		method: 'post',
		parameters: "class=Users&construct=&method=doLogin&parameters='%3B-%3B%3Bund%3B%3B-%3BloginUsername%3B-%3B%3Bistgleich%3B%3B-%3B0%3B-%3B%3Bund%3B%3B-%3BloginSHAPassword%3B-%3B%3Bistgleich%3B%3B-%3B0%3B-%3B%3Bund%3B%3B-%3Banwendung%3B-%3B%3Bistgleich%3B%3B-%3B0%3B-%3B%3Bund%3B%3B-%3BsaveLoginData%3B-%3B%3Bistgleich%3B%3B-%3B0'",
		onSuccess: function(transport) {
			if(transport.responseText == "") {
				alert("Fehler: Server antwortet nicht!");
				return;
			}
	    	if(transport.responseText == -2) alert("Bitte verwenden Sie 'Admin' als Benutzer und Passwort!\nDie Benutzer-Datenbank existiert noch nicht.");
		}});
	},
	
	doLogout: function(redirect){
		new Ajax.Request("./interface/rme.php?class=Users&constructor=&method=doLogout&parameters=''", {
		method: 'get',
		onSuccess: function(transport) {
			loadMenu();
			if(typeof redirect != "undefined" && redirect != "") document.location.href= redirect;
		}});
	}
}