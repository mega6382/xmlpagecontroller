<?php

/**
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/



    if( !defined('XPC_CLASSES') ) define('XPC_CLASSES',1);

    /** Define XPC system include path */
    define( 'XPC_CLASS_SYSTEM_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR );

    /** Include classes */
    require XPC_CLASS_SYSTEM_PATH . 'Template.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'XMLTag.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'XMLParser.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'XMLConfig.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'ZIPReader.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'XMLPage.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'XMLSite.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'DOMSimpler.php';
    require XPC_CLASS_SYSTEM_PATH . 'DOMBase.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'DOMMutator.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'DOMParser.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'DOMDataBase.class.php';
    require XPC_CLASS_SYSTEM_PATH . 'DOMSite.class.php';

?>