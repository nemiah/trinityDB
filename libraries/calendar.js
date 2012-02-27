//
// Kalender Popup 1.1
// Written by KjM <kjm@kjm.hu>
// Download und Dokumentation: http://www.goweb.de/javascriptkalender.htm
// 
//
// Danke an Volker Umpfenbach und Sebastian Ohme für Fehlermeldungen
// und Tipps zur Behebung.
//
// Neu in 1.1
// Berechnung der aktuellen Position auch wenn der User schon gescrollt hat
//
// Das Script kann frei auf jeder Seite verwendet werden
// Ich würde mich sehr über einen Backlink auf www.goweb.de freuen
//
// Erweitert von Rainer Furtmeier (Rainer@Furtmeier.de), 07.10.2007

// Shortcut
function gE(d) { return document.getElementById(d); }
var sliderControl;
var oldValue = -30;
var overflowRight = 0;
var overflowLeft = 0;

function startSlider(){
	sliderControl = new Control.Slider('jahreSlider','jahreTrack', {range:$R(-12,12),values:[-12,-11,-10,-9,-8,-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7,8,9,10,11,12], sliderValue: 0.000001});
	
    sliderControl.options.onSlide = function(value){
		if((oldValue == 0 && value == 0) || (oldValue == 20 && value == 20)) return;
		if(value == oldValue) return;
		d = new Date();
		Kalender.curMonat = d.getMonth() + 1 + value;
    	Kalender.curJahr = d.getFullYear();
		
		if(Kalender.curMonat <= 0) {
			Kalender.curMonat += 12;
			Kalender.curJahr--;
		}
		
		if(Kalender.curMonat >= 13) {
			Kalender.curMonat -= 12;
			Kalender.curJahr++;
		}
		
		Kalender.anzeige();
    };
}

