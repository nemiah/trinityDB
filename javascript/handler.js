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

var cookieManager = {
	getCookie: function(cookieName) {
		var cs = document.cookie.split("; ");
		for(var i = 0;i<cs.length;i++){
			var subcs = cs[i].split("=");
			if(subcs[0] == cookieName) return subcs[1];
		}
		
		return -1;
	}
}

var GlobalMessages = {
	E001: "Die Datenbank-Tabelle dieses Plugins wurde noch nicht angelegt.\nBitte verwenden Sie das Installations-Plugin im Administrationsbereich.",
	E002: "Keine Datenbank ausgewählt.\nBitte verwenden Sie das Installations-Plugin im Administrationsbereich.",
	E003: "Die Datenbank-Zugangsdaten sind falsch.\nBitte verwenden Sie das Installations-Plugin im Administrationsbereich.",
	E004: "Wurde dieses Plugin aktualisiert? Ein oder mehrere Felder existieren nicht in der Datenbank.\nBitte aktualisieren Sie über das Installations-Plugin im Administrationsbereich die Tabelle des Plugins.",
	E005: "Zugriff verweigert!",
	E006: "Sie haben keine Berechtigung, diese Seite zu betrachten!",
	E007: function(plugin) {return "Class "+plugin+"GUI needs to implement the interface iGUIHTML2 or iGUIHTMLMP2!";},
	//E008: "The interface iHTMLGUI is deprecated and should not be used. Use iHTMLGUI2 instead.",
	E009: function(Klasse) {return "Die Klasse "+Klasse+" konnte nicht gefunden werden. Die Anfrage wird abgebrochen.";},
	
	E010: function(Extension) {return "Die PHP-Erweiterung '"+Extension+"' wurde nicht geladen. Anfrage wird abgebrochen.";},
	E011: "Dieser Datensatz existiert nicht (mehr). Eventuell wurde er gelöscht.",
	E012: function(Value) {return "Der Wert "+Value+" wurde bereits vergeben";},
	
	E020: "Das Verzeichnis kann nicht gelöscht werden, es ist nicht leer!",
	E021: "Die Datei kann wegen fehlender Berechtigung nicht gelöscht werden!",
	
	E030: "Das Interface iCategorizer wird von der Klasse nicht implementiert.",
	E031: "Sie müssen gleiche Typen verwenden",
	E032: "Der zweite Eintrag muss eine höhere Nummer besitzen als der Erste.",
	E033: "Bitte wählen Sie eine Kategorie",
	
	E040: function(classMethod) {return "Die Methode "+classMethod+" existiert nicht";},
	
	A001: "Bitte geben Sie eine Zahl ein",
	A002: "Der letzte Datensatz wurde erreicht",
	A003: "Der erste Datensatz wurde erreicht",
	A004: "Ein Datensatz mit dieser ID existiert nicht",
	A005: "Ihre Sitzung ist abgelaufen, bitte loggen Sie sich erneut ein.",


	M001: "Änderungen gespeichert",
	M002: "Daten gespeichert",
	M003: "Datensatz erstellt",
	
	C001: "Achtung: Die Seite muss neu geladen werden, damit die Einstellungen wirksam werden. Jetzt neu laden?"
}

Ajax.Responders.register({
	onCreate: function(){
		Interface.startLoading();
	},

	onFailure: function(transport) {
		alert("An error occured: "+transport.responseText);
	},
	
	onComplete: function(){
		Interface.endLoading();
	}
});

function checkResponse(transport, hideError) {
	if(typeof hideError == "undefined") hideError = false;

	if(transport.responseText == "SESSION EXPIRED"){
		alert(GlobalMessages.A005);
		window.location.reload();
		return;
	}
	
	if(transport.responseText.search(/^error:/) > -1){
		eval("var message = "+transport.responseText.replace(/error:/,""));
		alert("Es ist ein Fehler aufgetreten:\n"+message);
		//alert("Es ist ein Fehler aufgetreten:\n"+transport.responseText.replace(/error:/,""));
		return false;
	}
	if(transport.responseText.search(/^alert:/) > -1){
		eval("var message = "+transport.responseText.replace(/alert:/,""));
		alert(message);
		return false;
	}
	if(transport.responseText.search(/^message:/) > -1){
		eval("var message = "+transport.responseText.replace(/message:/,""));
		
		if(navigator && navigator.platform != "iPod" && navigator.platform != "iPhone") showMessage(message);
		else alert(message);
		return true;
	}
	if(transport.responseText.search(/Fatal error/) > -1){
		if(!hideError) alert(transport.responseText.replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, ''));
		return false;
	}
	if(transport.responseText.search("FPDF error:") > -1){
		alert(transport.responseText.replace("FPDF error:","").replace(/<br \/>/g,"\n").replace(/<b>/g,"").replace(/<\/b>/g,"").replace(/<code>/g,"").replace(/<\/code>/g,"").replace(/&gt;/g,">").replace(/^\s+/, '').replace(/\s+$/, ''));
		return false;
	}
	
	return true;
}

