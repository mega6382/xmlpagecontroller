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

defined( 'XPC_CLASSES' ) or die('defined');

class DOMLoader
{
    protected $m_dom = null;

    protected final function dom()
    {
        return $this->m_dom;
    }

    public function __construct()
    {

    }

    public final function fromString( $a_string )
    {
        if( empty($a_string) )
            return;

        $d = new DOMDocument();

        if( $d->loadXML( $a_string ) )
            return $this->fromDOM( $d );
    }

    public final function fromFile( $a_filename )
    {
        if( empty($a_filename) )
            return;

        if ( !file_exists($a_filename) )
            return;

        $d = new DOMDocument();

        if( $d->load( $a_filename ) )
            return $this->fromDOM( $d );
    }

    public final function fromDOM( DOMDocument & $a_dom )
    {
        if( $this->m_dom )
            unset( $this->m_dom );

        if( !$a_dom )
            return false;

        $this->m_dom = $a_dom;
        return true;
    }
}
?>
