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

abstract class DOMElementParser
{
    protected $m_node = null;
    protected $m_owner = null;

    public function __construct()
    {}

    protected final function node()
    { return $this->m_node; }

    protected final function parser()
    { return $this->m_owner; }

    protected final function attr( $a_name )
    { return $this->m_node->hasAttribute( $a_name ) ? $this->m_node->getAttribute( $a_name ) : null; }

    protected function value()
    {
        $node = $this->node();

        $type = $node->hasAttribute('dataType') ? $node->getAttribute('dataType') : null;
        switch( $type )
        {
            default:
            case 'inline':
                return $node->textContent;

            case 'file':
                $filename = $node->textContent;
                if( file_exists($filename) )
                {
                    return file_get_contents($filename);
                }
                break;
            
            case 'php':
                $filename = $node->textContent;
                if( file_exists($filename) )
                {
                    ob_start();
                    include $filename;
                    $ob = ob_get_contents();
                    ob_end_clean();
    
                    return $ob;
                }
                break;

            case 'xml':
                $filename = $node->textContent;
                if( file_exists($filename) )
                {

                }
                break;
        }
    }

    public final function setNode( DOMElement & $a_node )
    { unset( $this->m_node ); $this->m_node = $a_node; }

    public final function setParser( DOMParser & $a_owner )
    { unset( $this->m_owner ); $this->m_owner = $a_owner; }

    public final function doParse( DOMParser & $a_parser, DOMElement & $a_node )
    {
        if( !$a_parser || !$a_node )
            return false;

        $this->setParser($a_parser);
        $this->setNode($a_node);
        return $this->parse();
    }

    public final function doInside()
    {
        $t_node     = $this->node();
        $t_parser   = $this->parser();

        foreach( $t_node->childNodes as $c )
        {
            if( $c->nodeType != XML_ELEMENT_NODE )
                continue;

            $t_parser->parseElement( $c );
        }

        $this->setNode($t_node);
        $this->setParser($t_parser);
    }

    abstract protected function parse();
}

/**
 * Description of DOMParser
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class DOMParser extends DOMLoader
{
    private $m_parsers;

    public function __construct()
    {
        $this->m_parsers = array();
    }

    public function registerParser( $a_name, $a_iname )
    {
        if( empty( $a_name ) )
            return;

        if( empty( $a_iname ) )
            return;

        if( !in_array( $a_iname, get_declared_classes() ) )
            return;
            
        if( !is_subclass_of( $a_iname, 'DOMElementParser' ) )
            return;

        $name_arr = explode(' ', $a_name);
        foreach( $name_arr as $i)
        {
            $key = strtolower( trim($i) );
            $this->m_parsers[ $key ] = $a_iname;

            //echo 'Register "' . $key . '" transformer<br />';
        }


        return true;
    }

    public function registerParserInstance( $a_name, DOMElementParser & $a_istance )
    {
        if( empty( $a_name ) )
            return;
           
        if( !$a_istance )
            return;

        $name_arr = explode(' ', $a_name);
        foreach( $name_arr as $i)
        {
            $key = strtolower( trim($i) );
            $this->m_parsers[ $key ] = $a_istance;
            //echo 'Register "' . $key . '" parser<br />';
        }
        return true;
    }

    public function registerParsers( array $a_arr )
    {
        foreach( $a_arr as $k => $v )
        {
            if( $v instanceof DOMElementParser )
                $this->registerParserInstance( $k , $v );
            else
                $this->registerParser( $k , $v );
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

    public function parseElement( DOMElement & $a_node )
    {
        $nodename = strtolower( $a_node->nodeName );
        if( isset( $this->m_parsers[ $nodename ] ) )
        {
            $trans =& $this->m_parsers[ $nodename ];
            if( $trans instanceof DOMElementParser )
            {
                $trans->doParse($this, $a_node);
                return;
            }
            
            $c = new $trans($this, $a_node);
            $trans->doParse($this, $a_node);
        }
    }
}
?>
