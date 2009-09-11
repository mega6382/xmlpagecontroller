<?php

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

defined( 'XPC_CLASSES' ) or die('defined');

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
