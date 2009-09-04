<?php

    /** Define XPC system include path */
    define( 'XPC_CLASS_SYSTEM_PATH', dirname(__FILE__) . '/' );

    /** Include classes */
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLTag.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLParser.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLConfig.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'ZIPReader.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLPage.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLSite.class.php';

?>