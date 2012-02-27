<?php
/**
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
class FormattedTextPDF extends FPDF {
	private $B = 0;
	private $I = 0;
	private $U = 0;
	private $HREF = "";
	private $FONT = "";
	private $H1 = 0;
	private $H2 = 0;
	private $H3 = 0;
	private $H4 = 0;
	private $H5 = 0;
	private $H6 = 0;
	
	protected $FontSizeH = array("H1" => 15, "H2" => 13, "H3" => 12, "H4" => 10, "H5" => 10, "H6" => 10);
	protected $FontDecoH = array("H1" => "B", "H2" => "B", "H3" => "B", "H4" => "B", "H5" => "B", "H6" => "B");
	
	protected $FontSizeDefault = 9;
	
	/*function printTextbaustein(Textbaustein $T){
		$T->loadMe();
		$TBText = $T->getA()->text;
		
		$this->SetFont($this->fontTextbausteine[0], $this->fontTextbausteine[1], $this->fontTextbausteine[2]);
		
		$this->WriteHTML(utf8_decode(Util::conv_euro($TBText)));
		$this->ln(10);
	}*/
	
	protected function getFontSize(){
		return $this->FontSize*$this->k;
	}
	
	private function replaceTag(DOMDocument $doc, $tagOld, $styleRequired, $tagNew){
		$nodeList = $doc->getElementsByTagName($tagOld);
		#echo $nodeList->length;
		for($i = $nodeList->length - 1; $i > -1; $i--) {
			$node = $nodeList->item($i);
			
			if($styleRequired != "" AND strpos($node->getAttribute("style"), $styleRequired) === false)
				return;

			$newNode = $doc->createElement($tagNew, "");
			if($styleRequired != "") $newNode->setAttribute("style", str_replace($styleRequired, "", $node->getAttribute("style")));
			
			if($node->hasChildNodes()){
				foreach($node->childNodes AS $child)
					$newNode->appendChild($child);
			}
			 
			 $node->parentNode->replaceChild($newNode, $node);
		}
	}
	
	public function WriteHTML($html) {
	    //HTML parser
	    #$html = str_replace("<p>","<br /><br />", $html);
	    
		if(trim($html) == "") return;
		
		$doc = new DOMDocument();

		$doc->loadHTML($html);
		
		$this->replaceTag($doc, "strong", "","b");
		$this->replaceTag($doc, "span", "text-decoration: underline;","u");
		$this->replaceTag($doc, "em", "","i");
		
		$html = $doc->saveHTML();
	    $html = html_entity_decode($html);
		
		#$html = str_replace("</p>","", $html);
	    $html = str_replace("\n",'',$html);
	    $a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
	    
	    
	    foreach($a as $i => $e){
	        if($i % 2 == 0) {
	            //Text
	            if($this->HREF)
	                $this->PutLink($this->HREF,$e);
	            elseif($this->FONT){
	            	$preSize = $this->getFontSize();
	            	$this->SetFontSize($this->FONT);
	            	$this->Write(5, $e);
	            	$this->SetFontSize($preSize);
	            }
	            else
	                $this->Write(5, $e);
	        } else {
	            //Tag
	            if($e[0]=='/')
	                $this->CloseTag(strtoupper(substr($e,1)));
	            else {
	                //Extract attributes
	                $a2 = explode(' ',$e);
	                $tag = strtoupper(array_shift($a2));
	                $attr = array();
	                foreach($a2 as $v) {
	                    if(preg_match('/([^=]*)=["\']?([^"\']*)/', $v, $a3))
	                        $attr[strtoupper($a3[1])] = $a3[2];

	                }
	                $this->OpenTag($tag,$attr);
	            }
	        }
	    }
	}
	
	protected function OpenTag($tag,$attr)	{
		
	    if($tag == 'B' || $tag == 'I' || $tag == 'U')
	        $this->SetStyle($tag,true);
		
	    if($tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6'){
	    	$this->ln(0);
	    	$this->SetStyle($tag,true);
	    }
	    if($tag == 'A')
	        $this->HREF = $attr['HREF'];
	        
	    if($tag == 'FONT')
	        $this->FONT = $attr['SIZE'];
	        
	    if($tag == 'BR')
	        $this->Ln(5);
	}
	
	protected function CloseTag($tag)	{

	    if($tag == 'B' || $tag == 'I' || $tag == 'U')
	        $this->SetStyle($tag,false);
	        
	    if($tag == 'H1' || $tag == 'H2' || $tag == 'H3' || $tag == 'H4' || $tag == 'H5' || $tag == 'H6'){
	    	$this->SetStyle($tag,false);
	    	$this->ln(10);
	    }
	        
	    if($tag == 'A')
	        $this->HREF='';
	        
	    if($tag == 'FONT')
	        $this->FONT='';
	        
	    if($tag == 'P')
	    	$this->ln(10);
	}
	
	protected function SetStyle($tag,$enable)	{
	    //Modify style and select corresponding font
	    $this->$tag+=($enable ? 1 : -1);
	    $style='';
	    $size = $this->FontSizeDefault;
	    
	    foreach(array('B','I','U') as $s)
	    {
	        if($this->$s>0 AND strpos($style, $s) === false)
	            $style.=$s;
	    }
	    
	    foreach(array('H1','H2','H3','H4','H5','H6') as $s)
	    {
	    	if($this->$s>0 AND strpos($style, $this->FontDecoH[$s]) === false){
	    		$size = $this->FontSizeH[$s];
	            $style .= $this->FontDecoH[$s];
	    	}
	    }
	    $this->SetFont('', $style, $size);
	}
	
	protected function PutLink($URL,$txt)	{
	    //Put a hyperlink
	    $this->SetTextColor(0,0,255);
	    $this->SetStyle('U',true);
	    $this->Write(5,$txt,$URL);
	    $this->SetStyle('U',false);
	    $this->SetTextColor(0);
	}
}
?>