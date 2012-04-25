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
var Popup = {
	windowsOpen: 0,
	zIndex: 2500,

	lastPopups: Array(),
	lastSidePanels: Array(),
	
	presets: {
		large: {hPosition: "center", width:1000},
		center: {hPosition: "center"}
	},

	load: function(title, targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, bps, name, options){
		if(typeof name == "undefined")
			name = "edit";
		
		if(typeof targetPluginMethodParameters == "undefined")
			targetPluginMethodParameters = new Array();
		
		var arrayCopy = targetPluginMethodParameters.slice(0, targetPluginMethodParameters.length); //because targetPluginMethodParameters is only a reference
		 
		contentManager.rmePCR(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, 'Popup.displayNamed(\''+name+'\', \''+title+'\', transport, \''+targetPlugin+'\''+(typeof options != "undefined" ? ", "+options : "")+');', bps);
		 
		Popup.lastPopups[targetPlugin] = [title, targetPlugin, targetPluginID, targetPluginMethod, arrayCopy];
	},

	refresh: function(targetPlugin, bps){
		var values = Popup.lastPopups[targetPlugin];
		var arrayCopy = values[4].slice(0, values[4].length); //because targetPluginMethodParameters is only a reference
		//Popup.lastPopups[targetPlugin] = [title, targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters];
		contentManager.rmePCR(targetPlugin, values[2], values[3], arrayCopy, 'Popup.displayNamed(\'edit\', \''+values[0]+'\', transport, \''+targetPlugin+'\');', bps);
	},

	display: function(name, transport){
		var ID = Math.random();

		Popup.create(ID,"rand",name);
		Popup.update(transport, ID, "rand");
	},

	displayNamed: function(name, title, transport, type, options){
		if(typeof type == "undefined")
			type = "";
		
		Popup.create(type,name,title, options);
		Popup.update(transport, type, name);
	},

	sidePanel: function(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters){
		targetPluginContainer = "editDetails"+targetPlugin;
		
		if($j('#'+targetPluginContainer+'SidePanel').length > 0){
			Popup.sidePanelClose(targetPlugin);
			return;
		}
	
		$j('#windows').append('<div id="'+targetPluginContainer+'SidePanel" style="display:none;top:'+($j("#"+targetPluginContainer).position().top + 10)+'px;left:'+($j("#"+targetPluginContainer).position().left+  $j("#"+targetPluginContainer).width())+'px;" class="backgroundColor0 borderColor1 popupSidePanel"></div>');
		
		$j("#"+targetPluginContainer).bind("dragstart", function(event, ui) {
			$j('#'+targetPluginContainer+'SidePanel').fadeOut();
		});
		
		$j("#"+targetPluginContainer).bind("dragstop", function(event, ui) {
			$j('#'+targetPluginContainer+'SidePanel').css({top: $j("#"+targetPluginContainer).position().top + 10, left: $j("#"+targetPluginContainer).position().left + $j("#"+targetPluginContainer).width()}).fadeIn();
		});
		
		//$j("#"+targetPluginContainer).bind("DOMNodeRemoved", function(event, ui) {
			//$j('#'+targetPluginContainer+'SidePanel').remove();
		//});
		
		Popup.lastSidePanels[targetPlugin] = [targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters.slice(0, targetPluginMethodParameters.length)];
		
		contentManager.rmePCR(targetPlugin, targetPluginID, targetPluginMethod, targetPluginMethodParameters, function(transport){
			$j('#'+targetPluginContainer+'SidePanel').html(transport.responseText).fadeIn();
		});
	},

	sidePanelClose: function(parentWindowID){
		if($j('#editDetails'+parentWindowID+'SidePanel').length == 0)
			return;
		
		$j('#editDetails'+parentWindowID+'SidePanel').fadeOut(300, function(){$j(this).remove();});
	},
	
	sidePanelRefresh: function(targetPlugin){
		var values = Popup.lastSidePanels[targetPlugin];
		
		contentManager.rmePCR(targetPlugin, values[1], values[2], values[3].slice(0, values[3].length), function(transport){$j('#editDetails'+targetPlugin+'SidePanel').html(transport.responseText)});
	},

	create: function(ID, type, name, options){
		if($(type+'Details'+ID)) return;
		var size = Overlay.getPageSize(true);
		var width = 400;
		var hasX = true;
		var persistent = false;
		var targetContainer = "windows";
		
		var top = null;
		var right = null;

		if(typeof options == "object"){
			if(options.width)
				width = options.width;

			if(options.hPosition && options.hPosition == "center")
				right = size[0] / 2 - width / 2;

			if(options.hPosition && options.hPosition == "right")
				right = 10;

			if(options.hPosition && options.hPosition == "left")
				right = size[0] - width - 2 - 10;

			if(typeof options.hasX == "boolean")
				hasX = options.hasX;

			if(options.top)
				top = options.top;
			
			if(options.persistent)
				persistent = options.persistent;
		}
		
		if(persistent)
			targetContainer = "windowsPersistent";
		
		
		if(top == null)
			top = size[0] <= 1124 ? (66 + $(targetContainer).childNodes.length * 40) : (100 + $(targetContainer).childNodes.length * 40);
		
		if(right == null)
			right = size[0] <= 1124 ? (0) : (410 + $(targetContainer).childNodes.length * 20);
		
		//if($(targetContainer).firstChild == null) Popup.windowsOpen = 0;
		var element = Builder.node(
			"div",
			{
				id: type+'Details'+ID,
				style: 'display:none;top:'+top+'px;right:'+right+'px;width:'+width+'px;z-index:'+Popup.zIndex,
				"class": "borderColor1 popup"
			}, [
				Builder.node("div", {"class": "backgroundColor1 cMHeader", id: type+'DetailsHandler'+ID}, [
					Builder.node("a", {id: type+"DetailsCloseWindow"+ID, "class": "closeContextMenu backgroundColor0 borderColor0", style:"cursor:pointer;"+(hasX ? "" : "display:none;")}, ["X"])
					, name]),
				Builder.node("div", {"class": "backgroundColor0", style: "clear:both;", id: type+'DetailsContent'+ID})
			]);

		$(targetContainer).appendChild(element);
		
		new Draggable($(type+'Details'+ID), {handle: $(type+'DetailsHandler'+ID)});
		Event.observe(type+'DetailsCloseWindow'+ID, 'click', function() {Popup.close(ID, type);});
		//Event.observe(type+'Details'+ID, 'click', function(event) {Popup.updateZ(event.target);});

	},

	close: function(ID, type){
		//new Effect.Fade(type+'Details'+ID,{duration: 0.4});
		var hasTinyMCE = $j("#"+type+'Details'+ID+" textarea[name=tinyMCEEditor]");
		if(hasTinyMCE.length){
			tinyMCE.execCommand("mceFocus", false, hasTinyMCE.attr("id"));                    
			tinyMCE.execCommand("mceRemoveControl", false, hasTinyMCE.attr("id"));
		}
		
		Popup.sidePanelClose(ID);
		
		//Popup.windowsOpen--;
		if($j("#"+type+'Details'+ID).length)
			$j("#"+type+'Details'+ID).fadeOut(400, function(){
				$j(this).remove();
			});//$('windows').removeChild($(type+'Details'+ID));
		Overlay.hideDark(0.1);
	},

	update: function(transport, ID, type){
		if(!$(type+'Details'+ID)) Popup.create(ID, type);
		if(!checkResponse(transport)) return;

		$(type+'DetailsContent'+ID).update(transport.responseText);
		Popup.show(ID, type);
		//Popup.windowsOpen++;
	},

	show: function(ID, type){
		if($(type+'Details'+ID).style.display == "none")
			new Effect.Appear(type+'Details'+ID,{duration: 0.4});
	},
	
	closeNonPersistent: function(){
		$j.each($j('#windows').children(), function(k, v){
			var P = $j(v).attr("id").split("Details");
			Popup.close(P[1], P[0]);
		});
	},
	
	closePersistent: function(){
		$j.each($j('#windowsPersistent').children(), function(k, v){
			var P = $j(v).attr("id").split("Details");
			Popup.close(P[1], P[0]);
		});
	}
}