/**
 * @deprecated
 **/
function rme(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps){
	//alert("JS function rme() deprecated, use contentManager.rmePCR instead!");
 	if(typeof targetMethodParameters != "string"){
 		for(var i=0;i<targetMethodParameters.length;i++)
 			targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
 
 	new Ajax.Request("./interface/rme.php?class="+targetClass+"&constructor="+targetClassId+"&method="+targetMethod+"&parameters="+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : "")+"&rand="+Math.random(), {
	method: 'get',
	onSuccess: function(transport) {
		if(onSuccessFunction) eval(onSuccessFunction);
	}});
}

/**
 * @deprecated
 **/
function rmeP(targetClass, targetClassId, targetMethod, targetMethodParameters, onSuccessFunction, bps){
	//alert("JS function rmeP() deprecated, use contentManager.rmePCR instead!");
 	if(typeof targetMethodParameters != "string"){
 		for(var i = 0; i < targetMethodParameters.length; i++)
 			targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
 
 	new Ajax.Request("./interface/rme.php?rand="+Math.random(), {
	method: 'post',
	parameters: "class="+targetClass+"&construct="+targetClassId+"&method="+targetMethod+"&parameters="+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""),
	onSuccess: function(transport) {
		if(onSuccessFunction) eval(onSuccessFunction);
	}});
 }
 
 
function windowWithRme(targetClass, targetClassId, targetMethod, targetMethodParameters, bps){
 	if(typeof targetMethodParameters != "string"){
 		for(var i=0;i<targetMethodParameters.length;i++)
 			targetMethodParameters[i] = "'"+encodeURIComponent(targetMethodParameters[i])+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
 	
	window.open(contentManager.getRoot()+'interface/rme.php?class='+targetClass+'&constructor='+targetClassId+'&method='+targetMethod+'&parameters='+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""),'Druckansicht','height=650,width=875,left=20,top=20,scrollbars=yes,resizable=yes');
}

function iframeWithRme(targetClass, targetClassId, targetMethod, targetMethodParameters, bps){
 	if(typeof targetMethodParameters != "string"){
 		for(var i=0;i<targetMethodParameters.length;i++)
 			targetMethodParameters[i] = "'"+targetMethodParameters[i]+"'";
 			
 		targetMethodParameters = targetMethodParameters.join(",");
 	}
 	else targetMethodParameters = "'"+targetMethodParameters+"'";
 	
	element = Builder.node('iframe',{src:'./interface/rme.php?class='+targetClass+'&constructor='+targetClassId+'&method='+targetMethod+'&parameters='+targetMethodParameters+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""), style:"display:none;"});
	$("contentLeft").appendChild(element);
}

/**
 * @deprecated
 **/
function loadFrameV2(target, plugin, bps, page){
	alert("JS function loadFrameV2() deprecated, use contentManager.loadFrame instead!");
	if(target == "contentRight"){
		lastLoadedRightPlugin = plugin;
		lastLoadedRightPage = page;
	}
	if(target == "contentLeft"){
		lastLoadedLeft = -2;
		lastLoadedLeftPlugin = plugin;
	}
	
	new Ajax.Request('./interface/loadFrame.php?p='+plugin+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : "")+((page != "" && typeof page != "undefined") ? "&page="+page : ""), {
	method: 'get',
	onSuccess: function(transport) {
		if(transport.responseText == "-1"){
			overlayBox.show();
			return;
		}
    	if(plugin != "" && checkResponse(transport)) $(target).update(transport.responseText);
	}});
}

function saveClass(className, id, onSuccessFunction, formName){
	formID = "AjaxForm";
	if(formName) formID = formName;
	
	if(!$(formID)) alert("Kein Formular gefunden!");
	if($(formID).elements.length == 0) alert("Keine Daten zum Speichern gefunden!");
	
	var dots = ".";
	if(document.location.pathname.search(/interface/) > -1) dots = "..";
	
	setString = dots+"/interface/set.php";

	new Ajax.Request(setString, {
	method: 'post',
	parameters: "class="+className+joinFormFields(formID)+"&id="+id+"&random="+Math.random(),
	onSuccess: function(transport) {
		if(checkResponse(transport)) {
			//showMessage(transport.responseText);
			onSuccessFunction(transport);
		}
	}});
}

function joinFormFields(formID){
	setString = "";
	for(i = 0;i < $(formID).elements.length;i++) {
		if($(formID).elements[i].type == "button") continue;
		//if($(formID).elements[i].type == "password" && $(formID).elements[i].value == "") continue;
		
		if($(formID).elements[i].type == "radio"){
			if($(formID).elements[i].checked) setString += "&"+$(formID).elements[i].name+"="+encodeURIComponent($(formID).elements[i].value);
		} else if($(formID).elements[i].type == "checkbox"){
			if($(formID).elements[i].checked) setString += "&"+$(formID).elements[i].name+"=1";
			else setString += "&"+$(formID).elements[i].name+"=0";
		} else if($(formID).elements[i].type == "select-multiple"){
			setString += "&"+$(formID).elements[i].name+"=";
			subString = "";
			for(j = 0; j < $(formID).elements[i].length; j++)
				if($(formID).elements[i].options[j].selected) subString += (subString != "" ? ";:;" : "")+$(formID).elements[i].options[j].value;
			
			setString += subString;
			
		} else setString += "&"+$(formID).elements[i].name+"="+encodeURIComponent($(formID).elements[i].value);
	}
	return setString;
}

