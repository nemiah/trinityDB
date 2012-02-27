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
class HTMLInput {
	private $type;
	private $name;
	private $value;
	private $options;
	private $onclick = null;
	private $onchange = null;
	#private $onblur = null;
	private $style = null;
	private $id = null;
	private $onkeyup = null;
	private $hasFocusEvent = false;
	private $isSelected = false;
	private $isDisabled = false;
	private $isDisplayMode = false;
	private $tabindex;
	private $multiEditOptions;
	private $autocomplete = true;
	private $onblur;
	private $onfocus;
	private $className;

	public function __construct($name, $type = "text", $value = null, $options = null){
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
		$this->options = $options;
	}

	public function setType($type){
		$this->type = $type;
	}

	public function setClass($className){
		$this->className = $className;
	}

	public function activateMultiEdit($targetClass, $targetClassID, $onSuccessFunction = null){
		$this->multiEditOptions = array($targetClass, $targetClassID, $onSuccessFunction);
	}

	public function setOptions($options, $labelField = null){
		if(is_object($options) AND $options instanceof Collection AND $labelField != null){
			$this->options = array("0" => "bitte auswählen");

			while($t = $options->getNextEntry())
				$this->options[$t->getID()] = $t->A($labelField);
		} else
			$this->options = $options;
	}

	public function autocomplete($bool){
		$this->autocomplete = $bool;
	}

	public function tabindex($index){
		$this->tabindex = $index;
	}

	public function isDisplayMode($b){
		$this->isDisplayMode = $b;
	}

	public function id($id){
		$this->id = $id;
	}

	public function style($style){
		$this->style = $style;
	}

	public function onclick($function){
		$this->onclick = $function;
	}

	public function onchange($function){
		$this->onchange = $function;
	}

	public function onblur($function){
		$this->onblur = $function;
	}

	public function onfocus($function){
		$this->onfocus = $function;
	}

	public function onkeyup($function){
		$this->onkeyup = $function;
	}



	public function onEnter($function){
		$this->onkeyup = "if(event.keyCode == 13) ".$function;
	}

	public function hasFocusEvent($bool){
		$this->hasFocusEvent = $bool;
	}

	public function isSelected($bool){
		$this->isSelected = $bool;
	}

	public function setValue($v){
		$this->value = $v;
	}

	public function isDisabled($bool){
		$this->isDisabled = $bool;
	}

