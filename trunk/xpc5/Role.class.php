<?php

/**
 * Description of Role
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class Role
{
    public $id;
    public $access;

    static public $sm_reg_name = "session_register";

    public function __construct()
    {

    }

    public function role()
    {

    }

    static public function register()
    {
        $_SESSION['auth'] = true;
    }

    static public function unregiter()
    {
        if( key_exists( self::$sm_reg_name, $_SESSION) )
            unset( $_SESSION[self::$sm_reg_name] );
    }

    static public function isLogged()
    {
        if( !$_SESSION || !is_array($_SESSION) )
            return false;

        return isset( $_SESSION[self::$sm_reg_name] );
    }
}
?>
