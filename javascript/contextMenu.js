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
 
var contextMenu = {
	container: null,
	fakeContainer: null,
	headerText: "<a href=\"javascript:contextMenu.stop();\" class=\"closeContextMenu backgroundColor0 borderColor0\" />X</a>",
	toButton: null,
	goUp: false,
	
	init: function(){
		//var b = document.getElementsByTagName("body");
		
		var cMDiv = Builder.node('div', {id:"cMDiv", className:"contextMenu backgroundColor0 borderColor1", style:"display:none;width:200px;"});
		var fakecMDiv = Builder.node('div', {id:"fakecMDiv", style:"top:-10000px;width:200px;", className:'contextMenu backgroundColor0 borderColor1'});
		
		var cMHeader = Builder.node('div', {id:"cMHeader", className:"backgroundColor1"});
		var fakecMHeader = Builder.node('div', {id:"fakecMHeader", className:"backgroundColor1 cMHeader"});
		
		var cMData = Builder.node('div', {id:"cMData"});
		var fakecMData = Builder.node('div', {id:"fakecMData"});
		
		cMDiv.appendChild(cMHeader);
		cMDiv.appendChild(cMData);
		
		fakecMDiv.appendChild(fakecMHeader);
		fakecMDiv.appendChild(fakecMData);
		
		
		$('contentLeft').appendChild(cMDiv);
		$('contentLeft').appendChild(fakecMDiv);
		//contextMenu.toButton.parentNode.appendChild(cMDiv);
		contextMenu.container = cMDiv;
		contextMenu.fakeContainer = fakecMDiv;

		$('cMDiv').style.position = "absolute";
		fakecMDiv.style.position = "absolute";
		
		new Draggable($('cMDiv'), {handler: $('cMHeader')});
		$('cMHeader').innerHTML = contextMenu.headerText+"contextMenu";
	},
	
	reInit: function(){
		contextMenu.container.style.display = 'none';
	},
	
	remove: function(){
		//var b = document.getElementsByTagName("body");
		//if($(contextMenu.container.id)) contextMenu.toButton.parentNode.removeChild(contextMenu.container);
		if(contextMenu.container != null && $(contextMenu.container.id)){
			$('contentLeft').removeChild(contextMenu.container);
		}
		if(contextMenu.fakeContainer != null && $(contextMenu.fakeContainer.id)){
			$('contentLeft').removeChild(contextMenu.fakeContainer);
		}
			
		contextMenu.container = null;
		contextMenu.fakeContainer = null;
		
		contextMenu.toButton = null;
	},
	
	stop: function(transport){
		if(transport && transport.responseText != "") alert(transport.responseText);
	
		new Effect.Fade(contextMenu.container,{duration: 0.4});
		setTimeout("contextMenu.remove();",450);
		
	},
	
	saveSelection: function(saveTo, identifier, key, onSuccessFunction){
		new Ajax.Request('./interface/rme.php?class='+saveTo+"&method=saveContextMenu&constructor='-1'&parameters='"+identifier+"','"+key+"'", {onSuccess: function(transport){
			contextMenu.stop(transport);
			if(typeof onSuccessFunction != "undefined") eval(onSuccessFunction);
		}});
	},
	
	appear: function(transport){
		$('cMData').update(transport.responseText);
		$('fakecMData').update(transport.responseText);
		
		if(contextMenu.goUp) $('cMDiv').style.top = (Observer.lastMouseY - contextMenu.fakeContainer.offsetHeight)+"px";
		
		new Effect.Appear(contextMenu.container,{duration: 0.4});
	},
	
	update: function(targetClass, identifier, label){
		$('cMHeader').innerHTML = contextMenu.headerText+""+label;
		new Ajax.Updater('cMData','./interface/rme.php?class='+targetClass+"&method=getContextMenuHTML&constructor=''&parameters='"+identifier+"'");
	},
	
	start: function(toButton, targetClass, identifier, label, leftOrRight, upOrDown){
		if(!$('cMHeader')) 
			contextMenu.container = null;

		contextMenu.toButton = toButton;
		contextMenu.goUp = (upOrDown && upOrDown == "up");
	
		if(contextMenu.container == null) contextMenu.init();
		else contextMenu.reInit();

		$('cMHeader').innerHTML = contextMenu.headerText+""+label;
		$('fakecMHeader').innerHTML = contextMenu.headerText+""+label;
		
		$('cMDiv').style.top = Observer.lastMouseY+"px";
		if(!leftOrRight || leftOrRight == "right") $('cMDiv').style.left = Observer.lastMouseX+"px";
		else if(leftOrRight && leftOrRight == "left") $('cMDiv').style.left = (Observer.lastMouseX - $('cMDiv').style.width.replace(/px/,""))+"px";
		
		new Ajax.Request('./interface/rme.php?class='+targetClass+"&method=getContextMenuHTML&constructor=''&parameters='"+identifier+"'", {onSuccess: contextMenu.appear});
	}
}