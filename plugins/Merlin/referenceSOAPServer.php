<?php
/*
 *  This file is part of open3A.

 *  open3A is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.

 *  open3A is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.

 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  2007 - 2012, Rainer Furtmeier - Rainer@Furtmeier.de
 */
die("REMOVE THIS LINE TO ACTIVATE THIS FILE!");

session_name("ExtConnMerlin");
error_reporting(E_ALL);
/**
 * The folder that contains the dirs applications, classes, images, plugins, system...
 */
$absolutePathToPhynx = "/home/nemiah/NetBeansProjects/phynx/";

require_once $absolutePathToPhynx."classes/frontend/ExtConn.class.php";
require_once $absolutePathToPhynx."plugins/Merlin/ExtConnMerlin.class.php";

class ExtConnMerlinSOAPServer extends ExtConn {
	function __construct(){
		GLOBAL $absolutePathToPhynx;

		parent::__construct($absolutePathToPhynx);

		$this->useDefaultMySQLData();
		$this->useUser();

		$this->loadPluginInterface("plugins/Merlin", "ExtConnMerlin");
	}

	function __destruct() {
		$this->cleanUp();
	}
}

$S = new SoapServer(null, array('uri' => 'http://phynx/'));
$S->setClass('ExtConnMerlinSOAPServer');
$S->handle();
?>