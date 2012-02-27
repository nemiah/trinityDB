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
require "../system/connect.php";
if($_SESSION["S"]->checkIfUserLoggedIn() == true) die("-1");
if(!$_SESSION["S"]->checkIfUserIsAllowed($_GET["class"])) die("You are not allowed to see this Page!");

$n = $_GET["class"]."GUI";
$C = new $n($_GET["id"]);
$C->deleteMe();

echo "Daten gelöscht";
?>