<?php
/**
 *  This file is part of plugins.

 *  plugins is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  plugins is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007, 2008, 2009, 2010, Rainer Furtmeier - Rainer@Furtmeier.de
 */

class Environment {
	public static $currentEnvironment;

	public function get($value, $default){
		$value = str_replace(":", "_", $value);
		if(isset($this->$value)) return $this->$value;

		return $default;
	}

	public static function getS($value, $default){
		Environment::load();

		return Environment::$currentEnvironment->get($value, $default);
	}

	public static function load(){
		if(Environment::$currentEnvironment != null) return;
		
		if(file_exists(Util::getRootPath()."plugins/Cloud/Cloud.class.php")){
			require_once Util::getRootPath()."plugins/Cloud/Cloud.class.php";
			require_once Util::getRootPath()."plugins/Cloud/mCloud.class.php";

			Environment::$currentEnvironment = mCloud::getEnvironment();
		} else
			Environment::$currentEnvironment = new Environment();
	}
}
?>