//
// Kalender Objekt initialisieren
var Kalender = {

  //
  // Ein Tag hat wieviel Millisekunden?
  //
  oneDay : 86400000,
  destObj: null,
  layout : "%d.%m.%y",
  lastMouseX: 0,
  lastMouseY: 0,
  isInit: false,
  // Microsoft product
  ismsie: false,
  onchange: "",

  //
  // Monatsnamen in deutsch
  //
  monate : new Array("Januar","Februar","März","April","Mai","Juni","Juli","August","September","Oktober","November","Dezember"),
  mshort : new Array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez"),

  //
  // Tagesnamen
  //
  weekdays : new Array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag"),
  weekdaysShort : new Array("So","Mo","Di","Mi","Do","Fr","Sa"),

  //
  // Tage pro Monat
  //
  daysinmonth : new Array(31,28,31,30,31,30,31,31,30,31,30,31),

  // 
  // Event programmieren
  //
  /*
  eH: function(obj, evType, fn, useCapture) {
    if (obj.addEventListener) {
      obj.addEventListener(evType, fn, useCapture);
      return true;
    } else if (obj.attachEvent) {
      var r = obj.attachEvent('on'+evType,fn);
      return r;
    } else {
      obj['on'+evType] = fn;
    }
  }, */ 
  resetDate: function() {
    var d = new Date();
    Kalender.curMonat = d.getMonth()+1; 
    Kalender.curJahr = d.getFullYear();
    if(typeof Control == "object") {	
    	overflowLeft = 0;
    	overflowRight = 0;
    	sliderControl.setValue(0.000001);
    }
    Kalender.anzeige();
  },
  //
  // Style auf ein Objekt anwenden
  //
  style: function(o, t, v) { eval(o+".style."+t+"='"+v+"';"); },

  //
  // Kalender initialisieren
  //
  init: function(afterObject) {

    // Kein DOM Support :(
    if (!document.getElementById) return;

    // MSIE?
    if (!window.opera && navigator.userAgent.indexOf("MSIE") !=-1)
      Kalender.ismsie = true;

    // Kalender Objekt in die Seite einfügen
    var b = document.getElementsByTagName("body");
    
    // Fehler auf der Seite - mehr als 1 Body Tag
    if (b.length != 1) return;

    // dateContainerelemente (DIV) erstellen
    var dateContainer = document.createElement("div"); 
    dateContainer.id = "dateContainer";
    dateContainer.className = "backgroundColor0 borderColor1";
    dateContainer.style.display = "none";
    
	var track = document.createElement("div");
	track.id = "jahreTrack";
	track.className = "backgroundColor3";
	var slider = document.createElement("div");
	slider.id = "jahreSlider";
	slider.className = "backgroundColor2";
	track.appendChild(slider);
	
    // Kalender erstellen
    var cal = document.createElement("div"); 

    // Header erstellen (DIV)
    var monat = document.createElement("div"); 
    monat.id = "dateMonat";
    monat.className = "backgroundColor1";
    var cls   = document.createElement("div"); 
    
    var tage = document.createElement("div"); 
    tage.id = "tage";

	var tageTable = document.createElement("table"); 
    tageTable.id = "tageTable";

    // Fenster zusammenfügen und oberstes zurückgeben
    dateContainer.appendChild(cal);

    //td1.appendChild(monat); 
    tage.appendChild(tageTable);
    cal.appendChild(monat);
    if(typeof Control == "object") cal.appendChild(track);
    cal.appendChild(tage);

    // Kalenderobjekt in die Seite einfügen
    b[0].appendChild(dateContainer);
    
	
    // Aktueller Monat und aktuelles Jahr
    var d = new Date();
    Kalender.curMonat = d.getMonth()+1; 
    Kalender.curJahr = d.getFullYear();
	
	
    // Datumsgrenzen
    Kalender.selectionStart = Kalender.selectionEnd = 0;
	new Draggable($('dateContainer'), {handler: $('dateMonat')});
    // Mausebewegungen abfangen
    //Kalender.eH(gE("dateMonat"),'mousedown',Kalender.verschieben,false);

    //Kalender.eH(document,'mousemove',Kalender.move,false);
    
    Kalender.isInit = true;
    
  },
  
  show: function(monat, jahr, obj, pdays, tdays, layout, onchange) {

	if(typeof onchange != "undefined") Kalender.onchange = onchange;
	
  	if(!Kalender.isInit) Kalender.init();
    var d = new Date();
    Kalender.curMonat = d.getMonth()+1; 
    Kalender.curJahr = d.getFullYear();
    
  	Kalender.anzeige(monat, jahr, obj, pdays, tdays, layout);
  	
	if(typeof Effect == "object") new Effect.Appear(gE("dateContainer"),{duration: 0.4});
    else gE("dateContainer").style.display = "block";
    
  	if(typeof Control == "object") setTimeout("startSlider()",100);
  },
  
  //
  // Kalender nicht länger anzeigen
  //
  close: function() {
  	if(typeof Effect == "object") new Effect.Fade(gE("dateContainer"),{duration: 0.4});
    else gE("dateContainer").style.display = "none";
  },

  //
  // Nächsten Monat anzeigen
  //
  nextMon: function() {
    if (Kalender.curMonat == 12) {
      Kalender.curMonat = 1; Kalender.curJahr++;
    } else Kalender.curMonat++;
    if(typeof Control == "object") {
    
    	if(sliderControl.value < 12) {
    		if(overflowLeft == 0) sliderControl.setValue(sliderControl.value + 1);
    		else overflowLeft--;
    	}
    	else overflowRight++;
    }
    Kalender.anzeige();
  },

  //
  // Vorheriger Monat anzeigen
  //
  prevMon: function() {
    if (Kalender.curMonat == 1) {
      Kalender.curMonat = 12;
      Kalender.curJahr--;
    } else Kalender.curMonat--;
    
    if(typeof Control == "object") {
    	
    	if(sliderControl.value > -12) {
    		if(overflowRight == 0) sliderControl.setValue(sliderControl.value - 1);
    		else overflowRight--;
    	}
    	else overflowLeft++;
    }
    
    Kalender.anzeige();
  },

  //
  // Datum in das entsprechende Objekt einfügen
  //
  setzen: function(ts) {
    var d = new Date(ts);
    if (Kalender.destObj) {
      var m = d.getMonth()+1; var y = d.getDate();
      if (m<10) m = "0"+m; if (y<10) y = "0"+y;
      var z = gE(Kalender.destObj);

      // Layoutstring erzeugen
      var l = Kalender.layout;
      l = l.replace(/%d/g,y);
      l = l.replace(/%m/g,m);
      l = l.replace(/%b/g,Kalender.mshort[d.getMonth()]);
      l = l.replace(/%B/g,Kalender.monate[d.getMonth()]);
      l = l.replace(/%y/g,d.getFullYear());
      l = l.replace(/%a/g,Kalender.weekdays[d.getDay()]);

      z.value = l;
    }

	if(Kalender.onchange != "") eval(Kalender.onchange);

    // Kalender schliessen
    Kalender.close();
  },

  //
  // Kalender für einen bestimmten Monat anzeigen
  // Wenn monat / jahr nicht angegeben wird, wird das jeweils aktuelle genommen
  // obj ist das Objekt in welches später das gewählte Datum geschrieben wird
  // pdays versteht sich als Startoffset für gültige Tage ab dem aktuellen
  // tdays ist der Endoffset für gültige Tage ab dem aktuellen
  //
  anzeige: function(monat, jahr, obj, pdays, tdays, layout) {
  
	var h = new Date();
	child = gE("tageTable").firstChild;
	
	while(child){
		gE("tageTable").removeChild(child);
		child = gE("tageTable").firstChild;
	}
	
	dayNames = Kalender.weekdaysShort;
	
	cg = document.createElement("colgroup");
	for(i=0;i<dayNames.length;i++){
		col = document.createElement("col");
		col.className = "s"+(i%2 == 0 ? "1" : "2");
		cg.appendChild(col);
	}
	
	gE("tageTable").appendChild(cg);
	
	ntb = document.createElement("tbody");
	ntb.id = "tageBody";
	ntr = document.createElement("tr");
	
	for(i=0;i<dayNames.length;i++){
		nth = document.createElement("th");
		nth.className = "borderColor1";
		nth.appendChild(document.createTextNode(dayNames[i]));
		
		ntr.appendChild(nth);
	}
	ntb.appendChild(ntr);
	ntr = document.createElement("tr");

    // Monat & Jahr sind angegeben und Monat ist zwischen 1 und 12?
    if ((monat == null) || (jahr == null)) {
	    monat = Kalender.curMonat; 
	    jahr = Kalender.curJahr;
    }

    // Datumslayout zuweisen
    if (layout) Kalender.layout = layout;

    // Zielobjekt setzen
    if (obj) {
	    Kalender.destObj = obj;
	
	    // dateContainer genau auf die Mausposition setzen
	    var c = gE("dateContainer");

	    c.style.left = (Observer.lastMouseX - (Observer.lastMouseX + 200 > overlayBox.getPageSize()[0] ? 200 : 0)) + "px";
	    
	    c.style.top = Observer.lastMouseY + "px";
    }

    // Monat ist gueltig?
    if ((isNaN(parseInt(monat))) || ((monat < 1) || (monat > 12))) return;

    // Monat & Jahr setzen
    Kalender.curJahr = jahr; 
    Kalender.curMonat = monat;

    // Monat und Jahr inkl. Links einblenden
    gE("dateMonat").innerHTML = "<a href=\"javascript:Kalender.close();\" class=\"closeCalendar backgroundColor0 borderColor0\" />X</a><a href='JavaScript:Kalender.prevMon()'>&lt;</a>&nbsp;<a href='JavaScript:Kalender.resetDate()'>&#8595;</a>&nbsp;<a href='JavaScript:Kalender.nextMon()'>&gt;</a>&nbsp;&nbsp;"+Kalender.monate[monat-1]+" "+jahr+"";

    // Zeitgrenzen setzen
    if (pdays != null) {
	    var h = new Date();
	    var n = new Date(h.getFullYear(),h.getMonth(),h.getDate(),0,0,1);
	    Kalender.selectionStart = n.getTime() + (Kalender.oneDay * pdays);
	    Kalender.selectionEnd = ((tdays == null)||(tdays == 0)) ? 0 : tdays;
    }

    // Datumsobjekt initialisieren
    var d = new Date(jahr,monat-1,1,6,0,1); 
    var n = d.getTime(); 
    var f = n; 
    var t = (Kalender.selectionEnd != 0)?Kalender.selectionStart+Kalender.oneDay*Kalender.selectionEnd:0;

    // Tage in den Kalender einfügen
    var j = 1; 
    var l = 0;
	for (var i = 1; i <= d.getDay(); i++) {
		ntd = document.createElement("td");
		ntr.appendChild(ntd);
			
    	j++;
    }
    
    var dim = Kalender.daysinmonth[monat-1];

    // Schaltjahr?
    
    if (dim == 28) {
		if (jahr % 4   == 0) dim++;
		if (jahr % 100 == 0) dim--;
		if (jahr % 400 == 0) dim++;
    }

    for (i = 1; i <= dim; i++) {

	    // Datum gültig ab?
		if ((f) && (f >= Kalender.selectionStart)) {
			f = 0; 
			l = 1;
		}

		// Datum gültig bis?
		if ((t>0) && (n >= t)) {
			t = -1; 
			l = 0; 
		}

		ntd = document.createElement("td");
		text = document.createTextNode(i)
		na = document.createElement("a");
		na.href = "javascript:Kalender.setzen("+n+");";
		if(l) na.appendChild(text);
		ntd.appendChild(l ? na : text);
		
		if(h.getDate() == i && h.getFullYear() == d.getFullYear() && h.getMonth() == d.getMonth())
			ntd.className = 'backgroundColor2';
		
		
		ntr.appendChild(ntd);
		
		j++;
		n+=Kalender.oneDay; 
		f += (f)?Kalender.oneDay:0;
		    
		if (j == 8) {
			ntb.appendChild(ntr);
		    ntr = document.createElement("tr");
		      
		    j = 1; 
		}
    }
    
    if (j!=1) 
    	for (i = j; i < 8; i++) {
			ntd = document.createElement("td");
			ntr.appendChild(ntd);
    }
    

    // Kalender anzeigen
    ntb.appendChild(ntr);
    
	gE("tageTable").appendChild(ntb);
  },


  //
  // Mausbutton wurde losgelassen
  //
  stop: function(e) {
    
    // Eventhandler loeschen
    gE("dateMonat").style.cursor = "auto";
    Kalender.obj = null;
  },

  //
  // DIV Position übergeben
  //
  chkEvH: function(e) {
    if (typeof e == 'undefined') e = window.event;
    if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
    if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
    return e;
  }
};
