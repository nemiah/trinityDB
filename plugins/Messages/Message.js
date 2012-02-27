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
SysMessDiv = document.createElement("div");
SysMessDiv.id = "messageLayer";

SysMessDiv.style.position = "fixed";
SysMessDiv.style.left = "100px";
SysMessDiv.style.bottom = "0px";
SysMessDiv.style.backgroundColor = "white";
SysMessDiv.style.padding = "5px";
SysMessDiv.style.borderRight = "1px solid #6868C9";
SysMessDiv.style.borderTop = "1px solid #6868C9";
SysMessDiv.style.borderLeft = "1px solid #6868C9";
SysMessDiv.style.width = "50px";
SysMessDiv.style.height = "18px";
SysMessDiv.style.overflow = "hidden";

FrameReloader = document.createElement("div");
FrameReloader.id = "FrameReloader";

FrameReloader.style.position = "fixed";
FrameReloader.style.left = "181px";
FrameReloader.style.bottom = "0px";
FrameReloader.style.backgroundColor = "white";
FrameReloader.style.padding = "5px";
FrameReloader.style.borderRight = "1px solid #6868C9";
FrameReloader.style.borderTop = "1px solid #6868C9";
FrameReloader.style.borderLeft = "1px solid #6868C9";
FrameReloader.style.width = "64px";
FrameReloader.style.height = "18px";
FrameReloader.style.overflow = "hidden";
FrameReloader.innerHTML = "<img src=\"./images/i2/refresh.png\" style=\"margin-right:5px;\" class=\"mouseoverFade\" onclick=\"reloadLeftFrame();\" /><img src=\"./images/i2/refresh.png\" class=\"mouseoverFade\" style=\"margin-right:5px;\" onclick=\"reloadRightFrame();\" /><img src=\"./images/i2/refresh.png\" class=\"mouseoverFade\" onclick=\"DesktopLink.loadContent();\" />";

var JSR = {
	lastReloaded: "",

	init: function() {
		JSReloaderDiv = document.createElement("div");
		JSReloaderDiv.id = "JSReloader";
		
		JSReloaderDiv.style.position = "fixed";
		JSReloaderDiv.style.left = "270px";
		JSReloaderDiv.style.bottom = "0px";
		if($('WorkflowContainer'))
			JSReloaderDiv.style.bottom = "50px";
		JSReloaderDiv.style.backgroundColor = "white";
		JSReloaderDiv.style.padding = "5px";
		JSReloaderDiv.style.borderRight = "1px solid #6868C9";
		JSReloaderDiv.style.borderTop = "1px solid #6868C9";
		JSReloaderDiv.style.borderLeft = "1px solid #6868C9";
		JSReloaderDiv.style.width = "41px";
		JSReloaderDiv.style.height = "18px";
		JSReloaderDiv.style.overflow = "hidden";

		if($('WorkflowContainer'))
			SysMessDiv.style.bottom = "50px";

		if($('WorkflowContainer'))
			FrameReloader.style.bottom = "50px";

		var html = "<img src=\"./images/i2/go-up.png\" style=\"margin-right:5px;\" class=\"mouseoverFade\" onclick=\"JSR.show();\" /><img src=\"./images/i2/refresh.png\" class=\"mouseoverFade\" onclick=\"JSR.reloadLast();\" /><br /><br />";
		
		var siblings = $('DynamicJS').childNodes;
		for(var i = 0;i < siblings.length; i++) {
			fN = siblings[i].src.split("=")[0].split("?")[0].split("/");
			html = html + "<a href=\"#\" onclick=\"JSR.reloadJSFile('"+fN[fN.length - 1]+"');\">"+fN[fN.length - 1]+"</a><br />";
		}
		
		$("container").appendChild(JSReloaderDiv);
		
		$('JSReloader').innerHTML = html;
	},
	
	show: function() {
		$('JSReloader').style.width = '150px';
		$('JSReloader').style.height = '365px';
		$('JSReloader').style.overflow = "auto";
	},
	
	hide: function() {
		$('JSReloader').style.width = '41px';
		$('JSReloader').style.height = '18px';
		$('JSReloader').style.overflow = "hidden";
	},
	
	reloadJSFile: function(filename){
		var siblings = $('DynamicJS').childNodes;
		
		var foundNode = null;
		
		for(var i = 0;i < siblings.length; i++) {
			fN = siblings[i].src.split("=")[0].split("?")[0].split("/");
			if(filename == fN[fN.length-1]) foundNode = siblings[i];
		}
		
		if(foundNode != null) {
			fileN = foundNode.src.split("?")[0];
			randomNumber = foundNode.src.split("=")[1] * 1 + 1;
			$('DynamicJS').removeChild(foundNode);
			
			var script = document.createElement("script");
			script.src = fileN+"?r="+randomNumber;
			script.type = "text/javascript";
			
			$('DynamicJS').appendChild(script);
			
			JSR.lastReloaded = filename;
		}
		
		JSR.hide();
	},
	
	reloadLast: function(){
		if(JSR.lastReloaded == "") return;
		JSR.reloadJSFile(JSR.lastReloaded);
	}
}


Event.observe(window, 'click', function() {
	if(!$('messageLayer')) {
		$("container").appendChild(SysMessDiv);
		$("container").appendChild(FrameReloader);
		loadMessages();
		JSR.init();
	}
});





function loadMessages(){
	if(!$('messageLayer')) $("container").appendChild(SysMessDiv);
	new Ajax.Updater('messageLayer', './interface/loadFrame.php?p=Messages');
}
function showBox(){
	$('messageLayer').style.width = '600px';
	$('messageLayer').style.left = '0px';
	$('messageLayer').style.height = '365px';
	$('messageLayer').style.overflow = "auto";
}

function hideBox(){
	$('messageLayer').style.left = '100px';
	$('messageLayer').style.width = '50px';
	$('messageLayer').style.height = '18px';
	$('messageLayer').style.overflow = "hidden";
}
