<?php

/*
    XML Page Controller
    Copyright (C) 2008 Saponenko Andrew<roguevoo@gmail.com>

    XML Page Controller is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    XML Page Controller is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with XML Page Controller.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
 * XML Page Controller Package bootstrap
 */

define('XPC_CLASS_PATH', dirname(__FILE__) );
define('DIRSEP', DIRECTORY_SEPARATOR);

require_once XPC_CLASS_PATH . DIRSEP . 'XMLTag.class.php';
require_once XPC_CLASS_PATH . DIRSEP . 'XMLParser.class.php';
require_once XPC_CLASS_PATH . DIRSEP . 'XMLConfig.class.php';
require_once XPC_CLASS_PATH . DIRSEP . 'ZIPReader.class.php';
require_once XPC_CLASS_PATH . DIRSEP . 'XMLPage.class.php';
require_once XPC_CLASS_PATH . DIRSEP . 'XMLSite.class.php';

?>