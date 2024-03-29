<?php

/*
    XML Page Controller
    Copyright (C) 2008 Saponenko Andrew<roguevoo@gmail.com>

    XML Page Controller is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    XML Page Controller is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with XML Page Controller.  If not, see <http://www.gnu.org/licenses/>.
*/

class XMLConfig
{
    /*
     * $filename (string) - f
     */
    var $filename = '';
    var $xml = 0;
    var $map = array();

    /* CONSTRUCTOR */
    function XMLConfig()
    {
    }


    /* Return elements total */
    function count()
    {
        return count( $this->map );
    }

    /* Return elements total */
    function lenght()
    {
        return count( $this->map );
    }

    /* Return data */
    function map()
    {
        return $this->map;
    }

    /* Set new data */
    function setMap( $newMap = array() )
    {
        if( !is_array($newMap) && !is_object( $newMap ) ) return;
        $this->map = $newMap;
    }


    /* Return TRUE if value isset */
    function is_set( $valueName = "" )
    {
        if( !$valueName || !is_string($valueName) ) return false;
        return isset( $this->map[$valueName] );
    }

    /* Save data as xml file ( XMLConfig format ) */
    function save($filename = '')
    {
        if( !$filename ) return false;
        $result = new XMLTag("values", array(), count( $this->map ) );
        foreach( $this->map as $key => $value )
        {
            $result->AddChild("key", array('name'=>$key), 0 );
        }


        foreach( $result->tagChildren as $item )
        {
            //echo $item->tagAttrs['name'] . '<br />';
            if( !$item->tagAttrs['name'] ) continue;

            $value = &$this->map[ $item->tagAttrs['name'] ];
            $item->tagData = "<![CDATA[". trim(serialize( $value )) ."]]>";
        }
        file_put_contents($filename, "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".$result->GetXML() );
        return true;
    }

    /* Load data from xml file ( XMLConfig format ), return true if success*/
    function load($filename = '')
    {
        if( !$filename || !is_string($filename) ) return false;
        if( !is_file( $filename ) ) return false;

        //$xml = new XMLParser( file_get_contents($filename) );
        $xml = new XMLParser( $filename );
        $data = $xml->Parse();

        foreach( $xml->document->tagChildren as $item )
        {
            //echo $item->tagData;

            if( isset( $item->tagAttrs['name'] ) == false ) continue;

            if( substr( trim( $item->tagData ), 1, 1) == ":" )
            {
                $this->map[ $item->tagAttrs['name'] ] = unserialize($item->tagData);
            }
            else
            {
                $this->map[ $item->tagAttrs['name'] ] = $item->tagData;
            }
        }
        return true;
    }

    /* Return value by name */
    function get($name)
    {
        if( $this->is_set( $name ) ) return $this->map[$name];
        return false;
    }


    /* Set new value by name */
    function set($name, $value = 0, $save = false)
    {
        if( !$name ) return false;
        if( is_string( $name ) ) $this->setArray( array( $name => $value ), $save );
        if( is_array( $name ) ) $this->setArray( $name, $save );
    }


    /* Append new data */
    function setArray( $array, $save = false )
    {
        if( !$array || is_array($array) == false ) return false;

        foreach ( $array as $key => $value )
        {
            $this->map[$key] = $value;
        }
        if( $save == true ) $this->save();
    }
}

?>
