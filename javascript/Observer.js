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

 var Observer = {
	
	lastMouseX: 0,
	lastMouseY: 0,
	handler: null,
	ismsie: true,
	
	mouseMove: function(event){
	    event = Observer.chkEvH(event);
	
	    if (Observer.ismsie) {
	
			// ab MSIE 6
		    if (document.documentElement && document.documentElement.scrollTop) {
		        var yFromTop = document.documentElement.scrollTop;
		    } else {
		        var yFromTop = document.body.scrollTop;
		    }
	    } else if (self.pageYOffset) {
	    	var yFromTop = self.pageYOffset;
	    } else {
	    	var yFromTop = 0;
	    }
	
	    Observer.lastMouseX = event.clientX;
	    Observer.lastMouseY = yFromTop + event.clientY;

	    return false;
	},
	
	chkEvH: function(event) {
		if (typeof event == 'undefined') event = window.event;
		if (typeof event.layerX == 'undefined') event.layerX = event.offsetX;
		if (typeof event.layerY == 'undefined') event.layerY = event.offsetY;
		return event;
	}
	

}

Event.observe(document, "mousemove", Observer.mouseMove);