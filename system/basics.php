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


function emoFatalError($excuse, $message, $title, $inSubdir = false, $die = true) {
	$errors = "";

	if(isset($_SESSION["phynx_errors"]) AND count($_SESSION["phynx_errors"]) > 0){
		$errors .= "<h2>Es sind PHP-Fehler aufgetreten:</h2><ol>";
		foreach($_SESSION["phynx_errors"] AS $error){
			$errors .= "<li>".$error[0].": ".$error[1]."</li>";
		}
		$errors .= "</ol>";
	}

		$text = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>'.$title.'</title>
		<style type="text/css">
			* {
				padding:0px;
				margin:0px;
			}

			body {
				font-size:0.8em;
				font-family:sans-serif;
				background-color:#d8d8d8;
				color:black;
			}

			p {
				padding:5px;
			}

			div {
				padding:10px;
			}

			li {
				font-size:0.8em;
			}

			ol {
				margin-left:10px;
			}

			h2 {
				margin-top:20px;
				clear:both;
			}

			pre {
				font-size:10px;
			}

			.backgroundColor0 {
				background-color:white;
			}
		</style>

	</head>
	<body>
		<div class="backgroundColor0">
			<img src="'.($inSubdir !== false ? $inSubdir."/" : "./").'system/basics.php?getEmoWarningSymbol=true" style="float:left;margin-right:15px;" />

			<h1>'.$excuse.'</h1>
			<p style="margin-left:80px;">'.$message.'</p>
			'.$errors.'
		</div>
		<!-- MORE SPACE -->
	</body>
</html>';

	if($die) die($text);

	return $text;
}

if(isset($_GET["getEmoWarningSymbol"])){
	header('Content-Type: image/png');
	echo stripslashes(base64_decode("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"));
}

?>