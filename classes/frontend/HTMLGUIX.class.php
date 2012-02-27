<?php
/*
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
class HTMLGUIX {

	protected $object;
	#protected $frame;
	protected $attributes;
	protected $parsers = array();

	protected $showTrash = true;
	protected $showEdit = true;
	protected $showNew = true;
	protected $showQuicksearch = false;

	protected $colWidth = array();
	protected $colStyle = array();

	protected $className;

	protected $displayMode = null;
	protected $displayGroup = null;

	protected $caption;

	protected $GUIFactory;

	protected $multiPageDetails;

	protected $name;
	protected $features = array();

	protected $appended = array();
	protected $prepended = array();

	protected $languageClass;

	protected $sideButtons = array();

	protected $labels = array();
	protected $types = array();

	protected $functionEntrySave = "function(transport){ /*ADD*/ }";

	public function __construct($object = null, $collectionName = null){
		if($object != null)
			$this->object($object, $collectionName);

		$this->languageClass = $this->loadLanguageClass("HTML");
	}

	public function name($name){
		$this->name = $name;
	}

	public function addSideButton($label, $image = ""){
		$B = new Button($label, $image);

		$this->sideButtons[] = $B;

		return $B;
	}

	// <editor-fold defaultstate="collapsed" desc="displayGroup">
	function displayGroup($attributeName, $parser = null){
		$this->displayGroup = array($attributeName, $parser);
	}
	// </editor-fold>

	public function label($fieldName, $label){
		$this->labels[$fieldName] = $label;
	}

	public function type($fieldName, $type){
		$this->types[$fieldName] = $type;
	}

	public function descriptionField($fieldName, $description){
		$this->descriptionsField[$fieldName] = $description;
	}

	// <editor-fold defaultstate="collapsed" desc="displayMode">
	/**
	 * Supported display modes:
	 * BrowserRight
	 * BrowserLeft
	 * popup
	 * CRMSubframeContainer
	 *
	 * @param string $DM
	 */
	public function displayMode($DM){
		$this->displayMode = $DM;

		if($DM == "popup")
			$this->addToEvent("onSave", "contentManager.reloadFrame('contentLeft'); Popup.close('".$this->object->getClearClass()."', 'edit');");
	}
	// </editor-fold>
	

	// <editor-fold defaultstate="collapsed" desc="caption">
	public function caption($defaultCaption){
		$this->caption = $defaultCaption;
	}
	// </editor-fold>

	/**
	 * Use this method to set the Object you want to create a GUI for.
	 *
	 * @param Collection PersistentObject $object
	 */
	// <editor-fold defaultstate="collapsed" desc="object">
	public function object($object, $collectionName = null){
		if($object instanceof PersistentObject){
			$this->object = $object;
			#$this->frame("contentLeft");
			$this->className = str_replace("GUI", "", get_class($object));
			$this->caption($this->className);
		}

		if($object instanceof Collection){
			$this->object = $object;
			#$this->frame("contentRight");
			$this->className = $object->getCollectionOf();
			$this->multiPageDetails = $object->getMultiPageDetails();
		}
		$this->GUIFactory = new GUIFactory($this->className, $collectionName);
	}
	// </editor-fold>

	/**
	 * This is a cool but complex function which lets you define another function to
	 * evaluate the value of the attribute before displaying it.
	 *
	 * E.g. setParser("AttributeName","HTMLGUIX::attribParser");
	 *
	 * The first parameter $w of attribParser($w, $E); is the old value of the attribute.
	 * The second parameter is the object which is currently processed
	 *
	 * @param string $attributeName
	 * @param string $function
	 */
	// <editor-fold defaultstate="collapsed" desc="parser">
	function parser($attributeName, $function) {
		$this->parsers[$attributeName] = $function;
		#$this->parserParameters[$attributeName] = $parameters;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="colWidth">
	public function colWidth($attributeName, $width){
		#$this->colWidth[$attributeName] = $width;
		if(!isset($this->colStyle[$attributeName])) $this->colStyle[$attributeName] = "";
		$this->colStyle[$attributeName] .= "width:$width".(strpos($width, "px") === false ? "px" : "").";";
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="colStyle">
	public function colStyle($attributeName, $style){
		if(!isset($this->colStyle[$attributeName])) $this->colStyle[$attributeName] = "";
		$this->colStyle[$attributeName] .= $style;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="attributes">
	public function attributes(array $attributes){
		$this->attributes = $attributes;
	}
	// </editor-fold>


	public function addToEvent($event, $function){
		$this->GUIFactory->addToEvent($event, $function);

		switch($event){
			case "onSave":
				$this->functionEntrySave = str_replace("/*ADD*/", $function, $this->functionEntrySave);
			break;
		}
	}

	public function replaceEvent($event, $function){
		$this->GUIFactory->replaceEvent($event, $function);
	}

	public function insertAttribute($where, $fieldName, $insertedFieldName){
		if($where == "after")
			$add = 1;

		if($where == "before")
			$add = 0;
		
		$first = array_splice($this->attributes, 0, array_search($fieldName, $this->attributes) + $add);
		$last = array_splice($this->attributes, array_search($fieldName, $this->attributes));

		$this->attributes = array_merge($first, array($insertedFieldName), $last);
	}

	public function removeAttribute($fieldName){
		unset($this->attributes[array_search($fieldName, $this->attributes)]);
	}

	// <editor-fold defaultstate="collapsed" desc="frame">
	#public function frame($frame){
	#	$this->frame = $frame;
	#}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="options">
	public function options($showTrash = true, $showEdit = true, $showNew = true, $showQuicksearch = false){
		$this->showTrash = $showTrash;
		$this->showEdit = $showEdit;
		$this->showNew = $showNew;
		$this->showQuicksearch = $showQuicksearch;
	}
	// </editor-fold>

	function getEditHTML(){
		$this->object->loadMeOrEmpty();

		$F = new HTMLForm("edit".get_class($this->object), $this->object, $this->displayMode != "popup" ? $this->name : null);
		$F->getTable()->setColWidth(1, 120);
		$F->setSaveClass(get_class($this->object), $this->object->getID(), $this->functionEntrySave);

		foreach($this->labels AS $n => $l)
			$F->setLabel($n, $l);

		foreach($this->types AS $n => $l)
			$F->setType($n, $l);

		foreach($this->descriptionsField AS $n => $l)
			$F->setDescriptionField($n, $l);

		foreach($this->object->getA() AS $n => $v)
			$F->setValue($n, $v);



		return $F;
	}

	/**
	 * Call getBrowserHTML() if you want a table containing all the elements of a collection-class
	 * When called with an id, only one row is returned to easily replace an old one with JavaScript
	 *
	 * @param int $lineWithId
	 */
	// <editor-fold defaultstate="collapsed" desc="getBrowserHTML">
	function getBrowserHTML($lineWithId = -1){

		$bps = BPS::getAllProperties("m".$this->className."GUI");

		$GUIF = $this->GUIFactory;
		$GUIF->setMultiPageDetails($this->multiPageDetails);
		$GUIF->setTableMode($this->displayMode);
		$GUIF->options($this->showTrash, $this->showEdit, $this->showNew);
		
		if(isset($bps["selectionMode"]))
			$GUIF->selection($bps["selectionMode"]);

		#$GUIF->features($this->features);

		#$this->multiPageDetails["target"] = $this->frame;#"contentRight";
		#$GUIF->setMultiPageDetails($this->multiPageDetails);

		$E = $this->object->getNextEntry();


		if($this->attributes == null AND $E != null)
			$this->attributes = PMReflector::getAttributesArrayAnyObject($E->getA());

		if($E == null) //To fix display error when no entry
			$this->attributes = array("");

		if($this->caption == null)
			$this->caption("Bitte ".($this->name == null ? $this->className : $this->name)." auswählen:");


		$Tab = $GUIF->getTable($this->attributes, $this->colStyle, $this->caption);

		if($lineWithId == -1) {
			if($this->showQuicksearch) $GUIF->buildQuickSearchLine();
			
			if($this->multiPageDetails["total"] > $this->multiPageDetails["perPage"])
				$GUIF->buildFlipPageLine("top");

			$GUIF->buildNewEntryLine(($this->name == null ? $this->className : $this->name)." neu anlegen");

			if($this->object->isFiltered()) $GUIF->buildFilteredWarningLine();
		}

		$this->object->resetPointer();


		$DisplayGroup = null;
		while($E = $this->object->getNextEntry()){

			/**
			 * DisplayGroup
			 */
			if($lineWithId == -1 AND $this->displayGroup != null AND $DisplayGroup != $E->A($this->displayGroup[0])){
				if($this->displayGroup[1] != null){
					$DGP = explode("::", $this->displayGroup[1]);
					$GUIF->buildGroupLine(Util::invokeStaticMethod($DGP[0], $DGP[1], $E->A($this->displayGroup[0])));
				} else
					$GUIF->buildGroupLine($E->A($this->displayGroup[0]));
			}

			$Line = array();

			foreach($this->attributes AS $attributeName){
				$LineContent = $E->A($attributeName);

				if(isset($this->parsers[$attributeName]))
					$LineContent = $this->invokeParser($this->parsers[$attributeName], $LineContent, $E);
				else $LineContent = htmlspecialchars($LineContent);

				$Line[] = $LineContent;
			}


			$GUIF->buildLine($E->getID(), $Line);

			if($this->displayGroup != null)
				$DisplayGroup = $E->A($this->displayGroup[0]);
		}

		if($lineWithId == -1) {
			if($this->object->isFiltered()) $GUIF->buildFilteredWarningLine();

			if($this->multiPageDetails["total"] > $this->multiPageDetails["perPage"])
				$GUIF->buildFlipPageLine("bottom");
		}
		else
			return $Tab->getHTMLForUpdate();
		
		$ST = "";
		if(count($this->sideButtons) > 0){
			$ST = new HTMLSideTable("right");

			foreach($this->sideButtons AS $B)
				$ST->addRow($B);
		}

		return $ST.$GUIF->getContainer($Tab, $this->caption);
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="invokeParser">
	protected function invokeParser($function, $value, $element){
		$c = explode("::", $function);
		$method = new ReflectionMethod($c[0], $c[1]);
		return $method->invoke(null, $value, $element);
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="makeParameterStringFromArray">
	protected function makeParameterStringFromArray($array, $E){
		foreach($array AS $k => $v) {
			$v = str_replace("\$ID", $E->getID(), $v);
			if(strpos($v,"\$") !== false){
				$v = str_replace("\$", "", $v);
				$array[$k] = $E->A($v);
			} else
				$array[$k] = $v;
		}
		return implode("%§%",$array);
	}
	// </editor-fold>

	public function append($element){
		$this->appended[] = $element;
	}

	public function prepend($element){
		$this->prepended[] = $element;
	}

	public function customize($customizer){
		if($customizer == null) return;

		try {
			if($this->object == null) die("please use HTMLGUIX::object");
			$customizer->customizeGUI($this->object, $this);
		} catch (ClassNotFoundException $e){

		}
	}

	/**
	 * Finds, loads and returns the language class for the given class name
	 * 
	 * @param string $class
	 * @return unknown_type
	 */
	function loadLanguageClass($class){
		try {
			$n = $class."_".$_SESSION["S"]->getUserLanguage();
			$c = new $n();
		} catch(ClassNotFoundException $e){
			try {
				$n = $class."_de_DE";
				$c = new $n();
			} catch(ClassNotFoundException $e){
				return null;
			}
		}
		return $c;
	}

	/**
	 * You may use this default version check to see if the version of the plugin matches the application's version
	 *
	 * @param string $plugin
	 */
	public function version($plugin){
		$l = $this->languageClass->getBrowserTexts();

		if(Util::versionCheck($_SESSION["applications"]->getRunningVersion(), $_SESSION["CurrentAppPlugins"]->getVersionOfPlugin($plugin) , "!=")){

			$t = new HTMLTable(1);
			$t->addRow(str_replace(array("%1","%2"),array($_SESSION["CurrentAppPlugins"]->getVersionOfPlugin($plugin), $_SESSION["applications"]->getRunningVersion()),$l["versionError"]));
			die($t->getHTML());
		}
	}

	/**
	 *  This Method activates several features. Possible values for HTMLGUIX are:
	 *
	 *  reloadOnNew
	 *  CRMEditAbove
	 *  editInPopup
	 *  ---
	 *
	 * @param string $feature The feature to activate
	 * @param PersistentObject Collection $class
	 * @param $par1
	 * @param $par2
	 * @param $par3
	 */
	 // <editor-fold defaultstate="collapsed" desc="activateFeature">
	function activateFeature($feature, $class, $par1 = null, $par2 = null, $par3 = null){
		switch($feature){
			case "reloadOnNew":
				#if($class instanceof PersistentObject AND $class->getID() == -1)
					#$this->setJSEvent("onSave","function(transport){ contentManager.reloadOnNew(transport, '".$class->getClearClass()."'); }");
				if($class instanceof Collection)
					$this->GUIFactory->addToEvent("onNew", "contentManager.reloadFrame('contentRight');");
			break;
			case "CRMEditAbove":
				#$this->features["CRMEditAbove"] = "";
				$new = "contentManager.loadFrame('subFrameEdit%COLLECTIONNAME', '%CLASSNAME', %CLASSID, 0, '', function(transport) { if($('subFrameEdit%COLLECTIONNAME').style.display == 'none') new Effect.BlindDown('subFrameEdit%COLLECTIONNAME', {duration:0.5}); });";
				
				$this->GUIFactory->replaceEvent("onNew", $new);
				$this->GUIFactory->replaceEvent("onDelete", "deleteClass('%CLASSNAME','%CLASSID', function() { contentManager.reloadFrame('contentLeft'); },'Eintrag wirklich löschen?');");
				$this->GUIFactory->replaceEvent("onEdit", $new);
				#$this->functionDelete = ;
				#$this->functionNew = ;
				#$this->functionEdit = $this->functionNew;
			break;
			case "editInPopup":
				$new = "contentManager.editInPopup('%CLASSNAME', %CLASSID, 'Eintrag bearbeiten');";
				$this->GUIFactory->replaceEvent("onNew", $new);
				$this->GUIFactory->replaceEvent("onEdit", $new);
			break;
		}
	}
	 // </editor-fold>
}
?>