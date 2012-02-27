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

class GraphGUI implements iGUIHTML2  {
	protected $height = 300;
	protected $width = 398;
	protected $image;
	protected $backgroundColor;
	protected $textColor;
	protected $chartColor;
	protected $transparentColor;
	protected $XAxisColor;
	protected $YAxisColor;
	
	protected $zeroPoint = array(35, 270);
	
	protected $XValue;
	protected $YValue;
	
	protected $XValueParser;
	protected $YValueParser;
	
	protected $Collection;
	protected $graph;
	
	protected $maxHeight;
	protected $maxWidth;
	
	public function __construct(){
	}
	
	public function setTransparentColor($r, $g, $b){
		$this->transparentColor = array($r, $g, $b);
	}
	
	public function setTextColor($r, $g, $b){
		$this->textColor = array($r, $g, $b);
	}
	
	public function setBackgroundColor($r, $g, $b){
		$this->backgroundColor = array($r, $g, $b);
	}
	
	public function setXAxisColor($r, $g, $b){
		$this->XAxisColor = array($r, $g, $b);
	}
	
	public function setYAxisColor($r, $g, $b){
		$this->YAxisColor = array($r, $g, $b);
	}
	
	public function setCollection(Collection $c){
		$this->Collection = $c;
	}
	
	public function setXValue($XV){
		$this->XValue = $XV;
	}
	
	public function setYValue($YV){
		$this->YValue = $YV;
	}
	
	public function setZeroPoint($x, $y){
		$this->zeroPoint = array($x, $y);
	}
	
	public function setXValueParser($P){
		$this->XValueParser = $P;
	}
	
	public function setYValueParser($P){
		$this->YValueParser = $P;
	}
	
