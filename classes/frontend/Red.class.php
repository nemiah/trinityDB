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
class Red {

	public static function alertD($message){
		die("alert:'".addslashes($message)."'");
	}

	public static function errorD($message){
		die("error:'".addslashes($message)."'");
	}

	public static function errorC($class, $message){
		$ac = new anyC();
		$Lang = $ac->loadLanguageClass($class)->getText();

		die("error:'".addslashes($Lang[$message])."'");
	}

	public static function alertC($class, $message){
		$ac = new anyC();
		$Lang = $ac->loadLanguageClass($class)->getText();

		die("alert:'".addslashes($Lang[$message])."'");

	}

	public static function messageC($class, $message){
		$ac = new anyC();
		$Lang = $ac->loadLanguageClass($class)->getText();

		die("message:'".addslashes($Lang[$message])."'");
	}
}
?>
