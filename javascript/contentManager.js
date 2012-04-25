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

var lastLoadedLeft        = -1;
var lastLoadedLeftPlugin  = "";
var lastLoadedLeftPage    = 0;

var lastLoadedRight		  = -1;
var lastLoadedRightPlugin = "";
var lastLoadedRightPage   = 0;

var lastLoadedScreen		  = -1;
var lastLoadedScreenPlugin = "";
var lastLoadedScreenPage   = 0;

var contentManager = {
	init: function(){
		Interface.init();
		Overlay.init();
		loadMenu();
		$('contentLeft').update();
		if (document.cookie == "") document.cookie = "CookieTest = Erfolgreich"
		if (document.cookie == "") alert("In Ihrem Browser sind Cookies deaktiviert.\nBitte aktivieren Sie Cookies, damit diese Anwendung funktioniert.");
		DesktopLink.init();
	},
	
	newSession: function(physion, plugin){
		Popup.load("Neue Sitzung", "Util", "-1", "newSession", [physion, plugin]);
	},
	
	/*backupLeftID: null,
	backupLeftPage: null,
	backupLeftPlugin:null,

	backupRightID: null,
	backupRightPlugin: null,
	backupRightPage: null,*/

	rootPath: null,

	backupFrames: new Object(),

	autoLogoutInhibitor: null,

	switchApplication: function(){
		Popup.closeNonPersistent();
		Popup.closePersistent();
		Menu.refresh();
		contentManager.emptyFrame("contentScreen");
		contentManager.loadDesktop();
		contentManager.loadJS();
		contentManager.loadTitle();
	},

	loadDesktop: function(){
		new Ajax.Request('./interface/loadFrame.php?p=Desktop&id=1', {
		method: 'get',
		onSuccess: function(transport) {
			if(transport.responseText.search(/^error:/) == -1){
				lastLoadedRightPlugin = "Desktop";
				lastLoadedRight = 1;
				$('contentRight').update(transport.responseText);

				if($('DesktopMenuEntry')) setHighLight($('DesktopMenuEntry'));

				if(!$('morePluginsMenuEntry')) {
					new Ajax.Request('./interface/loadFrame.php?p=Desktop&id=2', {
						onSuccess: function(transport){
							$('contentLeft').update(transport.responseText);
						}
					});
					lastLoadedLeftPlugin = "Desktop";
					lastLoadedLeft = 2;
				}
			}
		}});
	},
	
	loadPlugin: function(targetFrame, targetPlugin, bps, withId){
		contentManager.emptyFrame('contentLeft');
		contentManager.emptyFrame('contentRight');
		contentManager.emptyFrame('contentScreen');
		
		contentManager.loadFrame(targetFrame, targetPlugin, -1, 0, bps, function(){if(typeof withId != "undefined") contentManager.loadFrame("contentLeft", targetPlugin.substr(1), withId);});
		//$('windows').update('');
		Popup.closeNonPersistent();
		
		if($(targetPlugin+'MenuEntry'))
			setHighLight($(targetPlugin+'MenuEntry'));
	},

	loadJS: function(){
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
	},

	loadTitle: function(){
    	new Ajax.Request("./interface/rme.php?class=Menu&method=getActiveApplicationName&constructor=&parameters=",{onSuccess: function(transport){
			if(!Interface.isDesktop) document.title = transport.responseText;
			else $("wrapperHandler").update(transport.responseText);
    	}});
	},

	setRoot: function(path){
		contentManager.rootPath = path;
	},

	getRoot: function(){
		if(contentManager.rootPath == null)
			return "./";

		return contentManager.rootPath;
	},

	updateLine: function(FormID, elementID, CollectorClass){
		
		var CC = null;
		if(typeof CollectorClass != "undefined") CC = CollectorClass;
		else
			if($(FormID))
				CC = $(FormID).CollectorClass.value;
		

		if(CC == null)
			return;

		if(elementID != "-1"){
			new Ajax.Request('./interface/loadFrame.php?p='+CC+'&id='+elementID+'&type=main', {method:'get', onSuccess: function(transport){
				if(checkResponse(transport)) {
					if($('BrowserMain'+elementID))
						$('BrowserMain'+elementID).update(transport.responseText);

					else if($('Browser'+CC+elementID))
						$('Browser'+CC+elementID).update(transport.responseText);

					else if($('BrowserMainD'+elementID))
						$('BrowserMainD'+elementID).update(transport.responseText);
				}
			}});
		} else {
			contentManager.reloadFrameRight();
			if(TextEditor.open) TextEditor.hide();
		}
	},

	rightSelection: function(isMultiSelection, selectPlugin, callingPlugin, callingPluginID, callingPluginFunction){
		contentManager.loadFrame('contentRight', selectPlugin, -1, 0, selectPlugin+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+callingPlugin+','+callingPluginID+','+callingPluginFunction+','+lastLoadedRightPlugin);
		/*loadFrameV2(
			'contentRight',
			pluginRight,
			pluginRight+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+calledPlugin+','+calledPluginID+','+calledPluginFunction+','+lastLoadedRightPlugin+',contentLeft,'+lastLoadedLeftPlugin+','+pluginLeftID);
			*/
	},

	leftSelection: function(isMultiSelection, pluginRight, calledPlugin, calledPluginID, calledPluginFunction){
		contentManager.loadFrame('contentLeft', pluginRight, -1, 0, pluginRight+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+calledPlugin+','+calledPluginID+','+calledPluginFunction+','+lastLoadedRightPlugin);
		/*
			'contentLeft',
			pluginRight,
			pluginRight+'GUI;selectionMode:'+(isMultiSelection ? "multi" : "single")+'Selection,'+calledPlugin+','+calledPluginID+','+calledPluginFunction+','+lastLoadedRightPlugin+',contentLeft,'+lastLoadedLeftPlugin+','+pluginLeftID);*/
	},

	customSelection: function(targetFrame, callingPluginID, selectPlugin, selectJSFunction, addBPS){
		contentManager.loadFrame(targetFrame, selectPlugin, -1, 0, selectPlugin+'GUI;selectionMode:customSelection,'+selectJSFunction+','+callingPluginID+(typeof addBPS != "undefined" ? ";"+addBPS : ""));
	},

	setLeftFrame: function(plugin, id){
		lastLoadedLeft        = id;
		lastLoadedLeftPlugin  = plugin;
	},

	reloadOnNew: function(transport, plugin){
		contentManager.setLeftFrame(plugin, transport.responseText);
		contentManager.reloadFrame('contentLeft');
		contentManager.reloadFrame('contentRight');
	},

	reloadFrame: function(targetFrame, bps){
		if(targetFrame == "contentLeft" && lastLoadedLeftPlugin != "")
			contentManager.loadFrame("contentLeft", lastLoadedLeftPlugin, lastLoadedLeft, lastLoadedLeftPage, bps);

		if(targetFrame == "contentRight" && lastLoadedRightPlugin != "")
			contentManager.loadFrame("contentRight", lastLoadedRightPlugin, lastLoadedRight, lastLoadedRightPage, bps);

		if(targetFrame == "contentScreen" && lastLoadedScreenPlugin != "")
			contentManager.loadFrame("contentScreen", lastLoadedScreenPlugin, lastLoadedScreen, lastLoadedScreenPage, bps);
	},
	
	reloadFrameLeft: function(bps){
		contentManager.reloadFrame("contentLeft", bps);
	},
	
	reloadFrameRight: function(bps){
		contentManager.reloadFrame("contentRight", bps);
	},

	backupFrame: function(targetFrame, backupName, force){
		if(typeof force == "undefined") force = false;

		if(typeof contentManager.backupFrames[backupName] != "undefined" && contentManager.backupFrames[backupName] != null && !force) return;

		if(targetFrame == "contentLeft"){
			contentManager.backupFrames[backupName] = [lastLoadedLeft, lastLoadedLeftPlugin, lastLoadedLeftPage];
		}
		if(targetFrame == "contentRight"){
			contentManager.backupFrames[backupName] = [lastLoadedRight, lastLoadedRightPlugin, lastLoadedRightPage];
		}

	},

	restoreFrame: function(targetFrame, backupName, force){
		if(typeof force == "undefined") force = false;

		if(typeof contentManager.backupFrames[backupName] == "undefined" || contentManager.backupFrames[backupName] == null) {
			alert("Backup unknown");
			return;
		}
		if(contentManager.backupFrames[backupName][0] != -1 || (targetFrame == 'contentRight' && contentManager.backupFrames[backupName][1] != "") || force)
			contentManager.loadFrame(targetFrame, contentManager.backupFrames[backupName][1], contentManager.backupFrames[backupName][0], contentManager.backupFrames[backupName][2],contentManager.backupFrames[backupName][1]+"GUI;-","",true);
		else
			contentManager.emptyFrame(targetFrame);

		contentManager.backupFrames[backupName] = null;
	},

	emptyFrame: function(targetFrame){
		if(targetFrame == "contentLeft"){
			lastLoadedLeft        = -1;
			lastLoadedLeftPlugin  = "";
			lastLoadedLeftPage    = 0;
		}
		if(targetFrame == "contentRight"){
			lastLoadedRight       = -1;
			lastLoadedRightPlugin = "";
			lastLoadedRightPage   = 0;
		}
		if(targetFrame == "contentScreen"){
			lastLoadedScreen      = -1;
			lastLoadedScreenPlugin = "";
			lastLoadedScreenPage   = 0;
		}
		
		
		$(targetFrame).update("");
	},

	forwardOnePage: function(targetFrame){
		if(targetFrame == "contentLeft")
			contentManager.loadFrame(targetFrame, lastLoadedLeftPlugin, lastLoadedLeft, (lastLoadedLeftPage * 1) + 1);

		if(targetFrame == "contentRight")
			contentManager.loadFrame(targetFrame, lastLoadedRightPlugin, lastLoadedRight, (lastLoadedRightPage * 1) + 1);

		if(targetFrame == "contentScreen")
			contentManager.loadFrame(targetFrame, lastLoadedScreenPlugin, lastLoadedScreen, (lastLoadedScreenPage * 1) + 1);
	},

	backwardOnePage: function(targetFrame){
		if(targetFrame == "contentLeft")
			contentManager.loadFrame(targetFrame, lastLoadedLeftPlugin, lastLoadedLeft, lastLoadedLeftPage - 1);

		if(targetFrame == "contentRight")
			contentManager.loadFrame(targetFrame, lastLoadedRightPlugin, lastLoadedRight, lastLoadedRightPage - 1);

		if(targetFrame == "contentScreen")
			contentManager.loadFrame(targetFrame, lastLoadedScreenPlugin, lastLoadedScreen, lastLoadedScreenPage - 1);
	},

	loadPage: function(targetFrame, page){
		if(targetFrame == "contentLeft")
			contentManager.loadFrame(targetFrame, lastLoadedLeftPlugin, lastLoadedLeft, page);

		if(targetFrame == "contentRight")
			contentManager.loadFrame(targetFrame, lastLoadedRightPlugin, lastLoadedRight, page);

		if(targetFrame == "contentScreen")
			contentManager.loadFrame(targetFrame, lastLoadedScreenPlugin, lastLoadedScreen, page);
	},

	saveSelection: function(classe, classId, saveFunction, idToSave, targetFrame, bps){
		contentManager.rmePCR(classe, classId, saveFunction, idToSave, function() {contentManager.reloadFrame(targetFrame);}, bps)
	},

	editInPopup: function(plugin, withId, title, bps, options){
		contentManager.loadContent(plugin, withId, 0, bps, function(transport) {Popup.create(plugin, 'edit', title, options);Popup.update(transport, plugin, 'edit');});
	},

	loadInPopup: function(title, plugin, withId, page, bps){
		contentManager.loadContent(plugin, withId, page, bps, function(transport) {Popup.displayNamed(plugin, title, transport);});
	},

	loadContent: function(plugin, withId, page, bps, onSuccessFunction, hideError){
		new Ajax.Request('./interface/loadFrame.php?p='+plugin+(typeof withId != "undefined" ? '&id='+withId : "")+((typeof bps != "undefined" && bps != "") ? '&bps='+bps : "")+((typeof page != "undefined" && page != "") ? '&page='+page : ""), {onSuccess: function(transport){
			if(checkResponse(transport, hideError) && typeof onSuccessFunction != "undefined" && onSuccessFunction != "") onSuccessFunction(transport);
		}});
	},

	loadFrame: function(target, plugin, withId, page, bps, onSuccessFunction, hideError){
		if(typeof hideError == "undefined") hideError = false;

		if(typeof page == "undefined") page = 0;
		var arg = arguments;

		if(target == "contentRight"){
			lastLoadedRightPlugin = plugin;
			lastLoadedRightPage = page;
			lastLoadedRight = withId;
		}

		if(target == "contentLeft"){
			lastLoadedLeftPlugin = plugin;
			lastLoadedLeftPage = page;
			lastLoadedLeft = withId;
		}

		if(target == "contentScreen"){
			lastLoadedScreenPlugin = plugin;
			lastLoadedScreenPage = page;
			lastLoadedScreen = withId;
		}

		new Ajax.Request('./interface/loadFrame.php?p='+plugin+(typeof withId != "undefined" ? '&id='+withId : "")+((typeof bps != "undefined" && bps != "") ? '&bps='+bps : "")+((typeof page != "undefined" && page != "") ? '&page='+page : "")+"&r="+Math.random(), {onSuccess: function(transport){
			if(checkResponse(transport, hideError)) {
				$(target).update(transport.responseText);
				
				if(typeof onSuccessFunction != "undefined" && onSuccessFunction != "") onSuccessFunction(transport);
				
				Aspect.joinPoint("loaded", "contentManager.loadFrame", arg, transport.responseText);
			}
		}});

	},

	rmePCR: function(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps, responseCheck, onFailureFunction){
		var arg = arguments;
		if(typeof responseCheck == "undefined")
			responseCheck = true;

		if(typeof targetMethodParameters != "string"){
			for(var i = 0; i < targetMethodParameters.length; i++)
				targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i]).replace(/\'/g,'\\\'')+"'";

			targetMethodParameters = targetMethodParameters.join(",");
		}
		else targetMethodParameters = "'"+targetMethodParameters.replace(/\'/g,'\\\'')+"'";

		new Ajax.Request(contentManager.getRoot()+"interface/rme.php?rand="+Math.random(), {
		method: 'post',
		parameters: "class="+targetClass+"&construct="+targetClassId+"&method="+targetMethod+"&parameters="+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""),
		onSuccess: function(transport) {
			var check = checkResponse(transport);
			if(!responseCheck || check) {
				
				if(typeof onSuccessFunction == "string")
					eval(onSuccessFunction);

				if(typeof onSuccessFunction == "function")
					onSuccessFunction(transport);

				Aspect.joinPoint("loaded", "contentManager.rmePCR", arg, transport.responseText);
			}
			if(!check && typeof onFailureFunction == "function")
				onFailureFunction();
		}});
	},

	startAutoLogoutInhibitor: function(){
		if(contentManager.autoLogoutInhibitor) return;

		contentManager.autoLogoutInhibitor = true;

		new PeriodicalExecuter(function(pe) {
			contentManager.rmePCR('Menu','','autoLogoutInhibitor','');
		}, 300);
	},

	newClassButton: function(newClass, onSuccessFunction, targetFrame, bps){

		if(typeof targetFrame == "undefined") targetFrame = "contentLeft";

		contentManager.loadFrame(targetFrame, newClass, -1, 0, bps, onSuccessFunction);
/*
		new Ajax.Request('./interface/loadFrame.php?p='+newClass+'&id=-1'+(typeof bps != "undefined" ? "&bps="+bps : ""), {
		method: 'get',
		onSuccess: function(transport) {
			if(checkResponse(transport)) {
				if(typeof targetFrame == "undefined") targetFrame = "contentLeft";
				$(targetFrame).update(transport.responseText);
				//lastLoadedLeft = -1;
				//lastLoadedLeftPlugin = newClass;

				if(typeof onsuccessFunction != "undefined" && onsuccessFunction != "") onsuccessFunction();
			}
		}});*/
	},

	toggleFormFields: function(mode, fields, formID){
		if(mode == "hide"){
			for (var f = 0; f < fields.length; f++) {
				cField = $(fields[f]);
				if(typeof formID != "undefined")
					for(var i = 0; i < $(formID).elements.length; i++)
						if($(formID).elements[i].name == fields[f]) cField = $(formID).elements[i];


				if(cField && cField.parentNode && cField.parentNode)
					cField.parentNode.parentNode.style.display = "none";
				else alert(fields[f]+" does not exist!");
			}
		}

		if(mode == "show"){
			for (var f = 0; f < fields.length; f++) {
				cField = $(fields[f]);
				if(typeof formID != "undefined")
					for(var i = 0; i < $(formID).elements.length; i++)
						if($(formID).elements[i].name == fields[f]) cField = $(formID).elements[i];

				if(cField && cField.parentNode && cField.parentNode)
					cField.parentNode.parentNode.style.display = "";
				else alert(fields[f]+" does not exist!");
			}
		}
	},

	toggleFormFieldsTest: function(test, showOnTrue, showOnFalse, formID, showOnly){
		if(typeof showOnly == "undefined")
			showOnly = false;
		
		if(test){
			contentManager.toggleFormFields("show", showOnTrue, formID);
			if(!(showOnly))
				contentManager.toggleFormFields("hide", showOnFalse, formID);
		} else {
			if(!(showOnly))
				contentManager.toggleFormFields("hide", showOnTrue, formID);
			contentManager.toggleFormFields("show", showOnFalse, formID);
		}
	},
	
	formContent: function(formID){
		var fields = $j('#'+formID).serializeArray();
		
		$j.each(fields, function(key, value){ 
			if($j('#'+formID+' input[name='+value.name+']:checked').length > 0) value.value = '1'; 
		}); 
		
		$j.each($j('#'+formID+' input[type=checkbox]:not(:checked)'), function(key, value){ 
			fields.push({name: value.name, value: '0'}); 
		});
		
		return fields;
	}/*,
	
	tinyMCEFileBrowser: function(field_name, url, type, win) {
		
		//alert("Field_Name: " + field_name + "nURL: " + url + "nType: " + type + "nWin: " + win); // debug/testing
		
		tinyMCE.activeEditor.windowManager.open({
			file : "./interface/rme.php?rand="+Math.random()+"&class=FileManager&construct=&method=getTinyMCEManager&parameters='"+field_name+"','"+type+"'",
			title : 'Dateimanager',
			width : 800,  // Your dimensions may differ - toy around with them!
			height : 450,
			resizable : "yes",
			inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
			close_previous : "no"
		}, {
			window : win,
			input : field_name
		});
		return false;
	}*/
}