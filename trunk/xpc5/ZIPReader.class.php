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

class ZIPReader
{
    var $zip_installed = false;
    var $fileName = '';
    var $zip = 0;
    var $map = array();

    var $installed = false;

    function ZIPReader()
    {
        $this->zip_installed = ( function_exists('zip_open') && function_exists('zip_read') && function_exists('zip_entry_open') && function_exists('zip_entry_read') && function_exists('zip_entry_filesize')  &&  function_exists('zip_entry_name')  && function_exists('zip_entry_close') && function_exists('zip_close') );
    }

    function open( $filename = '' )
    {
        if( !$this->zip_installed ) return false;

        if( $filename == '' )
        {
            $filename = $this->fileName;
        }

        if( !is_string($filename) ) return false;

        $f = realpath( $filename );
        if( !$f ) return false;

        $this->fileName = $f;

        ob_start();
        $this->zip = zip_open( $this->fileName );
        ob_end_clean();

        if( !$this->zip || !is_resource( $this->zip ) ) return false;

        while( $zip_entry = zip_read( $this->zip ) )
        {
            $n = zip_entry_name($zip_entry);
            if( !$n ) continue;

            $this->map[ $n ] = $zip_entry;
        }
        return true;
    }

    function load( $filename = '' ){ return $this->open($filename); }


    function read( $name )
    {
        if( !$this->zip_installed ) return null;
        if( !$name || !is_string($name) ) return null;

        if( isset( $this->map[$name] ) )
        {
            $v = &$this->map[$name];
            if( !$v || !is_resource($v) ) return null;

            if( zip_entry_open($this->zip, $v, "r") )
            {
                $buf = zip_entry_read($v, zip_entry_filesize($v));
                zip_entry_close($v);
                return $buf;
            }
        }
        return null;
    }

    /*
     * todo files
     */
    function is_exist( $name )
    {
        if( !$name || !is_string($name) ) return false;
        return isset( $this->map[$name] );
    }

    function exist($name)
    {
        return $this->is_exist($name);
    }

    function files()
    {
        return array_keys($this->map);
    }

    function names()
    {
        return $this->files;

    }

    function get( $name )
    {
        return $this->read($name);
    }
};


?>
