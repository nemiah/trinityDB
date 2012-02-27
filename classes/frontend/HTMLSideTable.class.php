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
class HTMLSideTable extends HTMLTable  {
	function  __construct($where) {
		parent::__construct(1);

		switch($where){
			case "left":
				$this->setTableStyle("width:160px;margin:0px;margin-left:-170px;float:left;");
			break;
		
			case "right":
				$this->setTableStyle("width:160px;margin:0px;margin-right:-170px;float:right;");
			break;
		}
	}

	/**
	 * Creates a new Button and adds it to the table.
	 * Then the Button will be returned to add some more functionality
	 *
	 * @param string $label
	 * @param string $image
	 * @return Button
	 */
	function addButton($label, $image = ""){
		$B = new Button($label, $image);

		$this->addRow($B);

		return $B;
	}

	function addRow($content){
		parent::addRow($content);
		$this->addRowClass("backgroundColor0");
	}
}
?>
