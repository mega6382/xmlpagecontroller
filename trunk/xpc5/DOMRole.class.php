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
 * Description of DOMRole
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */

class tagParser_role_recursive extends DOMElementParser
{
    protected function parse()
    {
        parent::doInside();
    }
}

class tagParser_role_entity extends DOMElementParser
{
    protected function parse()
    {
        parent::parser()->addEntity( parent::node() );
    }
}

class DOMRoleTable extends DOMParser
{
    private $m_data;

    public function addEntity( DOMElement & $a_node )
    {
        $entity = array(
            'uid' => 0,
            'group' => '',
            'login' => '',
            'password' => ''
        );

        foreach( $a_node->childNodes as $n )
        {
            if( $n->nodeType != XML_ELEMENT_NODE )
                continue;

            $entity[ $n->nodeName ] = $n->textContent;
        }

        if( empty( $entity['login'] ) )
            return;

        $key =& $entity['login'];

        $this->m_data[ $key ] = $entity;
    }

    public function __construct()
    {
        parent::__construct();
        parent::registerParsers(array(
            'root' => new tagParser_role_recursive(),
            'entity' => new tagParser_role_entity()
        ));
    }

    public function get( $a_filename )
    {
        $this->m_data = array();
        parent::fromFile($a_filename);
        return $this->m_data;
    }
}
?>
