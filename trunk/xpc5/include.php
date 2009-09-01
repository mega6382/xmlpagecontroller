<?php

    // Define system include path
    define( 'XPCCLASS_SYSTEM_PATH', dirname(__FILE__) . '/' );

    // Include classes
    require_once XPCCLASS_SYSTEM_PATH . 'XMLTag.class.php';
    require_once XPCCLASS_SYSTEM_PATH . 'XMLParser.class.php';
    require_once XPCCLASS_SYSTEM_PATH . 'XMLConfig.class.php';
    require_once XPCCLASS_SYSTEM_PATH . 'ZIPReader.class.php';
    require_once XPCCLASS_SYSTEM_PATH . 'XMLPage.class.php';
    require_once XPCCLASS_SYSTEM_PATH . 'XMLSite.class.php';

?>