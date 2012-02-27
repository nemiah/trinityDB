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

var Aspect = {
	pointCuts: new Array(),

	joinPoint: function(mode, method, arg, responseText){

		arg = Array.prototype.slice.call(arg);

		if(!Aspect.pointCuts[mode]) return null;
		if(!Aspect.pointCuts[mode][method]) return null;

		for(var i = 0; i < Aspect.pointCuts[mode][method].length; i++)
			Aspect.pointCuts[mode][method][i](arg, responseText);
	},

	registerPointCut: function(mode, pointCut, advice){
		if(!Aspect.pointCuts[mode])
			Aspect.pointCuts[mode] = new Array();

		if(!Aspect.pointCuts[mode][pointCut])
			Aspect.pointCuts[mode][pointCut] = new Array();

		for(var i = 0; i < Aspect.pointCuts[mode][pointCut].length; i++)
			if(Aspect.pointCuts[mode][pointCut][i] == advice) return;

		Aspect.pointCuts[mode][pointCut][Aspect.pointCuts[mode][pointCut].length] = advice;
	},

	unregisterPointCut: function(mode, pointCut){
		Aspect.pointCuts[mode][pointCut] = new Array();
	},

	registerOnLoadFrame: function(targetFrame, plugin, isNewEntry, advice){
		if(typeof isNewEntry == "undefined") isNewEntry = true;

		Aspect.registerPointCut("loaded", "contentManager.loadFrame", function(a, responseText){
			if(a[0] != targetFrame) return;
			if(a[1] != plugin) return;

			if(isNewEntry && a[2] != "-1") return;
			if(!isNewEntry && a[2] == "-1") return;

			advice(a, responseText);
		});
	},

	registerOnRmePCR: function(targetClass, targetMethod, advice){
		Aspect.registerPointCut("loaded", "contentManager.rmePCR", function(a, responseText){
			//alert(responseText);//Belegvorlage,2,createAuftragWithBeleg,'A',contentManager.loadFrame('contentLeft', 'Auftrag', transport.responseText);,
			if(a[0] != targetClass) return;
			if(a[2] != targetMethod) return;

			advice(a, responseText);
		});
	}
}