	protected function createImage() {
		$this->image = imagecreatetruecolor($this->width, $this->height);
		
		if($this->transparentColor != null) imagecolortransparent($this->image, imagecolorallocate($this->image, $this->transparentColor[0], $this->transparentColor[1], $this->transparentColor[2]));
		
		$this->maxHeight = $this->zeroPoint[1] - 8;
		$this->maxWidth = $this->width - 5 - $this->zeroPoint[0];
		
		if($this->backgroundColor == null) $this->backgroundColor = imagecolorallocate($this->image, 255, 255, 255);
		else $this->backgroundColor = imagecolorallocate($this->image, $this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
		
		if($this->textColor == null) $this->textColor = imagecolorallocate($this->image, 0, 0, 0);
		else $this->textColor = imagecolorallocate($this->image, $this->textColor[0], $this->textColor[1], $this->textColor[2]);
		
		if($this->chartColor == null) $this->chartColor = imagecolorallocate($this->image, 255, 0, 0);
		else $this->chartColor = imagecolorallocate($this->image, $this->chartColor[0], $this->chartColor[1], $this->chartColor[2]);
		
		if($this->YAxisColor == null) $this->YAxisColor = imagecolorallocate($this->image, 0, 0, 0);
		else $this->YAxisColor = imagecolorallocate($this->image, $this->YAxisColor[0], $this->YAxisColor[1], $this->YAxisColor[2]);
				
		if($this->XAxisColor == null) $this->XAxisColor = imagecolorallocate($this->image, 0, 0, 0);
		else $this->XAxisColor = imagecolorallocate($this->image, $this->XAxisColor[0], $this->XAxisColor[1], $this->XAxisColor[2]);
		
		imagefill($this->image, 0, 0, $this->backgroundColor);
		
		imageline($this->image, 5, $this->zeroPoint[1], $this->width - 5, $this->zeroPoint[1], $this->XAxisColor);
		imageline($this->image, $this->zeroPoint[0], 8, $this->zeroPoint[0], $this->zeroPoint[1], $this->YAxisColor);
		
		if($this->Collection == null)
			$this->showError("Please set a collection with setCollection()");
			
		$minX = null;
		$minY = null;
		$maxX = null;
		$maxY = null;
		
		$x = $this->XValue;
		$y = $this->YValue;

		$this->Collection->resetPointer();
		
		while(($t = $this->Collection->getNextEntry())){
			if($minX == null) $minX = $t->getA()->$x;
			if($minY == null) $minY = $t->getA()->$y;
			
			if($maxX == null) $maxX = $t->getA()->$x;
			if($maxY == null) $maxY = $t->getA()->$y;
			
			if($t->getA()->$x > $maxX) $maxX = $t->getA()->$x;
			if($t->getA()->$y > $maxY) $maxY = $t->getA()->$y;
			
			if($t->getA()->$x < $minX) $minX = $t->getA()->$x;
			if($t->getA()->$y < $minY) $minY = $t->getA()->$y;
		}
		$this->Collection->resetPointer();
	
		if($this->XValueParser != null){
			$s = explode("::",$this->XValueParser);
			$XMethod = new ReflectionMethod($s[0], $s[1]);
		}
		
		if($this->YValueParser != null){
			$s = explode("::",$this->YValueParser);
			$YMethod = new ReflectionMethod($s[0], $s[1]);
		}
		
		$t = $this->Collection->getNextEntry();
		$XFactor = $this->maxWidth / ($maxX - $minX);
		$YFactor = $this->maxHeight / ($maxY - $minY);
		$lastX = 0;
		$lastY = round($t->getA()->$y * $YFactor);
		
		imagestring($this->image, 1, 2, $this->zeroPoint[1] - 8, ($this->YValueParser ? $YMethod->invoke(null, $minY) : $minY), $this->textColor);
		imagestring($this->image, 1, 2, 0, ($this->YValueParser ? $YMethod->invoke(null, $maxY) : $maxY), $this->textColor);
		
		imagestring($this->image, 1, $this->zeroPoint[0] - strlen(($this->XValueParser ? $XMethod->invoke(null, $minX) : $minX)) * 5, $this->zeroPoint[1] + 2, ($this->XValueParser ? $XMethod->invoke(null, $minX) : $minX), $this->textColor);
		imagestring($this->image, 1, $this->width - 5 - strlen(($this->XValueParser ? $XMethod->invoke(null, $maxX) : $maxX)) * 5, $this->zeroPoint[1] + 2, ($this->XValueParser ? $XMethod->invoke(null, $maxX) : $maxX), $this->textColor);
		
		while(($t = $this->Collection->getNextEntry())){
			$newX = round(($t->getA()->$x - $minX) * $XFactor);
			$newY = round(($t->getA()->$y - $minY) * $YFactor);
			
			imagestring($this->image, 1, $this->zeroPoint[0] - strlen(($this->XValueParser ? $XMethod->invoke(null, $t->getA()->$x) : $t->getA()->$x)) * 5 + $newX, $this->zeroPoint[1] + 2, ($this->XValueParser ? $XMethod->invoke(null, $t->getA()->$x) : $t->getA()->$x), $this->textColor);
			
			
			imageline($this->image, $this->zeroPoint[0] + $lastX, $this->zeroPoint[1] - $lastY, $this->zeroPoint[0] + $newX, $this->zeroPoint[1] - $newY, $this->chartColor);
			$lastX = $newX;
			$lastY = $newY;
		}

	}
	
	private function outputAndDestroy(){
		imagepng($this->image);
		imagedestroy($this->image);
	}
	
	protected function showError($message){
			$img = imagecreatetruecolor(398, 300);
			imagestring($img, 2, 5, 5,  $message, imagecolorallocate($img, 255, 0, 0));
			
			imagepng($img);
			imagedestroy($img);
			exit;
	}
	
	function getHTML($id){
		header("Content-type: image/png");
		
		if(!isset($_SESSION["Graph"])) 
			$this->showError("No GraphGUI-object in \$_SESSION[Graph]");
		
		
		$this->graph = $_SESSION["Graph"];
		
		$this->graph->createImage();
		$this->graph->outputAndDestroy();

		unset($_SESSION["Graph"]);
		
		return;
		$this->outputAndDestroy();
	}
	
}
?>