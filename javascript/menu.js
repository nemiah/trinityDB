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

mouseIsOver = new Array();
lastHighLight = null;
Event.observe(window, 'load', function() {
	Interface.init();
	overlayBox.init();
	loadMenu();
	$('contentLeft').update();
	if (document.cookie == "") document.cookie = "CookieTest = Erfolgreich"
	if (document.cookie == "") alert("In Ihrem Browser sind Cookies deaktiviert.\nBitte aktivieren Sie Cookies, damit diese Anwendung funktioniert.")
});

var appMenu = {
	isInit: false,

	show: function(){
		if(!appMenu.isInit)
			appMenu.init();

		$("appMenuContainer").style.display = "";
	},

	hide: function(){
		$("appMenuContainer").style.display = "none";
		$('appMenuDisplayedContainer').style.display = 'none';
	},

	init: function(){
		Sortable.create("appMenuHidden", {
			handle: "appMenuHandle",
			constraint: "vertical",
			containment: ["appMenuDisplayed", "appMenuHidden"],
			dropOnEmpty:true,
			onUpdate: appMenu.update,
			onChange: appMenu.change
		});
		Sortable.create("appMenuDisplayed", {
			handle: "appMenuHandle",
			constraint: "vertical",
			containment: ["appMenuDisplayed", "appMenuHidden"],
			dropOnEmpty:true,
			onUpdate: appMenu.update,
			onChange: appMenu.change
		});
	},

	change: function(){
		if($('appMenuHidden').children.length == 1)
			$('appMenu_emptyList').style.display = "";
		else
			$('appMenu_emptyList').style.display = "none";
	},

	update: function(element){
		var entries = Sortable.serialize(element.id).replace(/appMenuHidden/g,"").replace(/appMenuDisplayed/g,"").replace(/&/g,";").replace(/\[\]\=/g,"");
		if(element.id == "appMenuHidden"){
			var fore = entries.split(";");

			for(var i = 0; i < fore.length; i++)
				if($(fore[i]+'MenuEntry')) {
					$(fore[i]+'MenuEntry').style.display = 'none';
					$(fore[i]+'TabMinimizer').style.display = 'none';
				}

		}
		if(element.id == "appMenuDisplayed"){
			var fore = entries.split(";");

			for(var i = 0; i < fore.length; i++)
				if($(fore[i]+'MenuEntry')) {
					$(fore[i]+'MenuEntry').style.display = 'block';
					$(fore[i]+'TabMinimizer').style.display = 'block';
				}
		}

		rmeP("Menu", "", "saveAppMenuOrder", [element.id, entries]);
	}
}

function toggleTab(pluginName){
	if(pluginName == "morePlugins") {
		alert("Dieses Tab kann nicht minimiert werden.");
		return;
	}
	rme("Menu", '', 'toggleTab', pluginName, "refreshMenu();");
}

function refreshMenu(){
	new Ajax.Request("./interface/loadFrame.php?p=Menu&id=-1", {
	method: 'get',
	onSuccess: function(transport) {
    	$('navigation').update(transport.responseText);
    	setHighLight($(lastHighLight.id));
    }});
}

function querySt(ji) {
	hu = window.location.search.substring(1);
	gy = hu.split('&');
	for (i=0;i<gy.length;i++) {
		ft = gy[i].split('=');
		
		if (ft[0] == ji)
			return ft[1];
	}
}

function loadMenu(){
	new Ajax.Request("./interface/loadFrame.php?p=Menu&id=-1", {
	method: 'get',
	onSuccess: function(transport) {
		if(transport.responseText == "-1"){
			userControl.doTestLogin();
			overlayBox.show();
			return;
		} else overlayBox.hide();
		
    	if(!checkResponse(transport)) return;
    	
    	$('navigation').update(transport.responseText);
    	
    	if($('morePluginsMenuEntry')){
    		contentManager.loadFrame('contentLeft','morePlugins', -1, 0,'morePluginsGUI;-');
    		setHighLight($('morePluginsMenuEntry'));
    	}
    	
    	if(typeof querySt('plugin') != 'undefined'){

    		setHighLight($(querySt('plugin')+'MenuEntry'));
    		$('contentLeft').update('');
    		$('windows').update('');
    		contentManager.loadFrame('contentRight',querySt('plugin'),-1, 0,querySt('plugin')+'GUI;-');
    	} else {
    	
			new Ajax.Request('./interface/loadFrame.php?p=Desktop&id=1', {
			method: 'get',
			onSuccess: function(transport) {
		    	if(transport.responseText.search(/^error:/) == -1){
		    		lastLoadedRightPlugin = "Desktop";
		    		lastLoadedRight = 1;
		    		$('contentRight').update(transport.responseText);
		    		
		    		if($('DesktopMenuEntry')) setHighLight($('DesktopMenuEntry'));
		    		
		    		if(!$('morePluginsMenuEntry')) {
		    			new Ajax.Updater('contentLeft','./interface/loadFrame.php?p=Desktop&id=2');
			    		lastLoadedLeftPlugin = "Desktop";
			    		lastLoadedLeft = 2;
		    		}
		    	}
			}});
    	}
    	
    	new Ajax.Request("./interface/loadFrame.php?p=JSLoader&id=-1", {
    		method: "get",
    		onSuccess: function(transport){
    			$('DynamicJS').update('');
    			scripts = transport.responseText.split("\n");
    			for(i=0;i<scripts.length;i++) {
    				if(scripts[i] == "") continue;
    				s = document.createElement('script');
    				
    				src = document.createAttribute("src")
    				src.nodeValue = scripts[i];
    				s.setAttributeNode(src);
    				
    				t = document.createAttribute("type")
    				t.nodeValue = "text/javascript";
    				s.setAttributeNode(t);
    				
    				$('DynamicJS').appendChild(s);
    			}
    		}
    	});
    	
    	new Ajax.Request("./interface/rme.php?class=Menu&method=getActiveApplicationName&constructor=&parameters=",{onSuccess: function(transport){
    		setTitle(transport.responseText);
    	}});
    	//if($('messageLayer')) 
    	//if(typeof loadMessages == 'function') loadMessages();
	}});
}

function setTitle(to){
	if(!Interface.isDesktop) document.title = to;
	else $("wrapperHandler").update(to);
}

function showMenu(name){
	mouseIsOver[name] = true;
	//$(name).style.display='block';
	new Effect.Appear(name,{duration:0.1}); 
}

function setMouseOut(name){
	mouseIsOver[name] = false;
	setTimeout("hideMenu('"+name+"')",1000);
}

function hideMenu(name){
	if(mouseIsOver[name] == false) 
	//$(name).style.display='none';//
	new Effect.Fade(name,{duration:0.1}); 
	else setTimeout("hideMenu('"+name+"')",1000);
}

function setHighLight(obj){
	if(lastHighLight != null) lastHighLight.className = lastHighLight.className.replace(/ *theOne/,"");
	obj.className += " theOne";
	lastHighLight = obj;
}
