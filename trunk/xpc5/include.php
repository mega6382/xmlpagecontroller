<?php

    /** Define XPC system include path */
    define( 'XPC_CLASS_SYSTEM_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR );

    /** Include classes */
    require_once XPC_CLASS_SYSTEM_PATH . 'Template.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLTag.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLParser.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLConfig.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'ZIPReader.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLPage.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'XMLSite.class.php';
    
    require_once XPC_CLASS_SYSTEM_PATH . 'DOMBase.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'DOMMutator.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'DOMWalker.class.php';
    require_once XPC_CLASS_SYSTEM_PATH . 'DOMDataBase.class.php';
    
    require_once XPC_CLASS_SYSTEM_PATH . 'DOMSite.class.php';

?>