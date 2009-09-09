<?php

/**
 * To create a generator class must inherit from this class as a base
 */
abstract class SitemapGenerator extends NodeProcessor
{
    /**
     * Do generation process
     * @param DOMNode $node
     */
    abstract public function generate( DOMNode & $node );

    /**
     * Get element tag name where apply this generator
     */
    abstract public function name();
}

class tagPagesGenerator extends SitemapGenerator
{
    public function name() {
        return 'pages';
    }

    public function generate( DOMNode & $node )
    {
        if( !$node )
        return null;

        $params = array();

        foreach( $node->child() as $item )
        {
        if( $item->name() != 'param' )
            continue;

        $name = $item->attr('name');

        if( $name == null )
            continue;

        $params[ $name ] = $item->data();
        }

        if( count($params) < 1 )
        return null;

        $result = array();

        foreach( $params as $key => $val )
        {
        $n = $node->document()->createElement('page');
        $n->attr( 'id', $val );
        array_push($result, $n);
        }
        return $result;
    }
}

class SitemapGeneratorFactory extends DataCollection
{
    static private $m_instance = null;

    private function __construct()
    {
        foreach( get_declared_classes() as $decl )
        {
            if( !is_subclass_of($decl, 'SitemapGenerator') ) continue;


            $instance = new $decl();
            $declname = $instance->name();

            if( !$declname || !is_string($declname) || !strlen($declname) || $declname == 'undefined_parser' )
                continue;

            $keys = explode( ' ', $declname );

            foreach( $keys as $k )
            {
                $k = trim($k);

                if( !$k  ) continue;

                $this->set($k, $instance);
            }
        }
    }

    private function __clone(){}
    static public function instance()
    {
        if( self::$m_instance === null )
            self::$m_instance = new SitemapGeneratorFactory();

        return self::$m_instance;
    }
}

?>