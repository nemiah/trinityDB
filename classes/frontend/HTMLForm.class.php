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
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */
class HTMLForm {
	private $id;
	private $fields;
	private $types;
	private $labels;
	private $table;
	private $options;
	private $values;
	private $action;
	private $method;
	private $enctype;

	private $descriptionField = array();

	private $inputStyle = array();

	private $saveMode;
	private $saveButtonLabel;
	private $saveButtonBGIcon;
	private $saveButtonSubmit;
	private $saveButtonOnclick;
	private $saveButtonConfirm;
	private $saveButtonType = "button";
	private $saveClass;
	private $saveAction;

	private $onSubmit;
	private $onChange = array();

	private $spaces;
	private $spaceLines;

	private $endLV;

	private $translationClass;

	private $buttons;

	private $style;

	private $inputLineSyles = array();

	private $cols = 2;
	private $title;

	private $formTag = true;

	public function addJSEvent($fieldName, $event, $function){
		switch($event){
			case "onChange":
				if(!isset($this->onChange[$fieldName])) $this->onChange[$fieldName] = "";
				$this->onChange[$fieldName] .= $function;
			break;
		}
	}

	public function style($style){
		$this->style = $style;
	}

	public function cols($cols){
		if($cols != 2 AND $cols != 4 AND $cols != 1) return;

		$widths = Aspect::joinPoint("changeWidths", $this, __METHOD__);

		$this->table = new HTMLTable($cols, $this->title);
		$this->cols = $cols;

		if($cols == 4){
			if($widths == null) $widths = array(700, 132, 218);
			$this->table->setTableStyle("width:$widths[0]px;margin-left:10px;");

			$this->table->addColStyle(1, "width:$widths[1]px;");
			$this->table->addColStyle(2, "width:$widths[2]px;");
			$this->table->addColStyle(3, "width:$widths[1]px;");
			$this->table->addColStyle(4, "width:$widths[2]px;");
		}
	}

	public function hideIf($fieldName, $operator, $value, $event, $fieldsToHide){
		switch ($operator){
			case "=":
				if($this->values[$fieldName] == $value){
					foreach($fieldsToHide AS $v)
						$this->inputLineStyle($v, "display:none;");

				}
			break;
		}

		if($this->types[$fieldName] == "checkbox")
			$this->addJSEvent($fieldName, $event, "if(".($value == "1" ? "" : "!")."this.checked) contentManager.toggleFormFields('hide', ['".implode("','", $fieldsToHide)."'], '$this->id'); else contentManager.toggleFormFields('show', ['".implode("','", $fieldsToHide)."'], '$this->id');");
	}

	public function inputLineStyle($fieldName, $style){
		$this->inputLineSyles[$fieldName] = $style;
	}

	public function setInputStyle($fieldName, $style){
		$this->inputStyle[$fieldName] = $style;
	}

	public function __construct($formID, $fields, $title = null){
		$this->id = $formID;

		if(is_array($fields))
			$this->fields = $fields;
		if($fields instanceof PersistentObject){
			$fields->loadMeOrEmpty();
			$this->fields = PMReflector::getAttributesArrayAnyObject($fields->getA());
		}

		$this->types = array();
		$this->labels = array();
		$this->options = array();
		$this->values = array();
		$this->endLV = array();
		$this->spaces = array();
		$this->spaceLines = array();
		$this->table = new HTMLTable(2, $title);
		$this->title = $title;
		$this->saveMode = null;
		$this->onSubmit = null;
		$this->buttons = array();
	}

	public function hasFormTag($bool){
		$this->formTag = $bool;
	}

	public function addSaveDefaultButton($fieldName){
		$B = new Button("als Standard-Wert speichern", "./images/i2/save.gif");
		$B->rme("mUserdata","","setUserdata",array("'DefaultValue$this->id$fieldName'","$('$this->id').$fieldName.value"),"checkResponse(transport);");
		$B->type("icon");
		$B->style("float:right;");
		if(!isset($this->values[$fieldName])) {
			$U = new mUserdata();
			$this->values[$fieldName] = $U->getUDValue("DefaultValue$this->id$fieldName", "");
		}
		$this->buttons[$fieldName] = $B;
	}