function joinFormFieldsToString(formID){
	var get = joinFormFields(formID);
	
	get = get.replace(/&/g,";-;;und;;-;").replace(/=/g,";-;;istgleich;;-;").replace(/#/g,";-;;raute;;-;").replace(/\?/g,";-;;frage;;-;").replace(/%/g,";-;;prozent;;-;");
	
	return get;
}

/**
 * @deprecated
 **/
function reloadLeftFrame(bps){
	//alert("JS function reloadLeftFrame() deprecated, use contentManager.reloadFrame instead!");

	if(lastLoadedLeft == -1) {
		alert("Can't reload! lastLoadedLeft = -1");
		return;
	}
	new Ajax.Request('./interface/loadFrame.php?p='+lastLoadedLeftPlugin+'&id='+lastLoadedLeft+((bps != "" && typeof bps != "undefined") ? "&bps="+bps : ""), {onSuccess: function(transport){
		if(checkResponse(transport)) $('contentLeft').update(transport.responseText);
	}});
}

/**
 * @deprecated
 **/
function reloadRightFrame(bps){
	//alert("JS function reloadRightFrame() deprecated, use contentManager.reloadFrame instead!");

	if(lastLoadedRightPlugin == "") {
		alert("Can't reload! lastLoadedRightPlugin = ''");
		return;
	}
	contentManager.loadFrame('contentRight', lastLoadedRightPlugin, -1, lastLoadedRightPage, bps);
	//contentManager.reloadFrame("contentRight");
}

/**
 * @deprecated
 **/
function loadLeftFrameV2(plugin, withId, onSuccessFunction){
	//alert("JS function loadLeftFrameV2() deprecated, use contentManager.reloadFrame instead!");

	//lastLoadedLeft = withId;
	//lastLoadedLeftPlugin = plugin;

	contentManager.loadFrame("contentLeft", plugin, withId, 0, "bps", onSuccessFunction);

	/*new Ajax.Request('./interface/loadFrame.php?p='+plugin+'&id='+withId, {onSuccess: function(transport){
		if(checkResponse(transport)) $('contentLeft').update(transport.responseText);
		
		if(typeof onSuccessFunction != "undefined" && onSuccessFunction != "") onSuccessFunction();
	}});*/
}



function deleteClass(className, id, onSuccessFunction, question){
	Check = confirm(question);
	if (Check == false) return;

	contentManager.rmePCR(className, id, "deleteMe", "", onSuccessFunction);

	/*
	setString = "./interface/del.php?class="+className+"&id="+id+"&r="+Math.random();

	new Ajax.Request(setString, {
	method: 'get',
	onSuccess: function(transport) {
		if(checkResponse(transport)) {
			showMessage(transport.responseText);

			if(typeof onSuccessFunction != "undefined" && onSuccessFunction != "") onSuccessFunction();
		}
	}});*/
}
/*
function loadRightWithSelection(pluginToLoad,mode){
	alert("The function loadRightWithSelection is deprecated and should not be used!");
	
	new Ajax.Updater('contentRight','./interface/loadFrame.php?p='+pluginToLoad+'&mode='+mode);
}*/

function saveSelection(classe, classId, saveFunction, idToSave, targetFrame, targetClass, targetId){
	new Ajax.Request("./interface/rme.php", {
	method: 'post',
	parameters: "class="+classe+"&construct="+classId+"&method="+saveFunction+"&parameters='"+idToSave+"'",
	onSuccess: function(transport) {
		if(checkResponse(transport)){
			if(transport.responseText.search(/^message:/) == -1)showMessage(transport.responseText);

			if(targetId != -1) contentManager.loadFrame(targetFrame, targetClass, targetId);//new Ajax.Updater(targetFrame,'./interface/loadFrame.php?p='+targetClass+'&id='+targetId);
		}
	}});

}

function saveMultiEditInput(classe, eid, feld, onsuccessFunction){
	oldValue = $(feld+'ID'+eid).value;
	
	new Ajax.Request("./interface/rme.php?class="+classe+"&constructor="+eid+"&method=saveMultiEditField&parameters="+encodeURIComponent("'"+feld+"','"+$(feld+'ID'+eid).value+"'"), {
	method: 'get',
	onSuccess: function(transport) {
		if(checkResponse(transport)){
			if(transport.responseText.search(/^message:/) == -1) showMessage(transport.responseText);
			
			if(typeof onsuccessFunction != "undefined" && onsuccessFunction != "") onsuccessFunction();
		}
	}});
}