<?php

abstract class DOMElementWalker
{
    protected $m_node = null;
    protected $m_owner = null;

    public function __construct( DOMWalker & $a_owner, DOMElement & $a_node = null )
    { $this->m_node = $a_node; $this->m_owner = $a_owner; }

    protected final function node()
    { return $this->m_node; }

    protected final function owner()
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

    public final function setOwner( DOMWalker & $a_owner )
    { unset( $this->m_owner ); $this->m_owner = $a_owner; }

    abstract public function transform();
    //abstract function tagName();
}

/**
 * Description of DOMWalker
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class DOMWalker extends DOMBase
{
    var $m_tranformers;

    public function __construct()
    {

    }

    public function registerWalker( $a_name, $a_iname )
    {
        if( empty( $a_name ) )
            return;

        if( empty( $a_iname ) )
            return;

        if( !in_array( $a_iname, get_declared_classes() ) )
            return;
            
        if( !is_subclass_of( $a_iname, 'DOMElementWalker' ) )
            return;

        $name_arr = explode(' ', $a_name);
        foreach( $name_arr as $i)
        {
            $key = strtolower( trim($i) );
            $this->m_tranformers[ $key ] = $a_iname;

	    echo 'Register "' . $key . '" transformer<br />';
        }


        return true;
    }

    public function registerWalkerInstance( $a_name, DOMElementWalker & $a_istance )
    {
        if( empty( $a_name ) )
            return;
           
        if( !$a_istance )
            return;

        $name_arr = explode(' ', $a_name);
        foreach( $name_arr as $i)
        {
            $key = strtolower( trim($i) );
            $this->m_tranformers[ $key ] = $a_istance;
	    echo 'Register "' . $key . '" transformer<br />';
        }
        return true;
    }

    public function registerWalkers( array $a_arr )
    {
        foreach( $a_arr as $k => $v )
        {
            if( $v instanceof DOMElementWalker )
                $this->registerWalkerInstance( $k , $v );
            else
                $this->registerWalker( $k , $v );
        }
            
    }
    
    private function _walk( DOMNode & $a_node )
    {
        if( !$a_node )
            return;

        $t =& $a_node->nodeType;
        
        if( $t == XML_ELEMENT_NODE || $t == XML_DOCUMENT_NODE )
        {
            foreach( $a_node->childNodes as $c ) $this->_analyse($c);
        }
    }

    private function _analyse( DOMNode & $a_node )
    {
        $nodename = strtolower( $a_node->nodeName );
        if( isset( $this->m_tranformers[ $nodename ] ) )
        {
            $trans =& $this->m_tranformers[ $nodename ];
            if( $trance instanceof DOMElementWalker )
            {
                $trans->setNode( $a_node );
                $trans->setOwner( $this );

                if( $trans->transform() ) $this->_walk($a_node);

                return;
            }
            $c = new $trans($this, $a_node);
            if( $c->transform() ) $this->_walk($a_node);
        }
    }
}
?>
