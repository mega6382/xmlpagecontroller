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
abstract class Role
{
    protected $sm_uin   = "session_register_uin";
    protected $sm_group = "session_register_group";

    public function __construct(){}

    public function role()
    {
        return array( $this->group(), $this->uin() );
    }
    public function group()
    {
        return isset( $_SESSION[ $this->sm_group] )    ? $_SESSION[ $this->sm_group ] : null;
    }

    public function uin()
    {
        return isset( $_SESSION[ $this->sm_uin ] )      ? $_SESSION[ $this->sm_uin ] : null;
    }

    abstract public function login();
}

class RoleProvider
{
    private $m_role;

    public function __construct()
    {
        $this->m_role = null;
    }

    public function registerRole( Role & $a_role )
    {
        $this->m_role = $a_role;
        return true;
    }

    public function isLogged()
    {
        if( $this->m_role == null )
            return;
            
        if( $this->m_role->group() ==  null )
            return;

        if( $this->m_role->uin() == null )
            return;

        return true;
    }

    public function doLogin()
    {
        $this->m_role->login();
    }

    private function _roleCompare( $a_role )
    {

    }

    public function hasAccess( $a_to )
    {
        if( preg_match('/g:\w+/gim', $a_to) == false )
            return false;

        $res = preg_replace('(g:\w+)','${1}',$a_to);
        print_r( $res );
    }
}
?>
