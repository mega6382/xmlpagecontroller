<?php

/*
 * File: boot.php
 * Author: Andrew Saponenko <roguevoo@gmail.com>
 * Description: Zoombi PHP Framework bootstrap
 */

if( defined('ZBOOT') )
    return;

define('ZBOOT',         true);
define('ZDS',           DIRECTORY_SEPARATOR );
define('ZPS',           PATH_SEPARATOR );
define('ZOOMBI_BASE',   dirname(__FILE__) );

define('ZEXC_ERROR',    E_USER_ERROR);
define('ZEXC_WARNING',  E_USER_WARNING);
define('ZEXC_INFO',     E_USER_NOTICE);
define('ZEXC_DEBUG',    3);

include ZOOMBI_BASE.ZDS.'Base'.ZDS.'object.php';
include ZOOMBI_BASE.ZDS.'Tools'.ZDS.'singleton.php';
include ZOOMBI_BASE.ZDS.'Class'.ZDS.'loader.php';

class Zoombi extends ZSingleton
{
    private $m_booted;

    protected function  __construct()
    {
        $this->m_booted = false;
    }

    /**
     * Boot framework
     * @param  $a_config Configuration data
     * @return bool
     */
    static public function boot( $a_config )
    {
        $instance = Zoombi::instance();
        if( $instance->m_booted )
        {
            throw new Exception("Already booted", ZEXC_ERROR);
        }

        zinclude('zoombi.tools.config');

        $conf = ZConfig::instance();
        if( !$conf )
        {
            throw new Exception('configuration instance is invalid', ZEXC_ERROR);
            return false;
        }

        switch( gettype($a_config) )
        {
            case 'array':
                $conf->setData($a_config);
                break;

            case 'string':
                if( !$conf->load( $a_config ) )
                {
                    throw new Exception('configuration file "'.$a_config.'" is not found', ZEXC_ERROR);
                    return false;
                }
                break;
        }
       
        zinclude('zoombi.error.log');

        $log_path = zconfig('path.log');
        $log_path = realpath( $log_path );

        ZLog::instance()->setPath( $log_path );
        //ZLoader::instance()->connect('onError', ZLog::instance(), 'log' );
        //ZLoader::instance()->connect('onSuccess', ZLog::instance(), 'log' );

        zinclude('zoombi.system.security');
        zinclude('zoombi.tools.template');
        zinclude('zoombi.environment.querystring');
        zinclude('zoombi.environment.url');
        //zinclude('zoombi.tools.router');
        zinclude('zoombi.error.profiler');
        zinclude('zoombi.language.language');
        zinclude('zoombi.document.document');
        zinclude('zoombi.application.application');
        zinclude('zoombi.net.header');
        zinclude('zoombi.base.view');
        zinclude('zoombi.base.model');
        zinclude('zoombi.base.controller');

        return ZApplication::instance();
    }
}

?>