	public function getAllFields(){
		return $this->fields;
	}

	public function translate(iTranslation $translationClass){
		$this->translationClass = $translationClass;
		if($translationClass == null) return;

		$labels = $this->translationClass->getLabels();
		#$labelDescriptions = $this->translationClass->getLabelDescriptions();
		#$fieldDescriptions = $this->translationClass->getFieldDescriptions();
		#$this->texts = $this->languageClass->getText();

		$this->table->setCaption($this->translationClass->getEditCaption());
		$this->saveButtonLabel = $this->translationClass->getSaveButtonLabel();

		if($labels != null)
			foreach($labels AS $k => $v)
				$this->setLabel($k, $v);

		/*if($labelDescriptions != null)
			foreach($labelDescriptions AS $k => $v)
				$gui->setLabelDescription($k, $v);

		if($fieldDescriptions != null)
			foreach($fieldDescriptions AS $k => $v)
				$gui->setFieldDescription($k, $v);*/
	}

	public function setSaveMultiCMS($saveButtonLabel, $saveButtonBGIcon, $class, $action = "", $onSuccessFuntion = ""){
		$this->saveMode = "multiCMS";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;
		$this->saveClass = $class;
		if($action != "")
			$this->saveAction = $action;
		else
			$this->saveAction = $this->id;
		$this->onSubmit = "multiCMS.formHandler('$this->id'".($onSuccessFuntion != "" ? ", $onSuccessFuntion" : "")."); return false;";
	}

	public function setSaveRMEP($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;
		$this->saveButtonSubmit = "rmeP('$targetClass', '$targetClassId', '$targetMethod', joinFormFieldsToString('$this->id'), '$onSuccessFunction');";
	}

	public function setSaveRMEPCR($saveButtonLabel, $saveButtonBGIcon, $targetClass, $targetClassId, $targetMethod, $onSuccessFunction){
		$this->saveMode = "rmeP";
		$this->saveButtonLabel = $saveButtonLabel;
		$this->saveButtonBGIcon = $saveButtonBGIcon;

		$values = "";
		foreach($this->fields AS $f){
			if($this->types[$f] != "checkbox")
				$values .= ($values != "" ? ", " : "")."\$('$this->id').$f.value";
			else
				$values .= ($values != "" ? ", " : "")."\$('$this->id').$f.checked ? '1' : '0'";
		}
		$this->saveButtonSubmit = "contentManager.rmePCR('$targetClass', '$targetClassId', '$targetMethod', [$values], $onSuccessFunction);";
		$this->onSubmit = $this->saveButtonSubmit."return false;";
	}

	public function setSaveConfirmation($question){
		$this->saveButtonConfirm = $question;
	}

	public function setType($fieldName, $type, $value = null, $options = null){
		$this->types[$fieldName] = $type;
		$this->options[$fieldName] = $options;
		if($value != null) $this->values[$fieldName] = $value;
	}

	public function setValue($fieldName, $value){
		$this->values[$fieldName] = $value;
	}

	public function setValues(PersistentObject $PO){
		$fields = $this->getAllFields();
		foreach($fields AS $k => $v)
			$this->setValue($v, $PO->A($v));
	}

	public function setLabel($fieldName, $label){
		$this->labels[$fieldName] = $label;
	}

	/*public function setSaveUpload($className, $label, $onSuccessFunction = 'function(){}'){
		$this->action = "./interface/set.php";
		$this->method = "post";
		$this->enctype = "multipart/form-data";

		$this->fields[] = "id";
		$this->fields[] = "class";
		$this->fields[] = "saveToAttribute";

		$this->setValue("class", "TempFile");
		
		$this->setType("class", "hidden");
		$this->setType("id", "hidden");
		$this->setType("saveToAttribute", "hidden");

		$this->saveMode = "class";
		$this->saveButtonLabel = $label;
		$this->saveButtonBGIcon = "./images/i2/save.gif";
		$this->saveClass = "";
		$this->saveAction = "";
		#$this->saveButtonSubmit = "";
		$this->saveButtonType = "submit";
		$this->onSubmit = "return AIM.submit($('$this->id'), {'onComplete' : $onSuccessFunction});";
	}*/

