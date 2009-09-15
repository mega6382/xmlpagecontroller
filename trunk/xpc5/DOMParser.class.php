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
 *
 */
class DOMNodeProcessor
{
    private $m_node     = null;
    private $m_owner    = null;

    protected final function & node()
    { return $this->m_node; }

    protected final function & parser()
    { return $this->m_owner; }

    public final function setNode( DOMElement & $a_node )
    { unset( $this->m_node ); $this->m_node = $a_node; }

    public final function setParser( DOMParser & $a_owner )
    { unset( $this->m_owner ); $this->m_owner = $a_owner; }

    protected final function child()
    {
        $childs = array();

        foreach ( parent::node()->childNodes as $c )
        {
            if( $c->nodeType != XML_ELEMENT_NODE ) continue;
            $childs[] = $c;
        }
        return $childs;
    }

    protected final function text()
    { return parent::node()->textContent; }

    protected final function data()
    { return parent::node()->textContent; }

    protected final function name()
    { return parent::node()->nodeName; }

    protected final function attr( $a_name )
    { return parent::node()->hasAttribute( $a_name ) ? parent::node()->getAttribute( $a_name ) : null; }

    protected final function attrs()
    {
        $result = array();

        $attr_array = parent::node()->attributes;
        $attr_counter = 0;
        while( 1 )
        {
            $a = $attr_array->item($attr_counter);
            if( !$a )
                break;

            $result[ $a->name ] = $a->value;

            $attr_counter++;
        }
        return $result;
    }
}

abstract class DOMElementParser extends DOMNodeProcessor
{
    protected function value()
    { return parent::parser()->parseDataType( parent::node() ); }

    public final function doInside()
    {
        $t_node     = $this->node();
        $t_parser   = $this->parser();

        foreach( $this->child() as $c )
        {
            $t_parser->parseElement( $c );
        }

        $this->setNode($t_node);
        $this->setParser($t_parser);
    }
}

abstract class DOMElementDataParser extends DOMNodeProcessor
{

}

/**
 * Description of DOMParser
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class DOMParser
{
    private $m_parsers;
    private $m_dataParsers;

    public function __construct()
    {
        $this->m_parsers = array();
        $this->m_dataParsers = array();
    }

    public function registerParser( $a_name, $a_callback )
    {
        if( empty( $a_name ) || !is_string( $a_name ) )
            return;

        if( empty( $a_callback ) )
            return;

        $name_arr = explode(' ', $a_name);
        foreach( $name_arr as $i)
        {
            $key = strtolower( trim($i) );
            $this->m_parsers[ $key ] = $a_callback;
        }
        return true;
    }

    public function registerParsers( array $a_arr )
    {
        foreach( $a_arr as $k => $v )
        {
	    $this->registerParser( $k , $v );
        }
    }

    public function registerDataType( $a_name, $a_callback )
    {
	if( empty( $a_name ) || !is_string( $a_name ) )
            return;

        if( empty( $a_callback ) )
            return;

        $name_arr = explode(' ', $a_name);
        foreach( $name_arr as $i)
        {
            $key = strtolower( trim($i) );
            $this->m_dataParsers[ $key ] = $a_callback;
        }
        return true;
    }

    public function registerDataTypes( array $a_arr )
    {
        foreach( $a_arr as $k => $v )
        {
	    $this->registerDataType( $k , $v );
        }
    }

    public function parseDataType( DOMElement & $a_node )
    {
        $dataType = strtolower( $a_node->getAttribute('dataType') );

        if( key_exists($dataType, $this->m_dataParsers) )
        {
	    $this->_parseElement( $a_node, $this->m_dataParsers[ $dataType ] );
            return;
        }

        if( isset( $this->m_dataParsers[ 'default_parser' ] ) )
        {
	    $this->_parseElement( $a_node, $this->m_dataParsers[ 'default_parser' ] );
            return;
        }
    }
    
    public function parseDocument( DOMDocument & $a_doc )
    {
        foreach( $a_doc->childNodes as $c )
        {
            if( $c->nodeType != XML_ELEMENT_NODE )
                continue;

            $this->parseElement( $c );
        }
    }

    private function _parseElement( DOMElement & $a_node, & $a_callback )
    {
	switch( gettype($a_callback) )
	{
	    case 'string':
		call_user_func_array($a_callback, array( $this, $a_node ) );
		break;

	    case 'array':

		if( $a_callback[0] instanceof DOMElementParser )
		{
		    $instance =& $a_callback[0];

		    $instance->setNode($a_node);
		    $instance->setParser($this);

		    break;
		}

		call_user_func_array($a_callback, array( $this, $a_node ) );
		break;
	}
    }

    public function parseElement( DOMElement & $a_node )
    {
        $nodename = strtolower( $a_node->nodeName );
        if( array_key_exists($nodename, $this->m_parsers) )
        {
	    $this->_parseElement( $a_node, $this->m_parsers[ $nodename ] );
            return;
        }

        if( array_key_exists('default_parser', $this->m_parsers) )
        {
            $this->_parseElement( $a_node, $this->m_parsers[ 'default_parser' ] );
            return;
        }
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

    public final function fromDOM( DOMNode & $a_dom )
    {
        switch( $a_dom->nodeType )
        {
            default:
                return false;
            case XML_DOCUMENT_NODE:
                $this->parseDocument($a_dom);
                break;
            case XML_ELEMENT_NODE:
                $this->parseElement($a_dom);
                break;

        }
        return true;
    }
}
?>