	public function  __toString() {
		$style = "";
		if($this->type == "date") $this->style .= "width:80%;";
		if($this->style != null) $style = " style=\"$this->style\"";

		switch($this->type){
			case "multiInput":
				return "<input
					class=\"multiEditInput2\"
					type=\"text\"
					$style
					value=\"".htmlspecialchars($this->value)."\"
					onfocus=\"oldValue = this.value;\"
					id=\"".$this->options[2]."ID".$this->options[1]."\"
					onblur=\"if(oldValue != this.value) saveMultiEditInput('".$this->options[0]."','".$this->options[1]."','".$this->options[2]."');\"
					onkeydown=\"if(event.keyCode == 13) saveMultiEditInput('".$this->options[0]."','".$this->options[1]."','".$this->options[2]."');\"/>";
			break;

			/*case "customSelection":
				$B = new Button("Eintrag auswählen...", "gutschrift");
				$B->type("LPBig");
				$B->style("float:right;margin-left:10px;");
				#				 "contentRight"		"callingPluginID"  "selectPlugin"
				$B->customSelect($this->options[0], $this->options[1], $this->options[2], $this->options[3]);

				return $B."<input type=\"text\" name=\"$this->name\" value=\"$this->value\" />";
			break;*/

			case "textarea":
				if($this->isDisplayMode) return nl2br($this->value);

				if($this->multiEditOptions != null){
					$this->id($this->name."ID".$this->multiEditOptions[1]);
					$this->onfocus .= " oldValue = this.value;";
					$this->onkeyup .= "if(event.keyCode == 13) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
					$this->onblur .= "if(oldValue != this.value) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."'".($this->multiEditOptions[2] != null ? ", ".$this->multiEditOptions[2] : "").");";
				
					if($this->hasFocusEvent) {
						$this->onfocus .= "focusMe(this);";
						$this->onblur .= "blurMe(this);";
					}
					$this->hasFocusEvent = false;
				}

				return "<textarea
					$style
					name=\"$this->name\"
					".($this->className != null ? "class=\"$this->className\"" : "")."
					".($this->onkeyup != null ? "onkeyup=\"$this->onkeyup\"" : "")."
					".($this->onblur != null ? "onblur=\"$this->onblur\"" : "")."
					".($this->onfocus != null ? "onfocus=\"$this->onfocus\"" : "")."
					".($this->onkeyup != null ? "onkeyup=\"$this->onkeyup\"" : "")."
					".($this->hasFocusEvent ? "onfocus=\"focusMe(this);\" onblur=\"blurMe(this);\"" : "")."
					".($this->id != null ? "id=\"$this->id\"" : "").">$this->value</textarea>";
			break;

			case "file":
				$currentId = ($this->id != null ? $this->id : $this->name).rand(100, 100000000);
				return "
					<div id=\"progress_$currentId\" style=\"height:10px;width:95%;display:none;\" class=\"\">
						<div id=\"progressBar_$currentId\" style=\"height:10px;width:0%;\" class=\"backgroundColor1\"></div>
					</div>
					<div id=\"$currentId\" style=\"width:100%;\"></div>
					<script type=\"text/jacascript>
						var QQUploader = new qq.FileUploader({
							maxSizePossible: '".ini_get("upload_max_filesize")."B',
							element: $('$currentId'),
							action: './interface/set.php',
							params: {
								'class': '".(($this->options == null OR !isset($this->options["class"])) ? "TempFile" : $this->options["class"])."'
								,'id':'-1'
								".(($this->options != null AND isset($this->options["path"])) ? ",'path':'".$this->options["path"]."'" : "")."
							},
							onSubmit: function(id, fileName){ $('progress_$currentId').style.display = 'block';},
							onComplete: function(id, fileName, transport){ $('progress_$currentId').style.display = 'none'; if(checkResponse(transport)) { $this->onchange } },
							onProgress: function(id, fileName, loaded, total){ $('progressBar_$currentId').style.width = Math.ceil((loaded / total) * 100)+'%'; }});
					</script>";
			break;

			case "date":
			case "text":
			case "hidden":
			case "submit":
			case "button":
			case "password":
			case "checkbox":
			case "radio":
				if($this->isDisplayMode) {
					if($this->type == "checkbox") return Util::catchParser($this->value);
					return $this->value;
				}

				if($this->hasFocusEvent){
					$this->onfocus .= "focusMe(this);";
					$this->onblur .= "blurMe(this);";
				}

				$cal = "";
				if($this->type == "date") {
					if($this->id == null) $this->id = rand(10000,20000);

					$cal = new Button("","./images/i2/calendar.gif");
					$cal->onclick("Kalender.show(null,null,'$this->id',-365,0,'%d.%m.%y','');");
					$cal->type("icon");
					$cal->className("calendarIcon");
				}

				$value = "value=\"".htmlspecialchars($this->value)."\"";
				if($this->type == "checkbox") $value = $this->value == "1" ? "checked=\"checked\"" : "";

				if($this->multiEditOptions != null){
					$this->id($this->name."ID".$this->multiEditOptions[1]);
					$this->onfocus .= " oldValue = this.value;";
					$this->onkeyup .= "if(event.keyCode == 13) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."');";
					$this->onblur .= "if(oldValue != this.value) saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."');";
				}

				return "$cal<input
					$style
					".(!$this->autocomplete ? "autocomplete=\"off\"" : "")."
					".($this->className != null ? "class=\"$this->className\"" : "")."
					".($this->onclick != null ? "onclick=\"$this->onclick\"" : "")."
					".($this->onblur != null ? "onblur=\"$this->onblur\"" : "")."
					".($this->onfocus != null ? "onfocus=\"$this->onfocus\"" : "")."
					".($this->onkeyup != null ? "onkeyup=\"$this->onkeyup\"" : "")."
					".($this->tabindex != null ? "tabindex=\"$this->tabindex\"" : "")."
					".($this->isSelected ? "checked=\"checked\"" : "")."
					".($this->type == "file" ? "size=\"1\"" : "")."
					name=\"$this->name\"
					".($this->isDisabled ? "disabled=\"disabled\"" : "")."
					type=\"$this->type\"
					".($this->onchange != null ? "onchange=\"$this->onchange\"" : "")."
					".($this->id != null ? "id=\"$this->id\"" : "")."
					$value />";
			break;

			case "option":
				return "<option$style ".($this->isDisabled ? "disabled=\"disabled\"" : "")." ".($this->isSelected ? "selected=\"selected\"" : "")." value=\"$this->value\">$this->name</option>";
			break;

			case "select":
			case "select-multiple":
				if($this->type == "select-multiple")
					$values = explode(";:;", $this->value);

				if($this->isDisplayMode) return is_object($this->options[$this->value]) ? $this->options[$this->value]->__toString() : $this->options[$this->value];

				if($this->multiEditOptions != null){
					$this->onchange("saveMultiEditInput('".$this->multiEditOptions[0]."','".$this->multiEditOptions[1]."','".$this->name."');");
					$this->id($this->name."ID".$this->multiEditOptions[1]);
				}

				$html = "<select".($this->type == "select-multiple" ? " multiple=\"multiple\"" : "")."$style ".($this->onchange != null ? "onchange=\"$this->onchange\"" : "")." name=\"$this->name\" ".($this->id != null ? "id=\"$this->id\"" : "").">";

				if($this->options != null AND is_array($this->options))
					foreach($this->options AS $k => $v)
						if(!is_object($v)) {
							if($this->type == "select") $isThisIt = ($this->value == $k);
							else $isThisIt = in_array($k, $values);

							$html .= "<option ".($isThisIt ? "selected=\"selected\"" : "")." value=\"$k\">$v</option>";
						}
						else {
							if($this->value == $k)
								$v->isSelected(true);
							$html .= $v;
						}


				$html .= "</select>";

				return $html;
			break;
		}
	}
}
?>
