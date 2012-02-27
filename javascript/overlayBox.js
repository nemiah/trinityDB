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
 
var overlayBox = {
	isInit: false,
	white: false,
	loginBox: false,
	initWhite: false,
	
	init: function(){
		Event.observe(window, "resize", overlayBox.fit);
		
		overlayBox.isInit = true;
		overlayBox.fit();
		new Effect.Appear($('overlay'), {duration: 0.5});
		overlayBox.initWhite = true;
		
		setTimeout("$('container').style.display = 'block'",500);
		//new Effect.Fade($('overlay'), {duration: 1, delay:0.2, queue: "end"});
	},
	
	show: function(){
		if(!overlayBox.loginBox) {
			new Effect.Appear("boxInOverlay", {duration: 0.2});
			overlayBox.loginBox = true;
			
			setTimeout("$('loginUsername').focus()",300);
			var data = cookieManager.getCookie('userLoginData');
			if(data != -1) {
				data = data.split(":");
				$('loginUsername').value = data[0];
				$('loginPassword').value = ";;cookieData;;";
				$('loginSHAPassword').value = data[1];
				$('saveLoginData').checked = true;
			}
		}
		if(!overlayBox.white && !overlayBox.initWhite) {
			new Effect.Appear($('overlay'), {duration: 0.5});
			overlayBox.white = true;
			
			setTimeout("$('contentLeft').update('')",600);
			setTimeout("$('contentRight').update('')",600);
			
			
		}
	},
	
	hide: function(){

		if(overlayBox.initWhite) {
			setTimeout("new Effect.Fade($('overlay'), {duration: 1})",600);
			//new Effect.Fade($('overlay'), {duration: 0.5, delay:0.6});
			overlayBox.initWhite = false;
		}

		if(overlayBox.white) {
			new Effect.Fade($('overlay'), {duration: 1});
			overlayBox.white = false;
		}
		if(overlayBox.loginBox) {
			new Effect.Fade("boxInOverlay", {duration: 0.2});
			overlayBox.loginBox = false;
		}
	},
	
	fit: function(){
	
		var size = overlayBox.getPageSize();
		$('overlay').style.width = size[0]+'px';
		$('overlay').style.height = size[1]+'px';

		$('boxInOverlay').style.top = "20%";
		$('boxInOverlay').style.left = (size[0]/2 - 200)+"px";
	},
	
    getPageSize: function(windowSize) {
	        
	     var xScroll, yScroll;
		
		if (window.innerHeight && window.scrollMaxY) {	
			xScroll = window.innerWidth + window.scrollMaxX;
			yScroll = window.innerHeight + window.scrollMaxY;
		} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			xScroll = document.body.scrollWidth;
			yScroll = document.body.scrollHeight;
		} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			xScroll = document.body.offsetWidth;
			yScroll = document.body.offsetHeight;
		}
		
		var windowWidth, windowHeight;
		
		if (self.innerHeight) {	// all except Explorer
			if(document.documentElement.clientWidth){
				windowWidth = document.documentElement.clientWidth; 
			} else {
				windowWidth = self.innerWidth;
			}
			windowHeight = self.innerHeight;
		} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
			windowWidth = document.documentElement.clientWidth;
			windowHeight = document.documentElement.clientHeight;
		} else if (document.body) { // other Explorers
			windowWidth = document.body.clientWidth;
			windowHeight = document.body.clientHeight;
		}	
		
		if(typeof windowSize != "undefined" && windowSize) return [windowWidth,windowHeight];
		
		// for small pages with total height less then height of the viewport
		if(yScroll < windowHeight){
			pageHeight = windowHeight;
		} else { 
			pageHeight = yScroll;
		}
	
		// for small pages with total width less then width of the viewport
		if(xScroll < windowWidth){	
			pageWidth = xScroll;		
		} else {
			pageWidth = windowWidth;
		}

		return [pageWidth,pageHeight];
	}
}