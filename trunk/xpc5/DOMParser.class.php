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

    protected final function & getNode()
    { return $this->m_node; }

    protected final function & getParser()
    { return $this->m_owner; }

    public final function setNode( DOMElement & $a_node )
    { unset( $this->m_node ); $this->m_node = $a_node; }

    public final function setParser( DOMParser & $a_owner )
    { unset( $this->m_owner ); $this->m_owner = $a_owner; }

    protected final function child()
    {
        $childs = array();

        foreach ( $this->m_node->childNodes as $c )
        {
            if( $c->nodeType != XML_ELEMENT_NODE ) continue;
            $childs[] = $c;
        }
        return $childs;
    }

    protected final function text()
    { return $this->m_node->textContent; }

    protected final function data()
    { return $this->m_node->textContent; }

    protected final function name()
    { return $this->m_node->nodeName; }

    protected final function attr( $a_name )
    { return $this->m_node->hasAttribute( $a_name ) ? $this->m_node->getAttribute( $a_name ) : null; }

    protected final function attrs()
    {
        $result = array();

        $attr_array = $this->m_node->attributes;
        $attr_counter = 0;
        while( ( $a = $attr_array->item($attr_counter++) ) !== null )
            $result[ $a->name ] = $a->value;
            
        return $result;
    }
}

abstract class DOMElementParser extends DOMNodeProcessor
{
    protected final function value()
    {
        return parent::parser()->parseDataType( parent::node() );
    }

    public final function doInside()
    {
        $node     = parent::node();
        $parser   = parent::parser();

        foreach( parent::child() as $c )
        {
            $parser->parseElement( $c );
        }

        $this->setNode($node);
        $this->setParser($parser);
    }
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
        $this->m_parsers        = array();
        $this->m_dataParsers    = array();
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

        if( array_key_exists($dataType, $this->m_dataParsers) )
        {
            return $this->_parseElement( $a_node, $this->m_dataParsers[ $dataType ] );
        }

        if( isset( $this->m_dataParsers[ 'default_parser' ] ) )
        {
            return $this->_parseElement( $a_node, $this->m_dataParsers[ 'default_parser' ] );
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
                
                return call_user_func_array( $a_callback, array( $this, $a_node ) );
                

            case 'array':

                if( $a_callback[0] instanceof DOMElementParser )
                {
                    $instance =& $a_callback[0];
                    $instance->setNode($a_node);
                    $instance->setParser($this);

                    return call_user_func_array( $a_callback, array() );
                }

                return call_user_func_array( $a_callback, array( $this, $a_node ) );
        }
    }

    public function parseElement( DOMElement & $a_node )
    {
        $nodename = strtolower( $a_node->nodeName );
        if( array_key_exists($nodename, $this->m_parsers) )
        {
            return $this->_parseElement( $a_node, $this->m_parsers[ $nodename ] );
        }

        if( array_key_exists('default_parser', $this->m_parsers) )
        {
            return $this->_parseElement( $a_node, $this->m_parsers[ 'default_parser' ] );
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
                return;

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
