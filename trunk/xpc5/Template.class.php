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
 * class Template allow symply templating mechanism with string and arrays
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */

function no_case_sence_keys_compare($key1, $key2)
{
    if ( strtolower($key1) == strtolower($key2) )
        return 1;

    else return 0;
}

class Template
{
    var $m_values = array();

    public function __construct(){}

    static public function _doAssignOnce( & $a_template, & $a_key, & $a_val, array $a_brace, $a_case )
    {
        return $a_case ? str_replace($a_brace[0].$a_key.$a_brace[1], $a_val, $a_template) : str_ireplace($a_brace[0].$a_key.$a_brace[1], $a_val, $a_template);
    }

    static public function _doAssign( & $a_template, array & $a_values, array $a_brace, $a_case )
    {
        if( empty($a_values) )
            return $a_template;

        $l_output =@ $a_template;

        foreach( $a_values as $k => $v )
        {
            switch( gettype($v) )
            {
                case "boolean":
                case "integer":
                case "double":
                case "float":
                case "string":
                {
                    $l_output = self::_doAssignOnce($l_output,$k,$v,$a_brace,$a_case);
                }
                break;

                case "array":
                case "object":
                {
                    $l_output = self::_doAssign($l_output,$v,array($a_brace[0].$k.'.',$a_brace[1]),$a_case);
                }
                break;

                default:
                {
                    $l_output = self::_doAssignOnce( $l_output,$k,'[ERROR VALUE]',$a_brace,$a_case);
                }
                break;
            }
        }
        return $l_output;
    }

    public function assign( $name, $value = null )
    {
        if( is_array($name) || is_object($name) )
        {
            $this->m_values = $name;
            return;
        }
        
        $this->m_values[ $name ] = $value;
    }

    function __call( $name, $arg )
    {
        if( $name != 'apply' ) return;

        $templ  = isset( $arg[0] ) && is_string( $arg[0] )  ? $arg[0] : '';
        $array  = isset( $arg[1] ) && is_array( $arg[1] )   ? $arg[1] : $this->m_values;
        $brace  = isset( $arg[2] ) && is_array( $arg[2] )   ? $arg[2] : array('{','}');
        $case   = isset( $arg[3] ) ? $arg[3] : false;

        return $this->_doAssign($templ,$array,$brace,$case);
    }

    static function __callStatic( $name, $arg )
    {
        if( $name != 'apply' ) return;

        $templ  = isset( $arg[0] ) && is_string( $arg[0] )  ? $arg[0] : '';
        $array  = isset( $arg[1] ) && is_array( $arg[1] )   ? $arg[1] : array();
        $brace  = isset( $arg[2] ) && is_array( $arg[2] )   ? $arg[2] : array('{','}');
        $case   = isset( $arg[3] ) ? $arg[3] : false;

       // echo $templ . '<br />';
        return self::_doAssign($templ,$array,$brace,$case);
    }

    function applyParse( & $a_template, array & $a_values, array $a_brace )
    {
        $ts = &$a_brace[0];
        $te = &$a_brace[1];

        $startpos = 0;

        $result = array();

        do
        {
            $s = strpos( $a_template, $ts, $startpos );
            
            if( $s === false )
                break;
            
            $e = strpos( $a_template, $te, $s );
            
            if( $e === false )
                break;

            $startpos = $e;

            $sub = substr( $a_template, $s, ($e - $s) + strlen($te) );

            if( !strlen( $sub ) ) break;

            $key = substr( $sub, strlen($ts), -(strlen($te)) );
            $result[ strtoupper($key) ] = 1;
        }
        while( true );

        if( !count($result) ) return $a_template;

        $result2 = array();

        foreach( $a_values as $k => $v )
        {
            if( array_key_exists( strtoupper($k), $result) == true )
            {
                $result2[$k] = $v;
            }
        }
        return $this->_doAssign( @$a_template, $result2, $a_brace, false);
    }
}

?>
