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
	zIndex: 200,

	display: function(name, transport){
		var ID = Math.random();

		Popup.create(ID,"rand",name);
		Popup.update(transport, ID, "rand");
	},

	displayNamed: function(name, title, transport){
		Popup.create("",name,title);
		Popup.update(transport, "", name);
	},

	create: function(ID, type, name){
		if($(type+'Details'+ID)) return;
		var size = overlayBox.getPageSize(true);

		var top = size[0] <= 1124 ? (66 + $('windows').childNodes.length * 40) : (100 + $('windows').childNodes.length * 40);
		var right = size[0] <= 1124 ? (0) : (410 + $('windows').childNodes.length * 20);

		if($('windows').firstChild == null) Popup.windowsOpen = 0;
		var element = Builder.node(
			"div",
			{
				id: type+'Details'+ID,
				style: 'display:none;position:fixed;top:'+top+'px;right:'+right+'px;width:400px;border-style:solid;border-width:1px;',
				className: "borderColor1"
			}, [
				Builder.node("div", {className: "backgroundColor1 cMHeader", id: type+'DetailsHandler'+ID}, [Builder.node("a", {id: type+"DetailsCloseWindow"+ID, className: "closeContextMenu backgroundColor0 borderColor0", style:"cursor:pointer;"}, ["X"]), name]),
				Builder.node("div", {className: "backgroundColor0", style: "clear:both;", id: type+'DetailsContent'+ID})
			]);

		$('windows').appendChild(element);
		new Draggable($(type+'Details'+ID), {handle: $(type+'DetailsHandler'+ID)});
		Event.observe(type+'DetailsCloseWindow'+ID, 'click', function(event) {Popup.close(ID, type);});
		//Event.observe(type+'Details'+ID, 'click', function(event) {Popup.updateZ(event.target);});

	},

	close: function(ID, type){
		new Effect.Fade(type+'Details'+ID,{duration: 0.4});
		Popup.windowsOpen--;
		if($(type+'Details'+ID)) $('windows').removeChild($(type+'Details'+ID));
	},

	update: function(transport, ID, type){
		if(!$(type+'Details'+ID)) Popup.create(ID, type);
		if(!checkResponse(transport)) return;

		$(type+'DetailsContent'+ID).update(transport.responseText);
		Popup.show(ID, type);
		Popup.windowsOpen++;
	},

	show: function(ID, type){
		if($(type+'Details'+ID).style.display == "none")
			new Effect.Appear(type+'Details'+ID,{duration: 0.4});
	}
}