	public function setSaveBericht($berichtClass){
		$this->saveMode = "Bericht";
		$this->saveButtonLabel = "Einstellungen speichern";
		$this->saveButtonBGIcon = "";
		$this->saveClass = "";
		$this->saveAction = "";
		$this->saveButtonOnclick = "saveBericht('".get_class($berichtClass)."');";
	}

	public function setSaveClass($className, $classID, $onSuccessFunction = '', $label = ''){
		$this->saveMode = "class";
		$this->saveButtonLabel = "$label speichern";
		$this->saveButtonBGIcon = "./images/i2/save.gif";
		$this->saveClass = "";
		$this->saveAction = "";
		$this->saveButtonSubmit = "saveClass('$className', $classID, $onSuccessFunction, '$this->id')";
	}

	public function insertSpaceAbove($fieldName, $label = ""){
		$this->spaces[$fieldName] = $label;
	}

	public function insertLineAbove($fieldName, $label = ""){
		$this->spaceLines[$fieldName] = $label;
	}

	public function setDescriptionField($fieldName, $description){
		$this->descriptionField[$fieldName] = $description;
	}


	/**
	 * @return HTMLTable
	 */
	public function getTable(){
		return $this->table;
	}

	public function addTableEndLV($label, $value){
		$this->endLV[] = array($label, $value);
	}

	private function getInput($v){
		if(!isset($this->types[$v]) OR $this->types[$v] != "parser"){
			$Input = new HTMLInput(
				$v,
				isset($this->types[$v]) ? $this->types[$v] : "text",
				isset($this->values[$v]) ? $this->values[$v] : null,
				isset($this->options[$v]) ? $this->options[$v] : null);

			if(isset($this->onChange[$v]))
				$Input->onchange($this->onChange[$v]);

			if(isset($this->inputStyle[$v]))
				$Input->style($this->inputStyle[$v]);

		} else {
			$method = explode("::", $this->options[$v][0]);
			$Input = Util::invokeStaticMethod($method[0], $method[1], array($this->values[$v], "", $this->options[$v]));
		}

		return $Input;
	}

	private function getCustomButton($v, $Input){
		$B = "";
		if(!isset($this->types[$v]) OR $this->types[$v] != "parser"){
			if(isset($this->buttons[$v])) {
				$B = $this->buttons[$v];
				if(!isset($this->types[$v]) OR $this->types[$v] == "text" OR $this->types[$v] == "select")
					$Input->style("width:87%;");
			}
		}

		return $B;
	}

