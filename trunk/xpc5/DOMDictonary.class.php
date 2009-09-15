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
 * Description of DOMDictonary
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */

class tagParser_dictonary_recursive extends DOMElementParser
{
    protected function parse()
    {
        parent::doInside();
    }
}

class tagParser_dictonary_key extends DOMElementParser
{
    protected function parse()
    {
        $name = parent::attr('name');
        if( !$name )
            $name = parent::attr('id');
        if( !$name )
            return;

        parent::parser()->addValue($name, parent::text());
    }
}

class DOMDictonary extends DOMParser
{
    private $m_dic = array();
    private $m_file = '';

    public function __construct()
    {
        parent::__construct();
        parent::registerParsers(array(
            'root' => new tagParser_dictonary_recursive(),
            'key' => new tagParser_dictonary_key()
        ));
    }

    public function addValue( $a_name, $a_value )
    {
        if( empty($a_name) )
            return;

        $this->m_dic[ $a_name ] = $a_value;
    }

    public function value( $a_dic, $a_key )
    {
        if( $a_dic != $this->m_file )
        {
            $this->m_dic = array();
            if( parent::fromFile($a_dic) )
            {
                $this->m_file = $a_dic;
            }
        }
        return key_exists( $a_key, $this->m_dic ) ? $this->m_dic[$a_key] : null;
    }

    public function key_exist( $a_dic, $a_key )
    {
        return ( $this->value($a_dic, $a_key) != null ) ? true : false;
    }
}
?>
