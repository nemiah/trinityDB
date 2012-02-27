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
class propertyC extends anyC {
	#public $viewOnly = false;
	
	protected $ownerClassName;
	protected $ownerClassID;
	protected $compactLayout = false;

	protected $targetClassName;
	protected $buttonLabel;
	protected $buttonIcon;
	protected $buttonOnclick;
	
	protected $showAttributes = null;
	
	protected $GUI;
	protected $name;

	protected $allowDelete = true;
	protected $allowEdit = false;
	protected $tableLabel;

	protected $singleEntry = false;

	public function __construct(){
		$this->GUI = new HTMLGUI();
	}
	
	public function setOwner($className, $classID){
		$this->ownerClassID = $classID;
		$this->ownerClassName = $className;
	}

	public function setName($name){
		$this->name = $name;
	}
	
	public function setTarget($className){
		$this->targetClassName = $className;
	}
	
	public function setValuesClass($valuesClass){
		$this->valuesClass = $valuesClass;
	}

	public function setButton($label, $icon = null, $onclick = null){
		$this->buttonLabel = $label;
		$this->buttonIcon = $icon;
		$this->buttonOnclick = $onclick;
	}

	public function setShownAttributes($sa){
		$this->showAttributes = $sa;
	}

	public function setOptions($showTrash = true, $showEdit = false){
		$this->allowDelete = $showTrash;
		$this->allowEdit = $showEdit;
	}
	
	/**
	 * @return HTMLGUI
	 */
	public function getGUI(){
		return $this->GUI;
	}

	/**
	 * @return anyC
	 */
	public function getC(){
		if($this->C == null) $this->loadC();
		return $this->C;
	}
	
	public function getHTML($id, $page){
		if(!$this->compactLayout){
			$this->addAssocV3($this->collectionOf."OwnerClass", "=", $this->ownerClassName);
			$this->addAssocV3($this->collectionOf."OwnerClassID", "=", $this->ownerClassID);

			$gui = $this->GUI;

			$this->lCV3($id);

			if($this->showAttributes != null)
				$gui->setShowAttributes($this->showAttributes);
		
			$gui->setName($this->name);
			$gui->setObject($this);

			$gui->setJSEvent("onDelete","function() { contentManager.reloadFrame('contentLeft'); }");

			
			if($this->buttonLabel instanceof Button){
				$B = $this->buttonLabel;
			} else {
				$B = new Button($this->buttonLabel, $this->buttonIcon);
				if($this->buttonOnclick == null) $B->select(false, "m".$this->targetClassName, $this->ownerClassName, $this->ownerClassID, "add".$this->targetClassName);
				else $B->onclick($this->buttonOnclick);
			}

			$tab = new HTMLTable(1);
			$tab->addRow($B);

			$gui->setIsDisplayMode(true);
			if($this->allowDelete) $gui->setDeleteInDisplayMode(true);
			if($this->allowEdit) {
				$gui->setEditInDisplayMode(true);
				$gui->setJSEvent("onEdit", "contentManager.editInPopup('$this->collectionOf','%%VALUE%%', '$this->name bearbeiten')");
			}
			try {
				return ((!$this->singleEntry OR $this->numLoaded() == 0) ? $tab : "").$gui->getBrowserHTML($id);
			} catch (Exception $e){ }

		} else {
			if($this->C == null) $this->loadC();
			$K = $this->C;
		
			
			return $this->getSelectorHTML($K);
		}
	}
	
	public function __toString(){
		try {
			if($this->ownerClassID == -1) {
				$t = new HTMLTable(1);
				$t->addRow("Sie müssen den Datensatz erst speichern, bevor Sie ".($this->tableLabel != null ? $this-> tableLabel : $this->collectionOf)." eintragen können.");
				return "<div style=\"margin-top:30px;\">".$t->getHTML()."</div>";
			}
			else return "<div style=\"margin-top:30px;\">".$this->getHTML(-1, 0)."</div>";
		} catch (Exception $e){
			print_r($e);
			die();
		}
	}
}
?>