	public function  __toString() {
		$hiddenFields = "";

		if($this->cols == 2 OR $this->cols == 1)
			foreach($this->fields as $k => $v){
				if(isset($this->spaceLines[$v])){
					$this->table->addRow(array("<hr />"));
					#$this->table->addRowStyle("font-weight:bold;");
					#$this->table->addCellStyle(1, "padding-top:20px;");
					$this->table->addRowColspan(1, 2);
					$this->table->addRowClass("FormSeparatorWithoutLabel");

				}
				if(isset($this->spaces[$v]) AND $this->spaces[$v] != ""){
					$this->table->addRow(array($this->spaces[$v]));
					#$this->table->addRowStyle("font-weight:bold;");
					#$this->table->addCellStyle(1, "padding-top:20px;");
					$this->table->addRowColspan(1, 2);
					$this->table->addRowClass("FormSeparatorWithLabel");

				}
				if(isset($this->spaces[$v]) AND $this->spaces[$v] == ""){
					$this->table->addRow(array(""));
					$this->table->addRowClass("backgroundColor0");
					$this->table->addRowColspan(1, 2);
					$this->table->addRowClass("FormSeparatorWithoutLabel");
				}

				$Input = $this->getInput($v);
				$B = $this->getCustomButton($v, $Input);


				if(!isset($this->types[$v]) OR $this->types[$v] != "hidden"){

					if($this->cols == 2) $this->table->addLV(
						(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).":",
						$B.$Input.(isset($this->descriptionField[$v]) ? "<br /><small>".$this->descriptionField[$v]."</small>" : ""));

					if($this->cols == 1) $this->table->addRow(
						"<label>".(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).":</label>".
						$B.$Input.(isset($this->descriptionField[$v]) ? "<br /><small>".$this->descriptionField[$v]."</small>" : ""));

					if(isset($this->inputLineSyles[$v]))
						$this->table->addRowStyle($this->inputLineSyles[$v]);
				}
				else $hiddenFields .= $Input;
			}

		if($this->cols == 4){
			$row = array();
			foreach($this->fields as $k => $v){
				$Input = $this->getInput($v);

				if($this->types[$v] == "hidden") {
					$hiddenFields .= $Input;
					continue;
				}

				if(isset($this->spaces[$v])){
					if($this->spaces[$v] == ""){
						if(count($row) == 2) {
							$row[] = ""; $row[] = "";
							$this->table->addRow($row);
							$row = array();
						}
						#$this->table->addRow(array());
						#$this->table->addRowClass("backgroundColor0");
					}
				}

				$B = $this->getCustomButton($v);

				$row[] = "<label>".(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).":</label>";
				$row[] = $B.$Input;
				/*if(!isset($this->types[$v]) OR $this->types[$v] != "hidden"){
					$this->table->addLV(
						(isset($this->labels[$v]) ? $this->labels[$v] : ucfirst($v)).":",
						$B.$Input);

					if(isset($this->inputLineSyles[$v]))
						$this->table->addRowStyle($this->inputLineSyles[$v]);
				}
				else $hiddenFields .= $Input;*/


				if(count($row) == 4){
					$this->table->addRow($row);
					$row = array();
				}
			}

			if(count($row) == 2){
				$row[] = "";
				$row[] = "";
				$this->table->addRow($row);
			}
		}

		if($this->saveMode != null)
			switch($this->saveMode){
				case "class":
					$S = new HTMLInput("currentSaveButton",$this->saveButtonType,$this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");

					if($this->saveButtonSubmit != null)
						$S->onclick($this->saveButtonSubmit);

					$this->table->addRow(array($S));
					$this->table->addRowColspan(1, $this->cols);
				break;

				case "multiCMS":
					$S = new HTMLInput("submitForm","submit",$this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");

					$action = new HTMLInput("action", "hidden", $this->saveAction);
					$handler = new HTMLInput("HandlerName", "hidden", $this->saveClass);
					$return = new HTMLInput("returnP", "hidden", "/".$_GET["permalink"]);

					if($this->cols > 1) $this->table->addRow(array("",$S.$action.$handler.$return));
					else $this->table->addRow(array($S.$action.$handler.$return));
				break;

				case "rmeP":
					$S = new HTMLInput("submitForm","button",$this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");

					$S->onclick(($this->saveButtonConfirm != null ? "if(confirm('".$this->saveButtonConfirm."')) " : "").$this->saveButtonSubmit);

					$this->table->addRow(array($S));
					$this->table->addRowColspan(1, 2);
				break;

				case "Bericht":
					$S = new HTMLInput("submitForm","button",$this->saveButtonLabel);
					if($this->saveButtonBGIcon != "")
						$S->style("background-image:url($this->saveButtonBGIcon);background-position:98% 50%;background-repeat:no-repeat;");
					$S->onclick($this->saveButtonOnclick);

					$this->table->addRow(array($S));
					$this->table->addRowColspan(1, 2);
				break;
			}

		foreach($this->endLV AS $k => $v)
			$this->table->addLV($v[0],$v[1]);

		$html = "";

		if($this->formTag) $html .= "
	<form
		id=\"$this->id\"
		".($this->action != null ? "action=\"$this->action\"" : "")."
		".($this->method != null ? "method=\"$this->method\"" : "")."
		".($this->enctype != null ? "enctype=\"$this->enctype\"" : "")."
		".($this->onSubmit != null ? "onsubmit=\"$this->onSubmit\"" : "")."
		".($this->style != null ? "style=\"$this->style\"" : "").">";

		$html .= $this->table;


		$html .= $hiddenFields;

		if($this->formTag) $html .= "
	</form>";

		return $html;
	}

	public function getHTML(){
		return $this->__toString();
	}
}
?>
