<?php

/**
 * SitemapParser abstract class
 *
 * To create a parser class must inherit from this class as a base
 *
 */
abstract class SitemapParser extends NodeProcessor
{
    abstract public function parse( DOMNode & $node );
    abstract public function name();
}

class tagRecursiveParser extends SitemapParser
{
    function name()
    {
        return 'site root config conf metas options';
    }

    function parse( DOMNode & $node )
    {
        $this->parent->parse_recursive($node);
    }
}

class tagOptionParser extends SitemapParser
{
    function name()
    {
        return 'option';
    }

    function parse( DOMNode & $node )
    {
        $this->parent->addOption( $node->attr('name'), $node->data() );
    }
}

class tagOptionsParser extends SitemapParser
{
    function name()
    {
        return 'options';
    }

    function parse( DOMNode & $node )
    {
        foreach( $node->child() as $n )
            $this->parent->addOption( $n->name(), $n->data() );
    }
}

class tagMetasParser extends SitemapParser
{
    function name()
    {
        return 'metas';
    }

    function parse( DOMNode & $node )
    {
        foreach( $node->child() as $n )
            $this->parent->addMeta( $n->name(), $n->data() );
    }
}

class tagMetaParser extends SitemapParser
{
    function name()
    {
        return 'meta';
    }

    function parse( DOMNode & $node )
    {
        $this->parent->addMeta( $node->attr('name'), $node->data() );
    }
}

class tagPageParser extends SitemapParser
{
    var $stack = array();

    function name(){ return 'page'; }

    function parse( DOMNode & $node )
    {
        $id = $node->find_value('id');

        array_push($this->stack, $id );
        $path = implode('/', $this->stack );
        $this->parent->parse_recursive($node);
        array_pop($this->stack);

        $this->parent->addPage( $path, $node );
    }
}

class SitemapParserFactory extends DataCollection
{
    static private $m_instance = null;

    private function __construct()
    {
        foreach( get_declared_classes() as $decl )
        {
            if( !is_subclass_of($decl, 'SitemapParser') ) continue;

            $instance = new $decl();
            $declname = strtolower( $instance->name() );

            if( !$declname || !is_string($declname) || !strlen($declname) || $declname == 'undefined_parser' )
                continue;

            $keys = explode( ' ', $declname );

            foreach( $keys as $k )
            {
                $k = trim( $k );
                if( !$k  ) continue;

                $this->set($k, $instance);
            }
        }
    }

    private function __clone(){}
    static public function instance()
    {
        if( self::$m_instance === null )
            self::$m_instance = new SitemapParserFactory();

        return self::$m_instance;
    }
}

?>
